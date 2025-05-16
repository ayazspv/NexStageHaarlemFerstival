<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

    public function getFestivals(Request $request): JsonResponse
    {
        $festivals = Festival::all();
        return response()->json($festivals);
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

    /**
     * Update only the basic festival details (name, description, image).
     */
    public function updateDetails(Request $request, $id): RedirectResponse
    {
        $festival = Festival::findOrFail($id);
        \Log::info('Festival details update request:', [
            'festival_id' => $id,
            'has_name' => $request->has('name'),
            'has_description' => $request->has('description'),
            'has_image' => $request->hasFile('image')
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Create updateData with validated fields
        $updateData = [];

        // Only include fields that are explicitly provided
        if ($request->has('name')) {
            $updateData['name'] = $validated['name'];
        }

        if ($request->has('description')) {
            $updateData['description'] = $request->description;
        }

        // Handle image upload if present
        if ($request->hasFile('image')) {
            if ($festival->image_path) {
                Storage::disk('public')->delete($festival->image_path);
            }
            $updateData['image_path'] = $request->file('image')->store('festivals', 'public');
        }

        // Only update if we have something to update
        if (!empty($updateData)) {
            $festival->update($updateData);
        }

        return redirect()->back()->with('success', 'Festival details updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $festival = Festival::findOrFail($id);

        Storage::disk('public')->delete($festival->image_path);
        $festival->delete();

        return redirect()->back();
    }


}
