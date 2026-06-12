<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use App\Services\Domains\DomainDnsCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DomainManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_domains_render_inside_admin_shell_and_replace_placeholder(): void
    {
        $admin = User::factory()->admin()->create();
        Domain::query()->create([
            'domain_name' => 'inbox.example',
            'display_name' => 'Primary inbox',
            'is_active' => true,
            'is_public' => true,
            'is_default' => true,
            'status' => 'ready',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.domains.index'))
            ->assertOk()
            ->assertSee('Domain Operations Center')
            ->assertSee('Receiving domains')
            ->assertSee('DNS ready')
            ->assertSee('inbox.example')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_domain_validation_duplicate_prevention_and_audit(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.domains.store'), [
                'domain_name' => 'Temp-Mail.Example',
                'display_name' => 'Temp Mail',
                'is_active' => '1',
                'is_public' => '1',
                'catch_all_ready' => '0',
                'is_default' => '1',
                'sort_order' => 10,
                'status' => 'pending_dns',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('domains', [
            'domain_name' => 'temp-mail.example',
            'is_active' => true,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('user_audit_events', [
            'event' => 'domain.created',
            'actor_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.domains.create'))
            ->post(route('admin.domains.store'), [
                'domain_name' => 'temp-mail.example',
                'display_name' => 'Duplicate',
                'sort_order' => 11,
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.domains.create'))
            ->assertSessionHasErrors('domain_name');
    }

    public function test_only_one_default_domain_is_allowed_and_default_must_remain_active(): void
    {
        $admin = User::factory()->admin()->create();
        $first = Domain::query()->create([
            'domain_name' => 'first.example',
            'display_name' => 'First',
            'is_active' => true,
            'is_default' => true,
            'status' => 'ready',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
        $second = Domain::query()->create([
            'domain_name' => 'second.example',
            'display_name' => 'Second',
            'is_active' => true,
            'status' => 'ready',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.domains.default', $second))
            ->assertRedirect(route('admin.domains.index'));

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);

        $this->actingAs($admin)
            ->from(route('admin.domains.edit', $second))
            ->put(route('admin.domains.update', $second), [
                'domain_name' => 'second.example',
                'display_name' => 'Second',
                'is_active' => '0',
                'is_default' => '1',
                'sort_order' => 20,
                'status' => 'ready',
            ])
            ->assertRedirect(route('admin.domains.edit', $second))
            ->assertSessionHasErrors('is_default');
    }

    public function test_default_and_final_active_domain_are_protected_from_deactivation(): void
    {
        $admin = User::factory()->admin()->create();
        $default = Domain::query()->create([
            'domain_name' => 'default.example',
            'display_name' => 'Default',
            'is_active' => true,
            'is_default' => true,
            'status' => 'ready',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.domains.status', $default), ['status_action' => 'deactivate'])
            ->assertSessionHasErrors('domain');

        $default->forceFill(['is_default' => false])->save();

        $this->actingAs($admin)
            ->patch(route('admin.domains.status', $default), ['status_action' => 'deactivate'])
            ->assertSessionHasErrors('domain');

        $this->assertTrue($default->refresh()->is_active);
    }

    public function test_dns_checks_fail_gracefully_and_do_not_expose_secrets(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = Domain::query()->create([
            'domain_name' => 'restricted.example',
            'display_name' => 'Restricted DNS',
            'is_active' => true,
            'status' => 'pending_dns',
        ]);

        $mock = Mockery::mock(DomainDnsCheckService::class);
        $mock->shouldReceive('check')->once()->andReturn([
            'mx' => [
                'type' => 'MX',
                'host' => 'restricted.example',
                'expected' => 'Inbound mail provider target',
                'detected' => [],
                'status' => 'unavailable',
                'guidance' => 'DNS check could not complete on this hosting environment.',
            ],
        ]);
        $mock->shouldReceive('expectedRecords')->andReturn([
            'ownership' => ['type' => 'TXT', 'host' => '_tempmail-verification.restricted.example', 'value' => 'tempmail-site-verification=test'],
        ]);
        $this->app->instance(DomainDnsCheckService::class, $mock);

        config(['mail.mailers.smtp.password' => 'smtp-secret-value']);

        $this->actingAs($admin)
            ->post(route('admin.domains.dns-check', $domain))
            ->assertRedirect(route('admin.domains.edit', $domain));

        $domain->refresh();
        $this->assertSame('pending_dns', $domain->status);
        $this->assertDatabaseHas('user_audit_events', [
            'event' => 'domain.dns_check_executed',
            'actor_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.domains.edit', $domain))
            ->assertOk()
            ->assertSee('DNS check could not complete')
            ->assertDontSee('smtp-secret-value');
    }

    public function test_degraded_domain_health_has_notification_readiness(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $domain = Domain::query()->create([
            'domain_name' => 'degraded.example',
            'display_name' => 'Degraded',
            'is_active' => true,
            'status' => 'pending_dns',
        ]);

        $mock = Mockery::mock(DomainDnsCheckService::class);
        $mock->shouldReceive('check')->once()->andReturn([
            'mx' => ['type' => 'MX', 'host' => 'degraded.example', 'expected' => 'mx', 'detected' => ['10 mx.example'], 'status' => 'ready', 'guidance' => 'MX routing detected.'],
            'spf' => ['type' => 'TXT', 'host' => 'degraded.example', 'expected' => 'spf', 'detected' => [], 'status' => 'missing', 'guidance' => 'Add SPF.'],
        ]);
        $this->app->instance(DomainDnsCheckService::class, $mock);

        $this->actingAs($admin)
            ->post(route('admin.domains.dns-check', $domain))
            ->assertRedirect();

        $this->assertSame('degraded', $domain->refresh()->status);
        $this->assertDatabaseHas('system_notifications', [
            'recipient_user_id' => $owner->id,
            'event_key' => 'domain_health_failed',
            'target_type' => Domain::class,
            'target_id' => $domain->id,
        ]);
    }

    public function test_domain_permissions_are_enforced(): void
    {
        $member = User::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);

        $this->actingAs($member)
            ->get(route('admin.domains.index'))
            ->assertForbidden();

        $this->actingAs($moderator)
            ->get(route('admin.domains.index'))
            ->assertOk();

        $this->actingAs($moderator)
            ->get(route('admin.domains.create'))
            ->assertForbidden();
    }
}
