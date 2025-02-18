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
        'content',
        'date',
        'link',
    ];

    protected $casts = [
        'content' => 'array',  // expects content to be stored as a JSON array (e.g. [content1, content2, ...])
        'date'    => 'datetime',
    ];

    public function getImageAttribute()
    {
        return $this->image_path;
    }
}
