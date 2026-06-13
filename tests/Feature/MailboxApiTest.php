<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\Plan;
use App\Models\User;
use App\Services\Api\ApiKeyService;
use App\Services\Api\ApiSettingsStore;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_versioned_mailbox_api_creates_and_lists_owned_mailboxes(): void
    {
        [$secret] = $this->apiSecret(['mailbox:create', 'mailbox:read']);
        $domain = $this->domain();

        $created = $this->withToken($secret)->postJson(route('api.v1.mailboxes.store'), [
            'domain' => $domain->domain_name,
            'local_part' => 'demo-box',
        ])->assertCreated()
            ->assertJsonPath('error', null)
            ->assertJsonPath('data.address', 'demo-box@example.test')
            ->assertJsonPath('meta.environment', 'test');

        $this->assertDatabaseHas('mailboxes', [
            'address' => 'demo-box@example.test',
            'api_environment' => 'test',
        ]);

        $this->withToken($secret)->getJson(route('api.v1.mailboxes.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $created->json('data.id'))
            ->assertJsonStructure(['data', 'meta' => ['pagination'], 'error']);
    }

    public function test_scope_and_ownership_are_enforced_for_private_mailbox_data(): void
    {
        [$secret, $key, $owner] = $this->apiSecret(['mailbox:read']);
        [, , $otherOwner] = $this->apiSecret(['mailbox:read']);
        $mailbox = $this->mailbox($otherOwner, 'test');

        $this->withToken($secret)->getJson(route('api.v1.mailboxes.show', $mailbox))
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');

        $this->withToken($secret)->deleteJson(route('api.v1.mailboxes.destroy', $this->mailbox($owner, 'test')))
            ->assertForbidden()
            ->assertJsonPath('error.code', 'missing_scope');

        $this->assertSame('test', $key->environment);
    }

    public function test_messages_are_available_only_for_owned_mailboxes_and_are_not_logged(): void
    {
        [$secret, , $owner] = $this->apiSecret(['message:read']);
        $mailbox = $this->mailbox($owner, 'test');
        $message = $mailbox->messages()->create([
            'sender_email' => 'sender@example.test',
            'subject' => 'Private subject',
            'preview_text' => 'Preview only',
            'plain_text_body' => 'Very private body content',
            'sanitized_html_body' => '<p>Very private body content</p>',
            'raw_headers' => ['X-Test' => 'ok'],
            'received_at' => now(),
        ]);

        $this->withToken($secret)->getJson(route('api.v1.mailboxes.messages.index', $mailbox))
            ->assertOk()
            ->assertJsonPath('data.0.id', $message->id)
            ->assertJsonMissing(['plain_text_body' => 'Very private body content']);

        $this->withToken($secret)->getJson(route('api.v1.mailboxes.messages.show', [$mailbox, $message]))
            ->assertOk()
            ->assertJsonPath('data.plain_text_body', 'Very private body content');

        $payload = json_encode(ApiRequestLog::query()->pluck('endpoint')->all());
        $this->assertStringNotContainsString('Very private body content', $payload);
    }

    public function test_domains_usage_request_logs_and_rate_limits_work(): void
    {
        [$secret, $key] = $this->apiSecret(['domain:read', 'usage:read'], requestLimit: 2);
        $this->domain();

        $this->withToken($secret)->getJson(route('api.v1.domains.index'))
            ->assertOk()
            ->assertJsonPath('data.0.domain', 'example.test');

        $this->withToken($secret)->getJson(route('api.v1.usage.show'))
            ->assertOk()
            ->assertJsonPath('data.limit', 2);

        $this->withToken($secret)->getJson(route('api.v1.usage.show'))
            ->assertStatus(429)
            ->assertJsonPath('error.code', 'rate_limit_exceeded');

        $this->assertDatabaseHas('api_request_logs', ['key_prefix' => $key->key_prefix, 'response_status' => 429]);
        $this->assertDatabaseHas('abuse_signals', ['signal_type' => 'rate_limited_request', 'source_module' => 'api']);
        $this->assertDatabaseHas('analytics_events', ['event_key' => 'security.rate_limited']);
    }

    public function test_invalid_key_and_validation_use_consistent_error_shape(): void
    {
        $this->postJson(route('api.v1.mailboxes.store'), [])->assertUnauthorized()
            ->assertJsonStructure(['data', 'meta', 'error' => ['code', 'message']]);

        [$secret] = $this->apiSecret(['mailbox:create']);
        $this->domain();

        $this->withToken($secret)->postJson(route('api.v1.mailboxes.store'), [
            'local_part' => str_repeat('x', 80),
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_api_access_admin_page_includes_docs_usage_and_safe_logs(): void
    {
        [$secret, , $owner] = $this->apiSecret(['usage:read']);
        $admin = User::factory()->admin()->create(['current_plan_reference' => 'business', 'membership_status' => 'active']);
        $this->withToken($secret)->getJson(route('api.v1.usage.show'))->assertOk();

        $this->actingAs($admin)->get(route('admin.api-access.index'))
            ->assertOk()
            ->assertSee('API documentation')
            ->assertSee('/api/v1/mailboxes')
            ->assertSee('Safe request log')
            ->assertSee('Requests today')
            ->assertDontSee($secret)
            ->assertSee($owner->email);
    }

    /** @param array<int, string> $scopes @return array{string, ApiKey, User} */
    private function apiSecret(array $scopes, int $requestLimit = 100): array
    {
        app(PlanSettingsStore::class)->ensureDefaults();
        Plan::query()->where('key', 'business')->firstOrFail()->limits()->update([
            'api_access_allowed' => true,
            'api_request_limit' => $requestLimit,
        ]);
        $admin = User::factory()->admin()->create(['current_plan_reference' => 'business', 'membership_status' => 'active']);
        $owner = User::factory()->create(['current_plan_reference' => 'business', 'membership_status' => 'active']);
        app(ApiSettingsStore::class)->put([
            'api_enabled' => true,
            'business_api_enabled' => true,
            'premium_api_enabled' => false,
            'free_api_enabled' => false,
        ], $admin);

        $result = app(ApiKeyService::class)->create($admin, $owner, [
            'name' => 'API test key',
            'environment' => 'test',
            'scopes' => $scopes,
        ]);

        return [$result['secret'], $result['key']->load('user'), $owner];
    }

    private function domain(): Domain
    {
        return Domain::query()->create([
            'domain_name' => 'example.test',
            'display_name' => 'Example',
            'is_active' => true,
            'is_public' => true,
            'catch_all_ready' => true,
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    private function mailbox(User $owner, string $environment): Mailbox
    {
        $domain = Domain::query()->first() ?: $this->domain();

        return Mailbox::query()->create([
            'domain_id' => $domain->id,
            'user_id' => $owner->id,
            'address' => uniqid('box').'@'.$domain->domain_name,
            'local_part' => uniqid('box'),
            'mailbox_type' => 'api_test',
            'status' => 'active',
            'expires_at' => now()->addHour(),
            'message_count' => 0,
            'api_environment' => $environment,
        ])->load('domain');
    }
}
