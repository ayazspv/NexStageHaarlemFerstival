<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JazzFestival extends Model
{
    use HasFactory;

    protected $fillable = [
        'festival_id',
        'band_name',
        'performance_datetime',
        'performance_day',
        'start_time',
        'end_time',
        'ticket_price',
        'band_description',
        'band_details',
        'band_image',
    ];

    protected $casts = [
        'performance_day' => 'integer',
        'ticket_price' => 'float',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }
}
