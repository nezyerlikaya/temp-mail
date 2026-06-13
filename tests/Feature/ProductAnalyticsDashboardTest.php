<?php

namespace Tests\Feature;

use App\Models\AnalyticsDailyMetric;
use App\Models\Domain;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductAnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_product_analytics_dashboard_renders_real_center_with_privacy_and_stale_messages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.product-analytics.index'))
            ->assertOk()
            ->assertSee('Product Analytics')
            ->assertSee('Privacy-first analytics')
            ->assertSee('Aggregation needs attention')
            ->assertSee('Analytics filters')
            ->assertSee('Mailbox Activity')
            ->assertDontSee('Module workspace coming next');
    }

    public function test_kpis_filters_charts_and_top_lists_use_aggregate_rows(): void
    {
        $admin = User::factory()->admin()->create();
        [$domain, $locale] = $this->fixtures();
        $this->metric('mailbox.created', 8, 3, $domain, $locale);
        $this->metric('mailbox.email_received', 17, 4, $domain, $locale);
        $this->metric('user.registered', 4, 4, $domain, $locale);
        $this->metric('premium.granted', 2, 2, $domain, $locale);
        $this->metric('blog.viewed', 11, 6, $domain, $locale);

        $this->actingAs($admin)->get(route('admin.product-analytics.index', [
            'preset' => 'today',
            'locale_id' => $locale->id,
            'domain_id' => $domain->id,
        ]))->assertOk()
            ->assertSee('8')
            ->assertSee('17')
            ->assertSee('11')
            ->assertSee('analytics.example')
            ->assertSee('en')
            ->assertSee('Aggregates current');
    }

    public function test_custom_date_range_validation_and_empty_states(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.product-analytics.index', ['preset' => 'custom']))
            ->assertSessionHasErrors(['date_from', 'date_to']);

        $this->actingAs($admin)->get(route('admin.product-analytics.index', [
            'preset' => 'custom',
            'date_from' => today()->subDays(10)->toDateString(),
            'date_to' => today()->subDays(8)->toDateString(),
        ]))->assertOk()
            ->assertSee('No trend data')
            ->assertSee('No aggregate rows');
    }

    public function test_csv_export_is_owner_admin_only_audited_and_aggregate_only(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();
        [$domain, $locale] = $this->fixtures();
        $this->metric('mailbox.email_received', 5, 2, $domain, $locale, [
            'message_body' => 'PRIVATE BODY',
            'authorization' => 'Bearer secret-token',
        ]);

        $this->actingAs($editor)->get(route('admin.product-analytics.export'))->assertForbidden();

        $response = $this->actingAs($admin)->get(route('admin.product-analytics.export', ['preset' => 'today']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('metric_date,event_key,locale_id,domain_id,total_count,unique_visitors', $csv);
        $this->assertStringContainsString('mailbox.email_received', $csv);
        $this->assertStringNotContainsString('PRIVATE BODY', $csv);
        $this->assertStringNotContainsString('secret-token', $csv);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'analytics.exported', 'actor_id' => $admin->id]);
    }

    public function test_routes_are_named_and_export_action_visible_in_route_list(): void
    {
        $this->assertTrue(Route::has('admin.product-analytics.index'));
        $this->assertTrue(Route::has('admin.product-analytics.export'));
    }

    /** @return array{Domain, Locale} */
    private function fixtures(): array
    {
        $domain = Domain::query()->create([
            'domain_name' => 'analytics.example',
            'display_name' => 'Analytics',
            'is_active' => true,
            'is_public' => true,
            'catch_all_ready' => true,
            'status' => 'active',
        ]);
        $locale = Locale::query()->create([
            'language_name' => 'English',
            'native_name' => 'English',
            'locale' => 'en',
            'direction' => 'ltr',
            'region' => 'US',
            'market_readiness' => 'ready',
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 1,
            'launch_status' => 'active',
        ]);

        return [$domain, $locale];
    }

    /** @param array<string, mixed> $metadata */
    private function metric(string $eventKey, int $total, int $unique, Domain $domain, Locale $locale, array $metadata = []): void
    {
        AnalyticsDailyMetric::query()->create([
            'metric_date' => today()->toDateString(),
            'event_key' => $eventKey,
            'locale_id' => $locale->id,
            'domain_id' => $domain->id,
            'total_count' => $total,
            'unique_visitors' => $unique,
            'metadata' => ['source' => 'test', ...$metadata],
        ]);
    }
}
