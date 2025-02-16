<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminPanelController
{
    public function show(): Response
    {
        error_log("Admin Panel");
        return Inertia::render('Admin/Home');
    }
}
