<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use Inertia\Inertia;

class HomeController
{
    public function index()
    {
        $festivals = Festival::all();
        
        return Inertia::render('Home', [
            'festivals' => $festivals
        ]);
    }
}
