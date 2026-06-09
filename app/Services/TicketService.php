<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Upload a ticket with proof file and optional physical photo.
     *
     * @param  array{event_id: string, ticket_code: string, seat_number: ?string, original_price: float, ticket_type: ?string}  $data
     */
    public function uploadTicket(
        User $user,
        array $data,
        UploadedFile $ticketProof,
        ?UploadedFile $physicalPhoto = null,
    ): Ticket {
        return DB::transaction(function () use ($user, $data, $ticketProof, $physicalPhoto) {
            $disk = config('filesystems.tickets_disk', 'local');
            $proofPath = $ticketProof->store('ticket_proofs', $disk);
            $proofType = $ticketProof->getClientMimeType();

            $metadata = [
                'original_price' => (float) $data['original_price'],
            ];

            if (! empty($data['ticket_type'])) {
                $metadata['type'] = $data['ticket_type'];
            }

            if ($physicalPhoto) {
                $physicalPath = $physicalPhoto->store('ticket_proofs', $disk);
                $metadata['physical_photo_path'] = $physicalPath;
            }

            return Ticket::create([
                'event_id' => $data['event_id'],
                'current_owner_id' => $user->id,
                'original_buyer_id' => $user->id,
                'ticket_code' => $data['ticket_code'],
                'seat_number' => $data['seat_number'] ?? null,
                'ticket_metadata' => $metadata,
                'ticket_proof_path' => $proofPath,
                'ticket_proof_type' => $proofType,
                'proof_uploaded_at' => now(),
                'status' => 'aktif',
            ]);
        });
    }
}
