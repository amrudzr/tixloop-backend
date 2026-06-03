<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketOwnershipHistory extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     */
    protected $table = 'ticket_ownership_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'transaction_id',
        'previous_owner_id',
        'new_owner_id',
        'transferred_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transferred_at' => 'datetime',
        ];
    }

    /**
     * Get the ticket this history entry belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the transaction that triggered this transfer.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the previous owner.
     */
    public function previousOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_owner_id');
    }

    /**
     * Get the new owner.
     */
    public function newOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_owner_id');
    }
}
