<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FestivalEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'details',
        'time',
        'ticket_price',
        'image',
        'second_image',
        'day',
        'festival_id',
    ];

    /**
     * Get the festival associated with the event.
     */
    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    /**
     * Get the tickets associated with the event.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'event_id');
    }
}