<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JazzFestival extends Model
{
    protected $fillable = [
        'festival_id',
        'band_name',
        'performance_datetime',
        'performance_day', // field for the day (24, 25, 26, 27)
        'ticket_price',
        'band_description',
        'band_details',
        'band_image',
        'second_image',
    ];

    protected $casts = [
        'performance_day' => 'integer',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }
}
