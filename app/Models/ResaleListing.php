<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ResaleListing extends Model
{
    use HasUlids;

    protected $fillable = [
        'event_id',
        'seller_id',
        'ticket_type',
        'seat_number',
        'original_price',
        'current_price',
        'floor_price',
        'burn_prevention_active',
        'last_price_drop_at',
        'verified_seller',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
