<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResaleListing extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'seller_id',
        'original_price',
        'current_asking_price',
        'floor_price',
        'hard_cap_price',
        'verification_status',
        'listing_status',
        'listed_at',
        'sold_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'current_asking_price' => 'decimal:2',
            'floor_price' => 'decimal:2',
            'hard_cap_price' => 'decimal:2',
            'listed_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    /**
     * Get the ticket being listed for resale.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the seller of this listing.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
