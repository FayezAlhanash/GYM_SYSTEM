<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
     protected $fillable = [
        'player_id',
        'start_date',
        'end_date',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
