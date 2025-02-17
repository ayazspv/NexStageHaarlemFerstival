<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festival extends Model
{
    protected $fillable = [
        'name',
        'image_path'
    ];
}