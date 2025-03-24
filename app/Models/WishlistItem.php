<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'festival_id',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }
}
