<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_translation_center_renders_registry_groups_and_source_keys(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.translation-center.index'))
            ->assertOk()
            ->assertSee('Translation Center')
            ->assertSee('English is the canonical source language')
            ->assertSee('Homepage')
            ->assertSee('Mailbox Experience')
            ->assertSee('Cookie and Consent')
            ->assertSee('home.hero.title')
            ->assertSee('Canonical language');

        $this->assertDatabaseHas('translation_sources', [
            'translation_key' => 'home.hero.title',
            'group_key' => 'homepage',
            'source_value' => 'Private temporary email in seconds',
        ]);
        $this->assertDatabaseHas('translation_sources', ['translation_key' => 'mailbox.create.button']);
        $this->assertDatabaseHas('translation_sources', ['translation_key' => 'footer.links.privacy']);
    }

    public function test_search_and_filters_for_source_registry(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.translation-center.index', ['q' => 'hero']))
            ->assertOk()
            ->assertSee('home.hero.title');

        $this->assertContains('home.hero.title', collect($response->viewData('sources')->items())->pluck('translation_key'));
        $this->assertNotContains('footer.links.privacy', collect($response->viewData('sources')->items())->pluck('translation_key'));

        $response = $this->actingAs($admin)
            ->get(route('admin.translation-center.index', ['group' => 'footer']))
            ->assertOk()
            ->assertSee('Footer')
            ->assertSee('footer.copyright');

        $this->assertContains('footer.copyright', collect($response->viewData('sources')->items())->pluck('translation_key'));
        $this->assertNotContains('mailbox.create.button', collect($response->viewData('sources')->items())->pluck('translation_key'));

        $this->actingAs($admin)
            ->get(route('admin.translation-center.index', ['requirement' => 'optional']))
            ->assertOk()
            ->assertSee('cookie.banner.title')
            ->assertSee('system.maintenance.enabled');
    }

    public function test_owner_or_admin_can_create_translation_source_with_valid_dot_key(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.translation-center.sources.store'), [
                'group_key' => 'homepage',
                'translation_key' => 'home.banner.subtitle',
                'source_value' => 'A calmer inbox for every signup.',
                'description' => 'Secondary homepage banner text.',
                'value_type' => 'short_text',
                'is_required' => '1',
                'is_active' => '1',
                'sort_order' => 75,
            ])
            ->assertRedirect(route('admin.translation-center.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('translation_sources', [
            'translation_key' => 'home.banner.subtitle',
            'source_value' => 'A calmer inbox for every signup.',
            'created_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('user_audit_events', [
            'event' => 'translation_source.created',
            'actor_id' => $admin->id,
        ]);
    }

    public function test_duplicate_and_invalid_translation_keys_are_rejected_with_error_summary(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.translation-center.index'))->assertOk();

        $this->actingAs($admin)
            ->from(route('admin.translation-center.index'))
            ->post(route('admin.translation-center.sources.store'), [
                'group_key' => 'homepage',
                'translation_key' => 'home.hero.title',
                'source_value' => 'Duplicate key',
                'description' => 'Should fail.',
                'value_type' => 'short_text',
                'is_required' => '1',
                'is_active' => '1',
                'sort_order' => 10,
            ])
            ->assertRedirect(route('admin.translation-center.index'))
            ->assertSessionHasErrors('translation_key');

        $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.translation-center.index'))
            ->post(route('admin.translation-center.sources.store'), [
                'group_key' => 'homepage',
                'translation_key' => 'Home Hero',
                'source_value' => '',
                'description' => 'Should fail.',
                'value_type' => 'short_text',
                'is_required' => '1',
                'is_active' => '1',
                'sort_order' => 10,
            ])
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('Please fix the highlighted fields.');
    }

    public function test_editor_can_view_but_cannot_manage_translation_sources(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)
            ->get(route('admin.translation-center.index'))
            ->assertOk()
            ->assertSee('Translation Center')
            ->assertDontSee('Create source key');

        $this->actingAs($editor)
            ->post(route('admin.translation-center.sources.store'), [
                'group_key' => 'homepage',
                'translation_key' => 'home.editor.blocked',
                'source_value' => 'Blocked',
                'value_type' => 'short_text',
            ])
            ->assertForbidden();
    }

    public function test_content_translation_tables_are_not_created(): void
    {
        $this->assertTrue(Schema::hasTable('translation_sources'));
        $this->assertTrue(Schema::hasTable('translation_values'));
        $this->assertFalse(Schema::hasTable('post_translations'));
        $this->assertFalse(Schema::hasTable('page_translations'));
        $this->assertFalse(Schema::hasTable('section_translations'));
    }

    public function test_no_livewire_alpine_cdn_or_hardcoded_localhost_in_translation_center(): void
    {
        $files = collect([
            resource_path('views/dashboard/translation-center/index.blade.php'),
            resource_path('views/components/localization/translation-group-tabs.blade.php'),
            resource_path('views/components/localization/translation-source-row.blade.php'),
            resource_path('views/components/localization/translation-filters.blade.php'),
            app_path('Http/Controllers/Admin/TranslationCenterController.php'),
            base_path('routes/web.php'),
        ]);

        $contents = $files->map(fn (string $file): string => file_get_contents($file))->implode("\n");

        $this->assertStringNotContainsString('Livewire', $contents);
        $this->assertStringNotContainsString('livewire', $contents);
        $this->assertStringNotContainsString('unpkg.com/alpine', $contents);
        $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/alpine', $contents);
        $this->assertStringNotContainsString('127.0.0.1', $contents);
        $this->assertStringNotContainsString('localhost', $contents);
    }
}
