<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Models\HomepageContent;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HomeController
{
    public function index()
    {
        $festivals = Festival::all();
        $homepageContent = HomepageContent::first();
        
        // Handle hero image path correctly
        $heroUrl = null;
        if ($homepageContent && $homepageContent->hero_image_path) {
            // Make sure we don't double-prefix with 'storage/'
            if (strpos($homepageContent->hero_image_path, 'storage/') === 0) {
                $heroUrl = '/' . $homepageContent->hero_image_path;
            } else {
                $heroUrl = '/storage/' . $homepageContent->hero_image_path;
            }
        } else {
            // Fallback to default if exists
            if (Storage::disk('public')->exists('festivals/hero.png')) {
                $heroUrl = '/storage/festivals/hero.png';
            }
        }

        return Inertia::render('Home', [
            'festivals' => $festivals,
            'heroUrl' => $heroUrl,
            'homepageContent' => $homepageContent->content ?? null,
        ]);
    }
}
