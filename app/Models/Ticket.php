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
        'event_id',      // For specific events
        'qr_code',
        'quantity',
        'price_per_ticket',
        'ticket_type',    // 'standard', 'day_pass', 'full_pass', 'jazz_event'
        'ticket_details', // JSON field for additional details
        'is_used',
        'used_at',
        'redeemed_by',
    ];

    protected $casts = [
        'price_per_ticket' => 'decimal:2',
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'ticket_details' => 'array',
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

    // Add new relationship to JazzFestival
    public function jazzEvent()
    {
        return $this->belongsTo(JazzFestival::class, 'event_id');
    }
}
