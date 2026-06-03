<?php

namespace App\Http\Resources\BurnPrevention;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BurnPreventionMetricsResource extends JsonResource
{
    /**
     * The resource wraps a raw array, not a model.
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'total_value_at_risk' => (float) $this->resource['summary']['total_value_at_risk'],
                'high_risk_count' => (int) $this->resource['summary']['high_risk_count'],
                'medium_risk_count' => (int) $this->resource['summary']['medium_risk_count'],
                'low_risk_count' => (int) $this->resource['summary']['low_risk_count'],
                'historical_wasted_tickets' => (int) $this->resource['summary']['historical_wasted_tickets'],
            ],
            'user_listings_at_risk' => BurnListingRiskResource::collection(
                collect($this->resource['user_listings_at_risk'] ?? [])
            ),
            'metadata' => $this->resource['metadata'],
        ];
    }
}
