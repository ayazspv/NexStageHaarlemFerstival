<?php

namespace App\Http\Controllers\Admin;

use App\Models\Festival;
use App\Models\JazzFestival;
use App\Models\Game;
use Inertia\Inertia;

class SlugsController
{
    public function show($festivalSlug, $path = null)
    {
        $festival = Festival::whereRaw("LOWER(REPLACE(name, ' ', '-')) = ?", [$festivalSlug])->first();

        if (!$festival) {
            abort(404);
        }

        if($festival->isGame) {
            $games = Game::where('festival_id', $festival->id)->get();

            return Inertia::render('Festivals/NightAtTeylers', [
                'festival' => $festival,
                'games' => $games,
            ]);
        }

        if($festival->festivalType == 0) {
            $bands = JazzFestival::where('festival_id', $festival->id)->get();

            return Inertia::render('Festivals/Jazz', [
                'festival' => $festival,
                'bands' => $bands,
            ]);
        }

        if($festival->festivalType == 1) {
            return Inertia::render('Festivals/Yummy', [
                'festival' => $festival,
            ]);
        }

        abort(404);
    }
}
