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
        'is_used',
        'used_at',
        'redeemed_by',
    ];

    protected $casts = [
        'price_per_ticket' => 'decimal:2',
        'is_used' => 'boolean',
        'used_at' => 'datetime',
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

    // Helper method to check if ticket is redeemed
    public function isRedeemed()
    {
        return $this->is_used ?? false;
    }

    // Helper method to redeem ticket
    public function redeem($redeemedBy = null)
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'redeemed_by' => $redeemedBy,
        ]);
    }
}
