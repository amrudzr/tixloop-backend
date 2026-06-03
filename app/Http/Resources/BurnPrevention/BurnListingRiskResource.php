<?php

namespace App\Http\Resources\BurnPrevention;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BurnListingRiskResource extends JsonResource
{
    /**
     * The resource wraps listing + assessment pair.
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $listing = $this->resource['listing'];
        $assessment = $this->resource['assessment'];

        return [
            'listing_id' => $listing->id,
            'event_name' => $listing->ticket->event->event_name,
            'event_datetime' => $listing->ticket->event->event_datetime->toIso8601String(),
            'time_to_event_hours' => (float) $assessment['time_to_event_hours'],
            'current_asking_price' => (float) $listing->current_asking_price,
            'floor_price' => (float) $listing->floor_price,
            'risk_level' => $assessment['risk_level'],
            'is_at_floor' => $assessment['is_at_floor'],
            'recommendation' => $assessment['recommendation'],
        ];
    }
}
