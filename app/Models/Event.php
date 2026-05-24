<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasUlids;

    protected $fillable = [
        'event_name',
        'event_category',
        'event_datetime',
        'venue_name',
        'city',
        'event_poster_url',
    ];

    public function listings()
    {
        return $this->hasMany(ResaleListing::class);
    }
}
