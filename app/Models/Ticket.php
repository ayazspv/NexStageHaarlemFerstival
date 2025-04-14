<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'order_id',
        'event_id',
        'qr_code',
    ];

    // Define the relationship with the Event model
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
