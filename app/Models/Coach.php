<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Coach extends Model
{
    use HasFactory,HasApiTokens;

     protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'gym_name',
        'phone',
        'password',
        'location',
        'profile_image',
        'status',
    ];

    public function players(){
        return $this->hasMany(Player::class);
    }

}
