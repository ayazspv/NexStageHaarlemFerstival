<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'festival_id',
        'quantity',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }
}
