<?php

namespace Tests\Feature;

use App\Actions\BlockedLists\CreateBlockedEntryAction;
use App\Models\Domain;
use App\Models\Locale;
use App\Models\Mailbox;
use App\Models\Section;
use App\Models\SectionItem;
use App\Models\SecuritySetting;
use App\Models\ThemeState;
use App\Models\User;
use App\Services\Installer\InstallState;
use App\Services\Mailboxes\MailboxMessageService;
use App\Services\Themes\ThemeCacheService;
use App\Services\Translations\TranslationStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicMailboxExperienceTest extends TestCase
{
    use RefreshDatabase;

    private bool $hadInstallLock;

    private ?string $originalInstallLock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $lockPath = app(InstallState::class)->lockPath();
        $this->hadInstallLock = File::exists($lockPath);
        $this->originalInstallLock = $this->hadInstallLock ? File::get($lockPath) : null;
        app(InstallState::class)->lock();
        app(TranslationStore::class)->syncRegistry();
        $this->locale();
        $this->activateTheme('horizon');
    }

    protected function tearDown(): void
    {
        $lockPath = app(InstallState::class)->lockPath();
        if ($this->hadInstallLock) {
            File::put($lockPath, $this->originalInstallLock ?? '');
        } else {
            File::delete($lockPath);
        }
        parent::tearDown();
    }

    public function test_homepage_opens_with_usable_mailbox_creator_and_ready_domains(): void
    {
        $this->domain();

        $this->get(route('public.home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Create a private temporary inbox')
            ->assertSee('mail.test')
            ->assertSee(route('public.mailbox.store', ['locale' => 'en']), false)
            ->assertDontSee('inbox@private.test');
    }

    public function test_public_guest_can_create_random_mailbox_and_access_only_with_session_token(): void
    {
        $domain = $this->domain();

        $response = $this->post(route('public.mailbox.store', ['locale' => 'en']), [
            'domain_id' => $domain->id,
        ])->assertRedirect();

        $mailbox = Mailbox::query()->firstOrFail();
        $location = $response->headers->get('Location');

        $this->assertNotNull($location);
        $this->assertStringContainsString('/en/mailboxes/'.$mailbox->id, $location);
        $this->assertStringContainsString('access_token=', $location);
        $this->assertSame('guest', $mailbox->mailbox_type);

        $this->get($location)
            ->assertOk()
            ->assertSee($mailbox->address)
            ->assertSee('Your inbox is empty');

        $this->get(route('public.mailbox.show', ['locale' => 'en', 'mailbox' => $mailbox]))
            ->assertNotFound();
    }

    public function test_unready_domains_custom_aliases_and_active_limits_are_enforced(): void
    {
        $domain = $this->domain(['catch_all_ready' => false]);

        $this->post(route('public.mailbox.store', ['locale' => 'en']), ['domain_id' => $domain->id])
            ->assertSessionHasErrors('domain_id');

        $ready = $this->domain(['domain_name' => 'ready.test', 'display_name' => 'Ready']);
        $this->post(route('public.mailbox.store', ['locale' => 'en']), [
            'domain_id' => $ready->id,
            'alias' => 'custom-name',
        ])->assertSessionHasErrors('alias');
    }

    public function test_blocked_list_and_bot_protection_hooks_block_creation_cleanly(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = $this->domain();

        app(CreateBlockedEntryAction::class)->handle($admin, [
            'entry_type' => 'recipient_domain',
            'value' => $domain->domain_name,
            'reason' => 'Public abuse prevention',
            'source' => 'manual',
            'status' => 'active',
            'starts_at' => null,
            'expires_at' => null,
            'related_abuse_report_id' => null,
            'notes' => null,
        ]);

        $this->post(route('public.mailbox.store', ['locale' => 'en']), ['domain_id' => $domain->id])
            ->assertSessionHasErrors('local_part');

        SecuritySetting::query()->updateOrCreate(['group' => 'bot_protection'], [
            'payload' => [
                'provider' => 'turnstile',
                'recaptcha_mode' => 'v2_checkbox',
                'minimum_score' => 0.5,
                'fail_mode' => 'challenge',
                'is_active' => true,
                'protected_forms' => ['mailbox_creation'],
            ],
            'encrypted_secrets' => null,
            'test_history' => [],
        ]);

        $clean = $this->domain(['domain_name' => 'clean.test', 'display_name' => 'Clean']);
        $this->post(route('public.mailbox.store', ['locale' => 'en']), ['domain_id' => $clean->id])
            ->assertSessionHasErrors('bot_protection_token');
    }

    public function test_message_preview_uses_sanitized_body_and_never_requires_admin_controls(): void
    {
        $domain = $this->domain();
        $mailbox = Mailbox::query()->create([
            'domain_id' => $domain->id,
            'locale_id' => Locale::query()->where('locale', 'en')->value('id'),
            'address' => 'safe@mail.test',
            'local_part' => 'safe',
            'mailbox_type' => 'guest',
            'status' => 'active',
            'expires_at' => now()->addHour(),
            'last_activity_at' => now(),
        ]);
        $message = app(MailboxMessageService::class)->store($mailbox, [
            'sender_email' => 'sender@example.test',
            'subject' => 'Verification',
            'plain_text_body' => 'Safe fallback',
            'html_body' => '<p>Hello</p><script>alert(1)</script><a href="https://bad.test">bad</a>',
            'received_at' => now(),
        ]);
        $token = str_repeat('A', 64);
        $this->withSession(['public_mailboxes.'.$mailbox->id.'.access_hash' => Hash::make($token)]);

        $this->get(route('public.mailbox.messages.show', [
            'locale' => 'en',
            'mailbox' => $mailbox,
            'message' => $message,
            'access_token' => $token,
        ]))
            ->assertOk()
            ->assertSee('Verification')
            ->assertSee('<p>Hello</p>', false)
            ->assertDontSee('<script', false)
            ->assertDontSee('https://bad.test', false)
            ->assertDontSee('Lock mailbox');
    }

    public function test_sections_render_without_empty_placeholders_and_faq_schema_when_ready(): void
    {
        $locale = Locale::query()->where('locale', 'en')->firstOrFail();
        $section = Section::query()->create([
            'locale_id' => $locale->id,
            'section_type' => 'faq',
            'placement' => 'home.faq',
            'title' => 'FAQ',
            'status' => 'active',
            'visibility' => 'public',
            'sort_order' => 1,
        ]);

        foreach (range(1, 4) as $index) {
            SectionItem::query()->create([
                'section_id' => $section->id,
                'title' => 'Question '.$index,
                'content' => 'Answer '.$index,
                'status' => 'active',
                'sort_order' => $index,
            ]);
        }

        $this->domain();

        $this->get(route('public.home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('FAQ')
            ->assertSee('FAQPage')
            ->assertDontSee('Module workspace coming next');
    }

    public function test_all_three_themes_have_mailbox_experience_views(): void
    {
        $this->domain();

        foreach (['horizon', 'atlas', 'legacy'] as $theme) {
            $this->activateTheme($theme);

            $this->get(route('public.home', ['locale' => 'en']))
                ->assertOk()
                ->assertSee('data-public-theme="'.$theme.'"', false)
                ->assertSee('Create a private temporary inbox');
        }
    }

    private function locale(): Locale
    {
        return Locale::query()->create([
            'language_name' => 'English',
            'native_name' => 'English',
            'locale' => 'en',
            'direction' => 'ltr',
            'region' => 'Global',
            'market_readiness' => 'ready',
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 1,
            'launch_status' => 'launched',
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function domain(array $overrides = []): Domain
    {
        return Domain::query()->create([
            'domain_name' => 'mail.test',
            'display_name' => 'Mail Test',
            'is_active' => true,
            'is_public' => true,
            'catch_all_ready' => true,
            'is_default' => true,
            'sort_order' => 1,
            'status' => 'ready',
            ...$overrides,
        ]);
    }

    private function activateTheme(string $slug): void
    {
        ThemeState::query()->delete();

        foreach (['horizon', 'atlas', 'legacy'] as $theme) {
            ThemeState::query()->create([
                'slug' => $theme,
                'status' => $theme === $slug ? 'active' : 'inactive',
                'last_activated_at' => $theme === $slug ? now() : null,
            ]);
        }

        app(ThemeCacheService::class)->clear();
    }
}
