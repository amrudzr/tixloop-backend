<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\GetBurnPreventionRequest;
use App\Http\Resources\BurnPrevention\BurnPreventionMetricsResource;
use App\Services\BurnPrevention\BurnPreventionService;
use Illuminate\Http\JsonResponse;

class BurnPreventionController extends Controller
{
    public function __construct(private BurnPreventionService $service) {}

    /**
     * Retrieve burn prevention dashboard metrics.
     */
    public function index(GetBurnPreventionRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $metrics = $this->service->getMetrics($filters);

        // Attach user-specific at-risk listings if authenticated
        $user = $request->user();
        $metrics['user_listings_at_risk'] = $user
            ? $this->service->getUserListingsAtRisk($user->id, $filters)
            : [];

        return ApiResponse::success(
            new BurnPreventionMetricsResource($metrics),
            'Burn prevention dashboard metrics retrieved successfully',
        );
    }
}
