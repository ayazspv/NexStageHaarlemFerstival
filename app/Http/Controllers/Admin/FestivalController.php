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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isGame' => 'boolean',
            'ticket_amount' => 'nullable|integer|min:0',
            'time_slot' => 'nullable|string|max:255', 
        ]);

        $imagePath = $request->file('image')->store('festivals', 'public');

        Festival::create([
            'name' => $validated['name'],
            'image_path' => $imagePath,
            'isGame' => $request->has('isGame') ? $request->isGame : false,
            'ticket_amount' => $request->ticket_amount ?? 0,
            'time_slot' => $request->time_slot ?? null, 
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isGame' => 'boolean',
            'ticket_amount' => 'nullable|integer|min:0',
            'time_slot' => 'nullable|string|max:255', 
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($festival->image_path);
            $validated['image_path'] = $request->file('image')->store('festivals', 'public');
        }

        $festival->update([
            'name' => $validated['name'],
            'isGame' => $request->has('isGame') ? $request->isGame : false,
            'ticket_amount' => $request->ticket_amount ?? 0,
            'time_slot' => $request->time_slot ?? $festival->time_slot, 
        ]);

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
