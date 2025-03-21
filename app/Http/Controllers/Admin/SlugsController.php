<?php

namespace App\Http\Controllers\Admin;

use App\Models\CMS;
use App\Models\Festival;
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
        $festival = Festival::all()->first(function ($fest) use ($festivalSlug) {
            return Str::slug($fest->name) === $festivalSlug;
        });

        if (!$festival) abort(404);

        // Get top-level pages with children
        $topPages = CMS::where('festival_id', $festival->id)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        // Handle root festival URL
        if (!$path) {
            return Inertia::render('Components/FestivalPage', [
                'festival' => $festival,
                'cmsPages' => $topPages
            ]);
        }

        // Handle nested paths
        $segments = explode('/', trim($path, '/'));
        $currentPage = $this->findPageRecursively($topPages, $segments);

        if (!$currentPage) abort(404);

        return Inertia::render('Components/FestivalPage', [
            'festival' => $festival,
            'cmsPages' => $currentPage->children
        ]);
    }
}
