<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'festival_id',
        'event_id',       // For specific events like jazz performances
        'ticket_type',    // 'standard', 'day_pass', 'full_pass', 'jazz_event'
        'quantity',
        'unit_price',
        'performance_day',
        'performance_time',
        'artist_name',
        'details',        // JSON field for additional details
    ];

    protected $casts = [
        'details' => 'array',
        'unit_price' => 'float',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    public function jazzEvent()
    {
        return $this->belongsTo(JazzFestival::class, 'event_id');
    }

    // Calculate total price for this cart item
    public function getTotalPriceAttribute()
    {
        return $this->unit_price * $this->quantity;
    }
}
