<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResaleListingResource extends JsonResource
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
            'ticket_id' => $this->ticket_id,
            'seller_id' => $this->seller_id,
            'original_price' => (float) $this->original_price,
            'current_asking_price' => (float) $this->current_asking_price,
            'is_auto_drop' => (bool) $this->is_auto_drop,
            'floor_price' => $this->floor_price !== null ? (float) $this->floor_price : null,
            'hard_cap_price' => (float) $this->hard_cap_price,
            'verification_status' => $this->verification_status,
            'listing_status' => $this->listing_status,
            'listed_at' => $this->listed_at,
            'sold_at' => $this->sold_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ticket' => new TicketResource($this->whenLoaded('ticket')),
            'seller' => new UserResource($this->whenLoaded('seller')),
        ];
    }
}
