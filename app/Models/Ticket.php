<?php

namespace App\Models;

class Ticket
{
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'event_id',
        'qr_code',
        'date_time'
    ];
}
