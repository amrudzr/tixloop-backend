<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_name' => $this->event_name,
            'event_category' => $this->event_category,
            'event_datetime' => $this->event_datetime,
            'venue_name' => $this->venue_name,
            'city' => $this->city,
            'event_poster_url' => $this->event_poster_url,
        ];
    }
}
