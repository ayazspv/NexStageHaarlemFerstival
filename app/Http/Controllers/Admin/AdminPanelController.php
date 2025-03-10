<?php

namespace App\Http\Controllers\Admin;

use App\Models\Festival;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminPanelController
{
    public function show(): Response
    {
        $stats = [
            'events' => [
                'total' => Festival::count(),
                'latest' => Festival::latest()
                    ->take(5)
                    ->select('id', 'name', 'created_at')
                    ->get()
            ],
            'employees' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'latest' => User::latest()
                    ->take(5)
                    ->select('id', 'firstName', 'lastName', 'role', 'created_at')
                    ->get()
            ],
            'tickets' => [
                'placeholder' => true
            ]
        ];

        return Inertia::render('Admin/Home', [
            'stats' => $stats
        ]);
    }
}
