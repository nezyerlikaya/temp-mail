<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\Locale;
use App\Models\User;
use App\Services\EmailTemplates\EmailTemplateDeliverabilityService;
use App\Services\EmailTemplates\EmailTemplatePreviewService;
use App\Services\EmailTemplates\EmailTemplateReadinessService;
use App\Services\EmailTemplates\EmailTemplateRenderer;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_email_templates_render_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();
        $this->locale('en');

        $this->actingAs($admin)
            ->get(route('admin.email-templates.index'))
            ->assertOk()
            ->assertSee('Email Templates')
            ->assertSee('System email templates are content records')
            ->assertSee('Create template')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_email_templates_are_independent_per_language_and_can_be_created(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');
        $german = $this->locale('de');

        $this->actingAs($admin)
            ->post(route('admin.email-templates.store'), [
                'locale_id' => $english->id,
                'template_key' => 'password_reset',
                'subject' => 'Reset your {{ app_name }} password',
                'preheader' => 'Secure password reset for {{ user_name }}',
                'html_body' => '<p>Hello {{ user_name }}, use {{ reset_url }} for {{ app_name }}.</p>',
                'plain_text_body' => 'Hello {{ user_name }}, use {{ reset_url }} for {{ app_name }}.',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.email-templates.store'), [
                'locale_id' => $german->id,
                'template_key' => 'password_reset',
                'subject' => 'Passwort zurucksetzen',
                'preheader' => 'Sicherer Link',
                'html_body' => '<p>Hallo {{ user_name }}, {{ reset_url }} - {{ app_name }}</p>',
                'plain_text_body' => 'Hallo {{ user_name }}, {{ reset_url }} - {{ app_name }}',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('email_templates', ['locale_id' => $english->id, 'template_key' => 'password_reset', 'subject' => 'Reset your {{ app_name }} password']);
        $this->assertDatabaseHas('email_templates', ['locale_id' => $german->id, 'template_key' => 'password_reset', 'subject' => 'Passwort zurucksetzen']);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'email_template.created', 'actor_id' => $admin->id]);
    }

    public function test_unsafe_html_and_arbitrary_blade_or_php_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');

        $this->actingAs($admin)
            ->from(route('admin.email-templates.create'))
            ->post(route('admin.email-templates.store'), [
                'locale_id' => $english->id,
                'template_key' => 'login_alert',
                'subject' => 'Login alert',
                'html_body' => '<p onclick="alert(1)">Hello {{ user_name }}</p><script>alert(1)</script>',
                'plain_text_body' => 'Hello {{ user_name }}',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.email-templates.create'))
            ->assertSessionHasErrors('html_body');

        $this->actingAs($admin)
            ->from(route('admin.email-templates.create'))
            ->post(route('admin.email-templates.store'), [
                'locale_id' => $english->id,
                'template_key' => 'security_alert',
                'subject' => 'Security alert',
                'html_body' => '<p>@php echo "owned"; @endphp {{ user_name }}</p>',
                'plain_text_body' => '{{ user_name }}',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.email-templates.create'))
            ->assertSessionHasErrors('html_body');
    }

    public function test_required_variables_are_validated_before_critical_template_activation(): void
    {
        $admin = User::factory()->admin()->create();
        $english = $this->locale('en');

        $this->actingAs($admin)
            ->from(route('admin.email-templates.create'))
            ->post(route('admin.email-templates.store'), [
                'locale_id' => $english->id,
                'template_key' => 'password_reset',
                'subject' => 'Password reset',
                'html_body' => '<p>Hello {{ user_name }} from {{ app_name }}</p>',
                'plain_text_body' => 'Hello {{ user_name }} from {{ app_name }}',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.email-templates.create'))
            ->assertSessionHasErrors('html_body');
    }

    public function test_renderer_replaces_allowlisted_variables_without_executing_blade(): void
    {
        $template = EmailTemplate::query()->create([
            'locale_id' => $this->locale('en')->id,
            'template_key' => 'login_alert',
            'subject' => 'Login alert',
            'html_body' => '<p>Hello {{ user_name }}</p><p>{{ app_name }}</p><p>{{ unknown }}</p>',
            'plain_text_body' => 'Hello {{ user_name }} {{ app_name }}',
            'status' => 'draft',
        ]);

        $html = app(EmailTemplateRenderer::class)->renderHtml($template, [
            'app_name' => '<Temp Mail>',
            'user_name' => '<Admin>',
        ]);

        $this->assertStringContainsString('&lt;Admin&gt;', $html);
        $this->assertStringContainsString('&lt;Temp Mail&gt;', $html);
        $this->assertStringNotContainsString('{{ unknown }}', $html);
    }

    public function test_edit_screen_shows_desktop_mobile_plain_text_preview_and_readiness_panels(): void
    {
        $admin = User::factory()->admin()->create();
        $template = $this->template(['status' => 'draft']);

        $this->actingAs($admin)
            ->get(route('admin.email-templates.edit', $template))
            ->assertOk()
            ->assertSee('Safe preview')
            ->assertSee('Desktop')
            ->assertSee('Mobile')
            ->assertSee('Plain text')
            ->assertSee('Dark mode')
            ->assertSee('Send test email')
            ->assertSee('Deliverability readiness')
            ->assertSee('Brand layout')
            ->assertSee('Reset to default');

        $preview = app(EmailTemplatePreviewService::class)->preview($template);

        $this->assertStringContainsString('Alex Morgan', $preview['desktop_html']);
        $this->assertStringContainsString('Alex Morgan', $preview['plain_text']);
    }

    public function test_send_test_email_is_owner_admin_only_and_marks_subject_as_test(): void
    {
        Mail::fake();
        config(['mail.default' => 'array', 'mail.mailers.array' => ['transport' => 'array'], 'mail.from.address' => 'support@example.com']);

        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();
        $template = $this->template();

        $this->actingAs($editor)
            ->post(route('admin.email-templates.send-test', $template), ['recipient' => 'editor@example.com'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.email-templates.send-test', $template), ['recipient' => 'admin@example.com'])
            ->assertRedirect(route('admin.email-templates.edit', $template))
            ->assertSessionHas('test_status.status', 'sent');

        $this->assertDatabaseHas('user_audit_events', ['event' => 'email_template.test_sent', 'actor_id' => $admin->id]);
    }

    public function test_mail_delivery_failure_does_not_expose_secrets(): void
    {
        config(['mail.default' => '', 'mail.mailers.smtp.password' => 'super-secret-password', 'mail.from.address' => '']);

        $template = $this->template();
        $readiness = app(EmailTemplateDeliverabilityService::class)->readiness();

        $this->assertFalse($readiness['ready']);
        $this->assertStringNotContainsString('super-secret-password', $readiness['message']);
        $this->assertStringContainsString('not configured', $readiness['message']);
    }

    public function test_reset_to_default_is_owner_admin_only_and_restores_trusted_default(): void
    {
        $owner = User::factory()->owner()->create();
        $editor = User::factory()->editor()->create();
        $template = $this->template([
            'template_key' => 'password_reset',
            'subject' => 'Custom subject',
            'html_body' => '<p>Custom</p>',
            'plain_text_body' => 'Custom',
            'status' => 'active',
        ]);

        $this->actingAs($editor)
            ->post(route('admin.email-templates.reset', $template), ['confirm_reset' => '1'])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('admin.email-templates.reset', $template), ['confirm_reset' => '1'])
            ->assertRedirect(route('admin.email-templates.edit', $template));

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'subject' => 'Reset your {{ app_name }} password',
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'email_template.reset', 'actor_id' => $owner->id]);
    }

    public function test_readiness_coverage_is_language_specific(): void
    {
        $english = $this->locale('en');
        $german = $this->locale('de');

        $this->template(['locale_id' => $english->id, 'template_key' => 'password_reset', 'status' => 'active']);
        $this->template(['locale_id' => $german->id, 'template_key' => 'password_reset', 'status' => 'draft']);

        $readiness = app(EmailTemplateReadinessService::class)->dashboard();
        $englishCoverage = $readiness['languages']->first(fn (array $language): bool => $language['locale']->id === $english->id);
        $germanCoverage = $readiness['languages']->first(fn (array $language): bool => $language['locale']->id === $german->id);

        $this->assertGreaterThan($germanCoverage['score'], $englishCoverage['score']);
    }

    public function test_activate_and_hide_status_are_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $template = $this->template(['status' => 'draft']);

        $this->actingAs($admin)
            ->post(route('admin.email-templates.status', $template), ['status' => 'active'])
            ->assertRedirect(route('admin.email-templates.edit', $template));

        $this->actingAs($admin)
            ->post(route('admin.email-templates.status', $template), ['status' => 'hidden'])
            ->assertRedirect(route('admin.email-templates.edit', $template));

        $this->assertDatabaseHas('user_audit_events', ['event' => 'email_template.activated', 'actor_id' => $admin->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'email_template.hidden', 'actor_id' => $admin->id]);
    }

    public function test_email_template_sources_do_not_use_forbidden_patterns_or_translation_tables(): void
    {
        $files = [
            app_path('Http/Controllers/Admin/EmailTemplateController.php'),
            app_path('Models/EmailTemplate.php'),
            app_path('Services/EmailTemplates/EmailTemplateStore.php'),
            app_path('Services/EmailTemplates/EmailTemplateRenderer.php'),
            app_path('Services/EmailTemplates/EmailTemplatePreviewService.php'),
            app_path('Services/EmailTemplates/EmailTemplateReadinessService.php'),
            app_path('Services/EmailTemplates/EmailTemplateDeliverabilityService.php'),
            app_path('Services/EmailTemplates/SystemEmailLayoutResolver.php'),
            app_path('Services/EmailTemplates/EmailTemplateVariableRegistry.php'),
            app_path('Services/EmailTemplates/EmailTemplateSanitizer.php'),
            app_path('Actions/EmailTemplates/CreateEmailTemplateAction.php'),
            app_path('Actions/EmailTemplates/UpdateEmailTemplateAction.php'),
            app_path('Actions/EmailTemplates/ResetEmailTemplateAction.php'),
            app_path('Actions/EmailTemplates/SendTestEmailAction.php'),
            app_path('Http/Requests/EmailTemplates/StoreEmailTemplateRequest.php'),
            app_path('Http/Requests/EmailTemplates/UpdateEmailTemplateRequest.php'),
            app_path('Http/Requests/EmailTemplates/EmailTemplateFilterRequest.php'),
            app_path('Http/Requests/EmailTemplates/SendTestEmailRequest.php'),
            app_path('Http/Requests/EmailTemplates/ResetEmailTemplateRequest.php'),
            app_path('Http/Requests/EmailTemplates/ActivateEmailTemplateRequest.php'),
            resource_path('views/dashboard/email-templates/index.blade.php'),
            resource_path('views/dashboard/email-templates/create.blade.php'),
            resource_path('views/dashboard/email-templates/edit.blade.php'),
            resource_path('views/components/emails/template-card.blade.php'),
            resource_path('views/components/emails/template-editor.blade.php'),
            resource_path('views/components/emails/template-filter-bar.blade.php'),
            resource_path('views/components/emails/variable-picker.blade.php'),
            resource_path('views/components/emails/language-status.blade.php'),
            resource_path('views/components/emails/status-badge.blade.php'),
            resource_path('views/components/emails/required-variable-warning.blade.php'),
            resource_path('views/components/emails/validation-summary.blade.php'),
            resource_path('views/components/emails/empty-state.blade.php'),
            resource_path('views/components/emails/preview-panel.blade.php'),
            resource_path('views/components/emails/preview-device-tabs.blade.php'),
            resource_path('views/components/emails/send-test-panel.blade.php'),
            resource_path('views/components/emails/readiness-summary.blade.php'),
            resource_path('views/components/emails/missing-template-list.blade.php'),
            resource_path('views/components/emails/deliverability-warning.blade.php'),
            resource_path('views/components/emails/reset-warning.blade.php'),
            resource_path('views/components/emails/test-status.blade.php'),
            resource_path('views/components/emails/brand-layout-preview.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('Livewire', $contents, $file);
            $this->assertStringNotContainsString('livewire', $contents, $file);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $contents, $file);
            $this->assertStringNotContainsString('unpkg.com/alpine', $contents, $file);
            $this->assertStringNotContainsString('127.0.0.1', $contents, $file);
            $this->assertStringNotContainsString('email_template_translations', $contents, $file);
            $this->assertStringNotContainsString('template_translations', $contents, $file);
        }
    }

    private function locale(string $code): Locale
    {
        app(LocaleSettingsStore::class)->ensureSeeded();

        return Locale::query()->where('locale', $code)->firstOrFail();
    }

    /** @param array<string, mixed> $overrides */
    private function template(array $overrides = []): EmailTemplate
    {
        return EmailTemplate::query()->create([
            'locale_id' => $this->locale('en')->id,
            'template_key' => 'login_alert',
            'subject' => '{{ app_name }} login alert',
            'preheader' => 'Review sign-in activity.',
            'html_body' => '<p>Hello {{ user_name }}, sign in at {{ login_url }} for {{ app_name }}.</p>',
            'plain_text_body' => 'Hello {{ user_name }}, sign in at {{ login_url }} for {{ app_name }}.',
            'status' => 'active',
            ...$overrides,
        ]);
    }
}
