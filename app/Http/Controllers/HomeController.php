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

        $heroUrl = 'storage/festivals/hero.png';

        $festivals = Festival::all();
        $homepageContent = HomepageContent::first();

        return Inertia::render('Home', [
            'festivals' => $festivals,
            'heroUrl' => $heroUrl,
            'homepageContent' => $homepageContent->content ? $homepageContent->content : null,
        ]);
    }
}
