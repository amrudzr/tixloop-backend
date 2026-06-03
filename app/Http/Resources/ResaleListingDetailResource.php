<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResaleListingDetailResource extends JsonResource
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
            'current_asking_price' => $this->current_asking_price,
            'original_price' => $this->original_price,
            'floor_price' => $this->floor_price,
            'hard_cap_price' => $this->hard_cap_price,
            'verification_status' => $this->verification_status,
            'listing_status' => $this->listing_status,
            'ticket' => [
                'id' => $this->ticket->id,
                'event' => [
                    'id' => $this->ticket->event->id,
                    'name' => $this->ticket->event->event_name,
                    'category' => $this->ticket->event->event_category,
                    'venue' => $this->ticket->event->venue_name,
                    'city' => $this->ticket->event->city,
                    'date' => $this->ticket->event->event_date,
                ],
                'metadata' => $this->ticket->ticket_metadata,
            ],
            'seller' => new UserPublicResource($this->whenLoaded('seller')),
            'listed_at' => $this->listed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
