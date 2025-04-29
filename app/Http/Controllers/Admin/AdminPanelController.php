<?php

namespace App\Http\Controllers\Admin;

use App\Models\Festival;
use App\Models\HomepageContent;
use App\Models\User;
use Illuminate\Http\Request;
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

        $homepageContent = HomepageContent::first();

        error_log($homepageContent);

        return Inertia::render('Admin/Home', [
            'stats' => $stats,
            'homepageContent' => $homepageContent->content ? $homepageContent->content : null,
        ]);
    }

    public function storeHomepageContent(Request $request)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        // Find existing record or create new one
        $homepageContent = HomepageContent::first();

        if ($homepageContent) {
            // Update existing content
            $homepageContent->update([
                'content' => $request->input('content')
            ]);
        } else {
            // Create new content
            HomepageContent::create([
                'content' => $request->input('content')
            ]);
        }

        return redirect()->back()->with('success', 'Homepage content updated successfully');
    }
}
