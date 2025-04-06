<?php

namespace App\Http\Controllers\Admin;

use App\Models\Festival;
use App\Models\Game;
use App\Models\JazzFestival;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FestivalContentController
{
    public function show($festivalId)
    {
        $festival = Festival::findOrFail($festivalId);

        switch($festival->festivalType) {
            case 0:
                $bands = JazzFestival::where('festival_id', $festivalId)->get();
                return Inertia::render('Admin/CMS/ManageJazz', [
                    'festival' => $festival,
                    'bands'    => $bands,
                ]);

            case 1:
                return Inertia::render('Admin/CMS/ManageYummy', [
                    'festival' => $festival,
                ]);

            case 2:
                return Inertia::render('Admin/CMS/ManageHistory', [
                    'festival' => $festival,
                ]);

            case 3:
                $games = Game::where('festival_id', $festivalId)->get();

                return Inertia::render('Admin/CMS/ManageGame', [
                    'festival' => $festival,
                    'games' => $games,
                ]);

            default:
                abort(404);
        }
    }
}
