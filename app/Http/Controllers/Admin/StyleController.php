<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Style;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StyleController
{

    public function index()
    {
        return response()->json([
            'data' => Style::all() // Ensure this matches frontend expectation
        ]);
    }

    /**
     * Store a newly created style in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cms_id'  => 'required|exists:cms,id',
            'name'    => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $style = Style::updateOrCreate(
            ['cms_id' => $validated['cms_id']],
            ['name' => $validated['name'], 'content' => $validated['content']]
        );

        return redirect()->back()->with('success', 'Style saved successfully!');
    }

    /**
     * Retrieve a specific style (optional, for AJAX loading).
     */
    public function show($id)
    {
        $style = Style::findOrFail($id);
        return response()->json($style);
    }
}
