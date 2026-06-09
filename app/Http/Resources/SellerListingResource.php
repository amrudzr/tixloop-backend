<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Seller dashboard resource — includes rejection_reason, full ticket + event info.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_price' => (float) $this->original_price,
            'current_asking_price' => (float) $this->current_asking_price,
            'floor_price' => (float) $this->floor_price,
            'hard_cap_price' => (float) $this->hard_cap_price,
            'verification_status' => $this->verification_status,
            'listing_status' => $this->listing_status,
            'rejection_reason' => $this->when(
                $this->verification_status === 'rejected',
                $this->rejection_reason
            ),
            'ticket' => [
                'id' => $this->ticket->id,
                'seat_number' => $this->ticket->seat_number,
                'status' => $this->ticket->status,
                'event' => [
                    'id' => $this->ticket->event->id,
                    'name' => $this->ticket->event->event_name,
                    'category' => $this->ticket->event->event_category,
                    'venue' => $this->ticket->event->venue_name,
                    'city' => $this->ticket->event->city,
                    'date' => $this->ticket->event->event_datetime,
                    'poster_url' => $this->ticket->event->event_poster_url,
                ],
                'type' => $this->ticket->ticket_metadata['type'] ?? null,
            ],
            'listed_at' => $this->listed_at,
            'sold_at' => $this->sold_at,
            'verified_at' => $this->verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
