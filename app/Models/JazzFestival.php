<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JazzFestival extends Model
{
    protected $fillable = [
        'festival_id',
        'band_name',
        'performance_datetime',
        'ticket_price',
        'band_description',
        'band_details',
        'band_image',
        'second_image',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }
}
