<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'order_id',
        'festival_id',
        'qr_code',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
