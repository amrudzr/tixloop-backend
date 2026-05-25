<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'event_id' => $this->event_id,
            'seller_id' => $this->seller_id,
            'ticket_type' => $this->ticket_type,
            'seat_number' => $this->seat_number,
            'price' => (float) $this->price,
            'ticket_file_path' => $this->ticket_file_path,
            'is_verified' => (bool) $this->is_verified,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'event' => new EventResource($this->whenLoaded('event')),
            'seller' => new UserResource($this->whenLoaded('seller')),
        ];
    }
}
