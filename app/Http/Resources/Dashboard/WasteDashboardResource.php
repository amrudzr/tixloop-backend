<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WasteDashboardResource extends JsonResource
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
            'potential_impact' => $this->resource['potential_impact'],
            'listings_breakdown' => $this->resource['listings_breakdown'],
            'metadata' => $this->resource['metadata'],
        ];
    }
}
