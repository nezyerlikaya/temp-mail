<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardLiveMetricsRequest;
use App\Services\Dashboard\DashboardLivePayloadFactory;
use Illuminate\Http\JsonResponse;

class DashboardLiveMetricsController extends Controller
{
    public function __invoke(DashboardLiveMetricsRequest $request, DashboardLivePayloadFactory $payload): JsonResponse
    {
        return response()->json($payload->make($request->user()));
    }
}
