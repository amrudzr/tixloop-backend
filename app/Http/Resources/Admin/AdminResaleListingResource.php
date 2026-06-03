<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResaleListingResource extends JsonResource
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
            'original_price' => $this->original_price,
            'current_asking_price' => $this->current_asking_price,
            'floor_price' => $this->floor_price,
            'hard_cap_price' => $this->hard_cap_price,
            'verification_status' => $this->verification_status,
            'listing_status' => $this->listing_status,
            'verified_at' => $this->verified_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'listed_at' => $this->listed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'seller' => [
                'id' => $this->seller?->id,
                'name' => $this->seller?->name,
                'email' => $this->seller?->email,
            ],
            'ticket' => [
                'id' => $this->ticket?->id,
                'ticket_code' => $this->ticket?->ticket_code,
                'seat_number' => $this->ticket?->seat_number,
                'ticket_proof_path' => $this->ticket?->ticket_proof_path,
                'ticket_proof_type' => $this->ticket?->ticket_proof_type,
                'event' => $this->ticket?->event ? [
                    'id' => $this->ticket->event->id,
                    'name' => $this->ticket->event->event_name,
                    'venue' => $this->ticket->event->venue_name,
                    'city' => $this->ticket->event->city,
                ] : null,
            ],
            'verified_by' => $this->verifiedByAdmin ? [
                'id' => $this->verifiedByAdmin->id,
                'name' => $this->verifiedByAdmin->name,
            ] : null,
        ];
    }
}
