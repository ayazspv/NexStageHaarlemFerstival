<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Style extends Model
{
    protected $fillable = [
        'cms_id',
        'name',
        'content',
    ];

    public function cms()
    {
        return $this->belongsTo(CMS::class, 'cms_id');
    }
}
