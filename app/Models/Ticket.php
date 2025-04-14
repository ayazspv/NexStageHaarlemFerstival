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

    // Define the relationship with the FestivalEvent model
    public function event()
    {
        return $this->belongsTo(FestivalEvent::class, 'event_id'); // Use FestivalEvent
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
