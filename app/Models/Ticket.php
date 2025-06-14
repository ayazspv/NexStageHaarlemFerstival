<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'festival_id',
        'qr_code',
        'quantity',
        'price_per_ticket',
    ];

    protected $casts = [
        'price_per_ticket' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->price_per_ticket;
    }
}
