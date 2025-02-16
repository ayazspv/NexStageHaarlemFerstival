<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController
{
    public function show(): Response
    {
        // Return actual events from database
        return Inertia::render("Admin/Events", []);
    }
}
