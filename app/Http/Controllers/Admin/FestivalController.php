<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class FestivalController
{
    public function index(): Response
    {
        return Inertia::render('Admin/Events', [
            'festivals' => Festival::all()
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        error_log($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isGame' => 'boolean',
            'festivalType' => 'required|integer|max:4|min:0'
        ]);

        $imagePath = $request->file('image')->store('festivals', 'public');

        Festival::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'isGame' => $request->has('isGame') ? $request->isGame : false,
            'festivalType' => $validated['festivalType']
        ]);

        return redirect()->back();
    }

    public function edit(Festival $festival): Response
    {
        return Inertia::render('Admin/Festivals/Edit', [
            'festival' => $festival
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $festival = Festival::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isGame' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($festival->image_path);
            $validated['image_path'] = $request->file('image')->store('festivals', 'public');
        }

        $validated['isGame'] = $request->has('isGame') ? $request->isGame : false;

        $festival->update($validated);

        return redirect()->back();
    }

    public function destroy($id): RedirectResponse
    {
        $festival = Festival::findOrFail($id);

        Storage::disk('public')->delete($festival->image_path);
        $festival->delete();

        return redirect()->back();
    }
}
