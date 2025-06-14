<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'ordered_at',
        'payment_details',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'payment_details' => 'array',
        'total_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getTotalTicketsAttribute()
    {
        return $this->tickets->sum('quantity');
    }
}
