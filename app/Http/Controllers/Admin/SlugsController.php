<?php

namespace App\Http\Controllers\Admin;

use App\Models\CMS;
use App\Models\Festival;
use App\Models\Game;
use App\Models\JazzFestival;
use App\Models\Style;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SlugsController
{
    protected function findPageRecursively($pages, array $segments)
    {
        if (empty($segments)) return null;

        $segment = array_shift($segments);
        $page = $pages->first(fn ($p) => Str::slug($p->title) === $segment);

        if (!$page) return null;

        if (!empty($segments)) {
            $page->load('children');
            return $this->findPageRecursively($page->children, $segments);
        }

        return $page;
    }

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

        $topPages = CMS::where('festival_id', $festival->id)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        if (!$path) {
            return Inertia::render('Components/FestivalPage', [
                'festival' => $festival,
                'cmsPages' => $topPages
            ]);
        }

        $segments = explode('/', trim($path, '/'));
        $currentPage = $this->findPageRecursively($topPages, $segments);

        if (!$currentPage) {
            abort(404);
        }

        $style = Style::where('cms_id', $currentPage->id)->first();

        return Inertia::render('Components/FestivalPage', [
            'festival' => $festival,
            'cmsPages' => $currentPage->children,
            'currentPageId' => $currentPage->id,
            'style' => $style,
        ]);
    }

}
