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
        /*
        if (Storage::disk('public')->exists('festivals/hero.png')) {
            $heroUrl = Storage::url('festivals/hero.png');
        } elseif (Storage::disk('public')->exists('festivals/hero.jpg')) {
            $heroUrl = Storage::url('festivals/hero.jpg');
        } else {
            $heroUrl = null;
        }*/

        $festivals = Festival::all();
        $homepageContent = HomepageContent::first();
        
        // Default hero image if none is set
        $heroUrl = 'storage/festivals/hero.png';
        
        // Use the hero_image_path if it exists
        if ($homepageContent && $homepageContent->hero_image_path) {
            $heroUrl = 'storage/' . $homepageContent->hero_image_path;
        }

        return Inertia::render('Home', [
            'festivals' => $festivals,
            'heroUrl' => $heroUrl,
            'homepageContent' => $homepageContent->content ?? null,
        ]);
    }
}
