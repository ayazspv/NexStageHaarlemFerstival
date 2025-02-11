<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function show()
    {
        if(!Auth::check()) {
            return redirect('/login')->withErrors(['message' => 'You are not authorized to access this page.']);
        }

        // Add logic here

        return Inertia::render('Admin/Home', []);
    }
}
