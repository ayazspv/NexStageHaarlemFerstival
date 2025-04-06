<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Festival extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'date',
        'link',
        'isGame',
        'festivalType',
        'ticket_amount',
        'time_slot',
    ];

    protected $casts = [
        'date' => 'datetime',
        'isGame' => 'boolean',
        'festivalType' => 'integer',
        'ticket_amount' => 'integer',
    ];

    public function getImageAttribute()
    {
        return $this->image_path;
    }

    // Relationship to CMS pages
    public function cmsPages()
    {
        return $this->hasMany(CMS::class);
    }
}
