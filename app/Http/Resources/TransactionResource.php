<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'buyer' => new UserPublicResource($this->whenLoaded('buyer')),
            'seller' => new UserPublicResource($this->whenLoaded('seller')),
            'resale_listing_id' => $this->resale_listing_id,
            'ticket_id' => $this->ticket_id,
            'amount' => $this->amount,
            'service_fee' => $this->service_fee,
            'status' => $this->status,
            'escrow_status' => $this->escrow_status,
            'payment_reference' => $this->payment_reference,
            'paid_at' => $this->paid_at,
            'released_at' => $this->released_at,
            'released_by' => $this->released_by,
            'completed_at' => $this->completed_at,
            'ticket' => [
                'id' => $this->ticket->id,
                'current_owner_id' => $this->ticket->current_owner_id,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
