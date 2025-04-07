<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'order_id',
        'festival_id',
        'detail_ticket_id', // For example specific band, restaurant
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

    public function detail_ticket()
    {
        $festival = $this->festival();

        switch($festival->festivalType)
        {
            case 0:
                return JazzFestival::where('festival_id', $festival->id)->get();
            case 1:
                return null; // Replace with restaurants!!!!
            case 2:
                return null; // Replace with history!!!!

            default:
                throw new \Exception('Unsupported Festival type');
        }
    }
}
