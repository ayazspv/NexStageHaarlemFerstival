<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Festival;
use Illuminate\Support\Facades\Auth;

class PersonalProgramController
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all festivals
        $festivals = Festival::all();
        
        // For the Personal Program, all festivals happen on all days
        $festivalsByDay = [
            24 => $festivals,
            25 => $festivals,
            26 => $festivals,
            27 => $festivals
        ];
            
        return Inertia::render('User/PersonalProgram', [
            'user' => $user,
            'festivals' => $festivals,
            'festivalsByDay' => $festivalsByDay
        ]);
    }
}