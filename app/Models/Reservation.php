<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'restaurant_id', 'name', 'adults', 'children', 'date', 'session', 'phone', 'email', 'special_request'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
