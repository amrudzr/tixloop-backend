<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'event_name',
        'event_category',
        'event_datetime',
        'venue_name',
        'city',
        'event_poster_url',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(ResaleListing::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
