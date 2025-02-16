<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;

class HomeController
{
    public function index()
    {
        $events = Event::all();
        
        return Inertia::render('Home', [
            'events' => $events
        ]);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);
        
        return Inertia::render('Event/Show', [
            'event' => $event
        ]);
    }
}
