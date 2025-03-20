<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CMS extends Model
{
    protected $table = 'cms';

    protected $fillable = [
        'festival_id',
        'parent_id',
        'title',
        'content',
        'link',
        'image_path',
        'order',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    public function subpages()
    {
        return $this->hasMany(CMS::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(CMS::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('children');
    }
}
