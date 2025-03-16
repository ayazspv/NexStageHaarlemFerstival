<?php

namespace App\Models;

class Ticket
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'event_id',
        'qr_code',
        'date_time'
    ];
}
