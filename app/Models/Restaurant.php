<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'cta_text',
        'subheader_1',
        'subheader_2',
        'accessibility',
        'vegan',
        'gluten_free',
        'halal',
        'adult_price',
        'children_price',
        'location',
        'contact_number',
        'picture_1',
        'picture_2',
        'picture_3',
        'session_1_time',
        'session_2_time',
        'session_3_time',
    ];

    protected $casts = [
        'rate' => 'float',
        'accessibility' => 'boolean',
        'vegan' => 'boolean',
        'gluten_free' => 'boolean',
        'halal' => 'boolean',
        'adult_price' => 'decimal:2',
        'children_price' => 'decimal:2',
        'session_1_time' => 'string',
        'session_2_time' => 'string',
        'session_3_time' => 'string',
    ];

    public function foodTypes()
    {
        return $this->belongsToMany(FoodType::class);
    }
}
