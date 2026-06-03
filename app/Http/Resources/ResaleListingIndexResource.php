<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResaleListingIndexResource extends JsonResource
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
            'ticket' => [
                'id' => $this->ticket->id,
                'event' => [
                    'id' => $this->ticket->event->id,
                    'name' => $this->ticket->event->event_name,
                    'category' => $this->ticket->event->event_category,
                    'venue' => $this->ticket->event->venue_name,
                    'city' => $this->ticket->event->city,
                    'date' => $this->ticket->event->event_datetime,
                ],
                'type' => $this->ticket->ticket_metadata['type'] ?? null,
            ],
            'seller' => new UserPublicResource($this->whenLoaded('seller')),
            'listed_at' => $this->listed_at,
        ];
    }
}
