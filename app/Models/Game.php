<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'festival_id',
        'title',
        'question',
        'option1',
        'option2',
        'option3',
        'option4',
        'correct_option',
        'hint',
        'thumbnail',
        'stamp',
    ];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }
}
