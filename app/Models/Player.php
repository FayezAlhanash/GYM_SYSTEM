<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
      use HasFactory;

    protected $fillable = [
        'coach_id',
        'full_name',
        'phone',
        'description',
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
