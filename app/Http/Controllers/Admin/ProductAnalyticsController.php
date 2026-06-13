<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\AnalyticsFilterRequest;
use App\Http\Requests\Analytics\ExportAnalyticsRequest;
use App\Models\Domain;
use App\Models\Locale;
use App\Services\Analytics\AnalyticsDashboardService;
use App\Services\Analytics\AnalyticsDateRangeResolver;
use App\Services\Analytics\AnalyticsExportService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductAnalyticsController extends Controller
{
    public function index(AnalyticsFilterRequest $request, AnalyticsDateRangeResolver $dates, AnalyticsDashboardService $dashboard): View
    {
        $filters = [
            'preset' => (string) $request->query('preset', 'last_7_days'),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'domain_id' => (string) $request->query('domain_id', 'all'),
        ];
        $range = $dates->resolve([...$request->validated(), ...$filters]);

        return view('dashboard.product-analytics.index', [
            'adminUser' => $request->user(),
            'filters' => $filters,
            'range' => $range,
            'presets' => $dates->presets(),
            'locales' => Locale::query()->orderBy('sort_order')->orderBy('locale')->get(['id', 'locale', 'language_name']),
            'domains' => Domain::query()->orderBy('domain_name')->get(['id', 'domain_name']),
            'dashboard' => $dashboard->dashboard($range, $filters),
            'canExport' => $request->user()?->can('export analytics') ?? false,
        ]);
    }

    public function export(ExportAnalyticsRequest $request, AnalyticsDateRangeResolver $dates, AnalyticsExportService $export): StreamedResponse|Response
    {
        $filters = [
            'preset' => (string) $request->query('preset', 'last_7_days'),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
            'locale_id' => (string) $request->query('locale_id', 'all'),
            'domain_id' => (string) $request->query('domain_id', 'all'),
        ];
        $range = $dates->resolve([...$request->validated(), ...$filters]);

        return $export->csv($request->user(), $range, $filters);
    }
}
