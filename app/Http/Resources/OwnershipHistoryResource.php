<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnershipHistoryResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'transferred_at' => $this->transferred_at?->toIso8601String(),
            'previous_owner' => $this->whenLoaded('previousOwner', fn () => $this->previousOwner ? [
                'id' => $this->previousOwner->id,
                'name' => $this->previousOwner->name,
            ] : null),
            'new_owner' => $this->whenLoaded('newOwner', fn () => $this->newOwner ? [
                'id' => $this->newOwner->id,
                'name' => $this->newOwner->name,
            ] : null),
        ];
    }
}
