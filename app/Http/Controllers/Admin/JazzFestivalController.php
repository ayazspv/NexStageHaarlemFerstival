<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JazzFestival;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class JazzFestivalController
{
    public function index($festivalId)
    {
        // Retrieve only the bands that belong to this festival
        $bands = JazzFestival::where('festival_id', $festivalId)->get();

        return Inertia::render('Admin/JazzFestival/Index', [
            'bands'      => $bands,
            'festivalId' => $festivalId,
        ]);
    }

    public function create($festivalId)
    {
        return Inertia::render('Admin/JazzFestival/Create', [
            'festivalId' => $festivalId,
        ]);
    }

    public function store(Request $request, $festivalId)
    {
        $data = $request->validate([
            'band_name'            => 'required|string|max:255',
            'performance_datetime' => 'required|date',
            'performance_day'      => 'required|integer|between:24,27',
            'start_time'           => 'required|string',
            'end_time'             => 'required|string',
            'ticket_price'         => 'required|numeric',
            'band_description'     => 'required|string',
            'band_details'         => 'required|string',
            'band_image'           => 'nullable|image|max:2048',
        ]);

        $data['festival_id'] = $festivalId;
        
        // Store start and end times
        $data['start_time'] = $request->start_time;
        $data['end_time'] = $request->end_time;

        if ($request->hasFile('band_image')) {
            $data['band_image'] = $request->file('band_image')->store('festivals/jazz', 'public');
        }

        JazzFestival::create($data);

        return redirect()->back()->with('success', 'Artist has been added successfully.');
    }

    public function edit($festivalId, $id)
    {
        $band = JazzFestival::where('festival_id', $festivalId)->findOrFail($id);
        return Inertia::render('Admin/JazzFestival/Edit', [
            'band'       => $band,
            'festivalId' => $festivalId,
        ]);
    }

    public function update(Request $request, $festivalId, $id)
    {
        $band = JazzFestival::where('festival_id', $festivalId)->findOrFail($id);
        $data = $request->validate([
            'band_name'            => 'required|string|max:255',
            'performance_datetime' => 'required|date',
            'performance_day'      => 'required|integer|between:24,27',
            'start_time'           => 'required|string',
            'end_time'             => 'required|string',
            'ticket_price'         => 'required|numeric',
            'band_description'     => 'required|string',
            'band_details'         => 'required|string',
            'band_image'           => 'nullable|image|max:2048',
        ]);

        // Store start and end times
        $data['start_time'] = $request->start_time;
        $data['end_time'] = $request->end_time;

        if ($request->hasFile('band_image')) {
            if ($band->band_image) {
                Storage::disk('public')->delete($band->band_image);
            }
            $data['band_image'] = $request->file('band_image')->store('festivals/jazz', 'public');
        }

        $band->update($data);

        // Refresh the page by redirecting back
        return redirect()->back()->with('success', 'Jazz Festival has been updated.');
    }

    public function destroy($festivalId, $id)
    {
        $band = JazzFestival::where('festival_id', $festivalId)->findOrFail($id);

        if ($band->band_image) {
            Storage::disk('public')->delete($band->band_image);
        }

        $band->delete();

        // Return a proper response instead of redirecting
        return response()->json(['success' => true], 200);
    }

    public function getEventPrice($id)
    {
        try {
            $event = JazzFestival::findOrFail($id);
            return response()->json([
                'price' => (float)$event->ticket_price
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting jazz event price: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'price' => 25.00 // Default price on error
            ], 500);
        }
    }
}
