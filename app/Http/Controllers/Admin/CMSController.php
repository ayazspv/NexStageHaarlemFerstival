<?php

namespace App\Http\Controllers\Admin;

use App\Models\Festival;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CMSController
{
    public function cmsManage($festivalId): Response
    {
        $festival = Festival::findOrFail($festivalId);

        $cmsContents = $festival->content ? json_decode($festival->content, true) : [];

        return Inertia::render('Admin/ManageEvent', [
            'festival'   => $festival,
            'cmsContents'=> $cmsContents,
        ]);
    }

    public function cmsUpdate(Request $request, $id): RedirectResponse
    {
        $festival = Festival::findOrFail($id);

        $validated = $request->validate([
            'contents'   => 'nullable|array',
            'contents.*' => 'nullable|string',
            'images'     => 'nullable|array',
            'images.*'   => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('images')) {
            $image = $request->file('images')[0];
            $path = $image->store('festivals', 'public');
            $validated['image_path'] = $path;
        }

        $contents = $request->input('contents', []);

        // Encode the CMS panel HTML as JSON.
        $encodedContent = json_encode($contents);

        $updateData = [
            'content'    => $encodedContent,
            'image_path' => $validated['image_path'] ?? $festival->image_path,
        ];

        $festival->update($updateData);

        return redirect()->back()->with('success', 'Content updated successfully!');
    }


    public function cmsRemoveContent(Request $request, $festivalId)
    {
        error_log("Request data: " . print_r($request->all(), true));

        $validated = $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $index = $validated['index'];
        error_log("Index to remove: " . $index);

        // Fetch the festival manually using the festival ID
        $festival = Festival::find($festivalId);

        if (!$festival) {
            error_log("Festival not found for ID: " . $festivalId);
            return response()->json(['error' => 'Festival not found.'], 404);
        }

        // Decode the contents array
        $contents = json_decode($festival->content, true) ?: [];
        error_log("Current contents: " . print_r($contents, true));

        if (isset($contents[$index])) {
            // Remove the panel at the given index.
            array_splice($contents, $index, 1);
            error_log("Updated contents: " . print_r($contents, true));

            // Save the updated contents array
            $festival->content = json_encode($contents);
            $festival->save();

            return redirect()->back()->with('success', 'Content removed successfully!');
        }

        return response()->back()->withErrors(['index' => 'Content not found.']);
    }
}
