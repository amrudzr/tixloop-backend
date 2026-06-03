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
            'current_owner_id' => $this->current_owner_id,
            'original_buyer_id' => $this->original_buyer_id,
            'ticket_code' => $this->ticket_code,
            'seat_number' => $this->seat_number,
            'ticket_proof_path' => $this->ticket_proof_path,
            'ticket_proof_type' => $this->ticket_proof_type,
            'proof_uploaded_at' => $this->proof_uploaded_at,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'event' => new EventResource($this->whenLoaded('event')),
            'current_owner' => new UserResource($this->whenLoaded('currentOwner')),
        ];
    }
}
