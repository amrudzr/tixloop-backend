<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'current_owner_id',
        'original_buyer_id',
        'ticket_code',
        'seat_number',
        'ticket_metadata',
        'ticket_proof_path',
        'ticket_proof_type',
        'proof_uploaded_at',
        'qr_secret_key',
        'device_binding_id',
        'status',
        'burned_at',
        'used_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ticket_metadata' => 'array',
            'proof_uploaded_at' => 'datetime',
            'burned_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the event this ticket belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the current owner of the ticket.
     */
    public function currentOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_owner_id');
    }

    /**
     * Get the original buyer of the ticket.
     */
    public function originalBuyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_buyer_id');
    }

    /**
     * Get the resale listing for this ticket.
     */
    public function resaleListing(): HasOne
    {
        return $this->hasOne(ResaleListing::class);
    }

    /**
     * Get the ownership history records for this ticket.
     */
    public function ownershipHistories(): HasMany
    {
        return $this->hasMany(TicketOwnershipHistory::class);
    }
}
