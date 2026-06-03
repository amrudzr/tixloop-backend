<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'resale_listing_id',
        'ticket_id',
        'amount',
        'service_fee',
        'status',
        'escrow_status',
        'payment_reference',
        'paid_at',
        'released_at',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the buyer of this transaction.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller of this transaction.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the resale listing for this transaction.
     */
    public function resaleListing(): BelongsTo
    {
        return $this->belongsTo(ResaleListing::class);
    }

    /**
     * Get the ticket for this transaction.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the ownership history record for this transaction.
     */
    public function ownershipHistory(): HasOne
    {
        return $this->hasOne(TicketOwnershipHistory::class);
    }
}
