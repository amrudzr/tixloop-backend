<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\GetWasteDashboardRequest;
use App\Http\Resources\Dashboard\WasteDashboardResource;
use App\Services\Dashboard\WasteDashboardService;
use Illuminate\Http\JsonResponse;

class WasteDashboardController extends Controller
{
    public function __construct(private WasteDashboardService $service) {}

    /**
     * Retrieve waste dashboard sustainability metrics.
     */
    public function index(GetWasteDashboardRequest $request): JsonResponse
    {
        $metrics = $this->service->getMetrics($request->validated());

        return ApiResponse::success(
            new WasteDashboardResource($metrics),
            'Data Dasbor Dampak TixLoop berhasil diambil',
        );
    }
}
