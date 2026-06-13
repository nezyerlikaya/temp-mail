<?php

namespace Tests\Feature;

use App\Models\IntegrationSetting;
use App\Models\User;
use App\Services\Integrations\IntegrationSecretStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IntegrationsCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_integrations_center_renders_registry_categories_and_settings_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.integrations.index'))
            ->assertOk()
            ->assertSee('Integrations Center')
            ->assertSee('Email Delivery')
            ->assertSee('Payments')
            ->assertSee('Mailgun')
            ->assertSee('Stripe')
            ->assertSee('Required fields')
            ->assertDontSee('Module workspace coming next');

        $this->actingAs($admin)->get(route('admin.integrations.index', ['category' => 'payments']))
            ->assertOk()
            ->assertSee('Stripe')
            ->assertSee('Paddle')
            ->assertSee('Iyzico readiness')
            ->assertDontSee('Mailgun');
    }

    public function test_configuration_saves_payload_and_encrypted_masked_secret_without_audit_leak(): void
    {
        $admin = User::factory()->admin()->create();
        $secret = 'sk_test_super_secret_value';

        $this->actingAs($admin)->put(route('admin.integrations.update', 'stripe'), [
            'environment' => 'sandbox',
            'settings' => [
                'publishable_key' => 'pk_test_public',
            ],
            'secrets' => [
                'secret_key' => $secret,
            ],
        ])->assertRedirect(route('admin.integrations.index', ['integration' => 'stripe', 'environment' => 'sandbox']));

        $setting = IntegrationSetting::query()->where('integration_key', 'stripe')->where('environment', 'sandbox')->firstOrFail();
        $this->assertSame('pk_test_public', $setting->payload['publishable_key']);
        $this->assertNotNull($setting->encrypted_secrets);
        $this->assertStringNotContainsString($secret, DB::table('integration_settings')->where('id', $setting->id)->value('encrypted_secrets'));
        $this->assertSame($secret, json_decode(Crypt::decryptString($setting->encrypted_secrets), true, flags: JSON_THROW_ON_ERROR)['secret_key']);

        $this->actingAs($admin)->get(route('admin.integrations.index', ['integration' => 'stripe']))
            ->assertOk()
            ->assertSee('********')
            ->assertDontSee($secret);

        $auditPayload = json_encode(DB::table('user_audit_events')->where('event', 'integrations.settings_updated')->latest('id')->first(), JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString($secret, $auditPayload);
    }

    public function test_blank_secret_preserves_existing_value_and_deactivation_preserves_configuration(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.integrations.update', 'stripe'), [
            'environment' => 'sandbox',
            'settings' => ['publishable_key' => 'pk_test_public'],
            'secrets' => ['secret_key' => 'keep-this-secret'],
        ])->assertRedirect();

        $before = IntegrationSetting::query()->where('integration_key', 'stripe')->where('environment', 'sandbox')->firstOrFail();
        $rawBefore = DB::table('integration_settings')->where('id', $before->id)->value('encrypted_secrets');

        $this->actingAs($admin)->put(route('admin.integrations.update', 'stripe'), [
            'environment' => 'sandbox',
            'settings' => ['publishable_key' => 'pk_test_public_updated'],
            'secrets' => ['secret_key' => ''],
        ])->assertRedirect();

        $after = $before->refresh();
        $this->assertSame('keep-this-secret', json_decode(Crypt::decryptString($after->encrypted_secrets), true, flags: JSON_THROW_ON_ERROR)['secret_key']);
        $this->assertNotNull($rawBefore);

        $this->actingAs($admin)->post(route('admin.integrations.activate', 'stripe'), ['environment' => 'sandbox'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.integrations.deactivate', 'stripe'), ['environment' => 'sandbox'])->assertRedirect();

        $after->refresh();
        $this->assertFalse($after->is_active);
        $this->assertSame('pk_test_public_updated', $after->payload['publishable_key']);
        $this->assertSame('keep-this-secret', json_decode(Crypt::decryptString($after->encrypted_secrets), true, flags: JSON_THROW_ON_ERROR)['secret_key']);
    }

    public function test_sandbox_and_production_environments_are_separate(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.integrations.update', 'stripe'), [
            'environment' => 'sandbox',
            'settings' => ['publishable_key' => 'pk_test_public'],
            'secrets' => ['secret_key' => 'sandbox-secret'],
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('admin.integrations.update', 'stripe'), [
            'environment' => 'production',
            'settings' => ['publishable_key' => 'pk_live_public'],
            'secrets' => ['secret_key' => 'production-secret'],
        ])->assertRedirect();

        $this->assertDatabaseHas('integration_settings', ['integration_key' => 'stripe', 'environment' => 'sandbox']);
        $this->assertDatabaseHas('integration_settings', ['integration_key' => 'stripe', 'environment' => 'production']);

        $store = app(IntegrationSecretStore::class);
        $sandbox = IntegrationSetting::query()->where('integration_key', 'stripe')->where('environment', 'sandbox')->firstOrFail();
        $production = IntegrationSetting::query()->where('integration_key', 'stripe')->where('environment', 'production')->firstOrFail();

        $this->assertSame('sandbox-secret', $store->decrypt($sandbox->encrypted_secrets)['secret_key']);
        $this->assertSame('production-secret', $store->decrypt($production->encrypted_secrets)['secret_key']);
    }

    public function test_permissions_and_owner_secret_reveal(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();

        $this->actingAs($owner)->put(route('admin.integrations.update', 'stripe'), [
            'environment' => 'sandbox',
            'settings' => ['publishable_key' => 'pk_test_public'],
            'secrets' => ['secret_key' => 'owner-secret'],
        ])->assertRedirect();

        $this->actingAs($editor)->get(route('admin.integrations.index'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.integrations.update', 'stripe'), [
            'environment' => 'sandbox',
        ])->assertForbidden();

        $this->actingAs($admin)->get(route('admin.integrations.secrets.reveal', ['integration' => 'stripe', 'field' => 'secret_key']))
            ->assertForbidden();

        $this->actingAs($owner)->get(route('admin.integrations.secrets.reveal', ['integration' => 'stripe', 'field' => 'secret_key']))
            ->assertOk()
            ->assertJson(['value' => 'owner-secret']);
    }

    public function test_no_forbidden_patterns_and_route_is_real(): void
    {
        $this->assertTrue(Route::has('admin.integrations.index'));

        $paths = [
            app_path('Services/Integrations'),
            app_path('Actions/Integrations'),
            app_path('Http/Controllers/Admin/IntegrationController.php'),
            app_path('Http/Requests/Integrations'),
            resource_path('views/components/integrations'),
            resource_path('views/dashboard/integrations'),
            base_path('routes/web.php'),
        ];

        $source = collect($paths)
            ->flatMap(fn (string $path) => File::isDirectory($path) ? File::allFiles($path) : [$path])
            ->map(fn ($file): string => File::get((string) $file))
            ->implode("\n");

        $this->assertStringNotContainsString('cdn.tailwindcss', $source);
        $this->assertStringNotContainsString('Livewire', $source);
        $this->assertStringNotContainsString('livewire', $source);
        $this->assertStringNotContainsString('127.0.0.1', $source);
        $this->assertStringNotContainsString('http://localhost', $source);
    }
}
