<?php

namespace App\Http\Controllers\Admin;

use App\Models\Festival;
use App\Models\HomepageContent;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

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
            $homepageContent = HomepageContent::create([
                'content' => $request->input('content')
            ]);
        }

        return redirect()->back()->with('success', 'Homepage content updated successfully');
    }

    public function uploadHero(Request $request)
    {
        $request->validate([
            'hero' => 'required|image|max:2048' // Accept any image format up to 2MB
        ]);

        $homepageContent = HomepageContent::first();
        if (!$homepageContent) {
            $homepageContent = HomepageContent::create([
                'content' => '<h1>What is Haarlem Festival?</h1><p>Join the Haarlem Festival and experience four unforgettable days celebrating everything that makes Haarlem unique!</p>'
            ]);
        }

        // Delete old image if it exists
        if ($homepageContent->hero_image_path) {
            Storage::disk('public')->delete($homepageContent->hero_image_path);
        }

        // Store new image
        $path = $request->file('hero')->store('festivals', 'public');
        
        // Update the hero_image_path
        $homepageContent->update([
            'hero_image_path' => $path
        ]);

        return response()->json(['success' => true, 'path' => $path]);
    }
}
