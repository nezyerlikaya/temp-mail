<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Api\ApiKeyService;
use App\Services\Api\ApiSettingsStore;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_api_access_page_renders_real_foundation(): void
    {
        $admin = $this->adminWithBusinessAccess();

        $this->actingAs($admin)->get(route('admin.api-access.index'))
            ->assertOk()
            ->assertSee('API Access')
            ->assertSee('Create API key')
            ->assertSee('mailbox:create')
            ->assertDontSee('Module workspace coming next');
    }

    public function test_secret_is_shown_once_and_only_hash_is_persisted(): void
    {
        [$admin, $owner] = $this->actorsWithEnabledApi();

        $response = $this->actingAs($admin)->post(route('admin.api-access.keys.store'), [
            'user_id' => $owner->id,
            'name' => 'Production worker',
            'environment' => 'live',
            'scopes' => ['mailbox:create', 'usage:read'],
        ])->assertRedirect()->assertSessionHas('api_secret_once');

        $secret = $response->baseResponse->getSession()->get('api_secret_once')['secret'];
        $key = ApiKey::query()->firstOrFail();

        $this->assertStringStartsWith('tm_live_', $secret);
        $this->assertNotSame($secret, $key->hashed_secret);
        $this->assertTrue(Hash::check($secret, $key->hashed_secret));
        $this->assertDatabaseHas('api_keys', ['key_prefix' => $key->key_prefix, 'status' => 'active']);

        $this->actingAs($admin)->get(route('admin.api-access.index'))->assertOk()->assertSee($secret);
        $this->actingAs($admin)->get(route('admin.api-access.index'))->assertOk()->assertDontSee($secret);
    }

    public function test_revoked_and_expired_keys_fail_authentication(): void
    {
        [$admin, $owner] = $this->actorsWithEnabledApi();
        $result = app(ApiKeyService::class)->create($admin, $owner, [
            'name' => 'Test worker',
            'environment' => 'test',
            'scopes' => ['mailbox:read'],
        ]);

        $this->assertNotNull(app(ApiKeyService::class)->authenticate($result['secret']));

        $this->actingAs($admin)->post(route('admin.api-access.keys.revoke', $result['key']), [
            'confirmation' => 'REVOKE',
        ])->assertRedirect();

        $this->assertNull(app(ApiKeyService::class)->authenticate($result['secret']));

        $expired = app(ApiKeyService::class)->create($admin, $owner, [
            'name' => 'Expired worker',
            'environment' => 'test',
            'scopes' => ['mailbox:read'],
        ]);
        $expired['key']->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->assertNull(app(ApiKeyService::class)->authenticate($expired['secret']));
    }

    public function test_regeneration_invalidates_previous_secret(): void
    {
        [$admin, $owner] = $this->actorsWithEnabledApi();
        $created = app(ApiKeyService::class)->create($admin, $owner, [
            'name' => 'Rotating worker',
            'environment' => 'test',
            'scopes' => ['message:read'],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.api-access.keys.regenerate', $created['key']), [
            'confirmation' => 'REGENERATE',
        ])->assertRedirect()->assertSessionHas('api_secret_once');

        $newSecret = $response->baseResponse->getSession()->get('api_secret_once')['secret'];

        $this->assertNull(app(ApiKeyService::class)->authenticate($created['secret']));
        $this->assertNotNull(app(ApiKeyService::class)->authenticate($newSecret));
    }

    public function test_plan_and_ownership_restrictions_are_enforced(): void
    {
        [$admin, $businessOwner] = $this->actorsWithEnabledApi();
        $freeUser = User::factory()->create(['role' => 'member', 'is_admin' => false, 'current_plan_reference' => 'free']);
        $otherAdmin = User::factory()->admin()->create(['current_plan_reference' => 'free']);

        $this->actingAs($admin)->post(route('admin.api-access.keys.store'), [
            'user_id' => $freeUser->id,
            'name' => 'Free blocked',
            'environment' => 'test',
            'scopes' => ['mailbox:read'],
        ])->assertForbidden();

        $created = app(ApiKeyService::class)->create($admin, $businessOwner, [
            'name' => 'Owned key',
            'environment' => 'test',
            'scopes' => ['mailbox:read'],
        ]);

        $this->actingAs($otherAdmin)->post(route('admin.api-access.keys.revoke', $created['key']), [
            'confirmation' => 'REVOKE',
        ])->assertRedirect();
    }

    public function test_ip_allowlist_and_scope_validation_are_friendly(): void
    {
        [$admin, $owner] = $this->actorsWithEnabledApi();

        $this->actingAs($admin)->from(route('admin.api-access.index'))->post(route('admin.api-access.keys.store'), [
            'user_id' => $owner->id,
            'name' => 'Bad network',
            'environment' => 'test',
            'scopes' => ['unknown:scope'],
            'ip_allowlist_text' => "127.0.0.1\nnot-an-ip",
        ])->assertRedirect(route('admin.api-access.index'))
            ->assertSessionHasErrors(['scopes.0', 'ip_allowlist.1']);
    }

    public function test_secret_is_absent_from_audit_metadata(): void
    {
        [$admin, $owner] = $this->actorsWithEnabledApi();

        $response = $this->actingAs($admin)->post(route('admin.api-access.keys.store'), [
            'user_id' => $owner->id,
            'name' => 'Audit worker',
            'environment' => 'test',
            'scopes' => ['usage:read'],
        ])->assertRedirect()->assertSessionHas('api_secret_once');

        $secret = $response->baseResponse->getSession()->get('api_secret_once')['secret'];
        $auditPayload = json_encode(UserAuditEvent::query()->pluck('metadata')->all());

        $this->assertStringNotContainsString($secret, $auditPayload);
        $this->assertStringContainsString('key_prefix', $auditPayload);
    }

    /** @return array{User, User} */
    private function actorsWithEnabledApi(): array
    {
        $admin = $this->adminWithBusinessAccess();
        $owner = User::factory()->create(['role' => 'member', 'is_admin' => false, 'current_plan_reference' => 'business', 'membership_status' => 'active']);
        app(ApiSettingsStore::class)->put([
            'api_enabled' => true,
            'business_api_enabled' => true,
            'premium_api_enabled' => false,
            'free_api_enabled' => false,
        ], $admin);

        return [$admin, $owner];
    }

    private function adminWithBusinessAccess(): User
    {
        app(PlanSettingsStore::class)->ensureDefaults();
        Plan::query()->where('key', 'business')->firstOrFail()->limits()->update(['api_access_allowed' => true]);

        return User::factory()->admin()->create(['current_plan_reference' => 'business', 'membership_status' => 'active']);
    }
}
