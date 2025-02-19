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
    ];

    protected $casts = [
        'date' => 'datetime',
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
