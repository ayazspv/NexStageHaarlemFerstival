<?php

namespace App\Http\Controllers\Admin;

use App\Models\Festival;
use App\Models\CMS; // Ensure your CMS model has $table = 'cms';
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Nette\Schema\ValidationException;

class CMSController
{
    public function cmsManage($festivalId): Response
    {
        $festival = Festival::findOrFail($festivalId);

        $cmsPages = $festival->cmsPages()->get();

        return Inertia::render('Admin/ManageEvent', [
            'festival' => $festival,
            'cmsPages' => $cmsPages,
        ]);
    }


    // Update main CMS content
    public function cmsUpdate(Request $request, $festivalId)
    {
        try {
            $validated = $request->validate([
                'pages' => 'required|array',
                'pages.*.id' => 'nullable',
                'pages.*.title' => 'required|string',
                'pages.*.content' => 'required|string',
                'pages.*.parent_id' => 'nullable|exists:cms,id',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        foreach ($validated['pages'] as $pageData) {
            if (!empty($pageData['id'])) {
                // Update existing record
                $cms = CMS::find($pageData['id']);
                if ($cms) {
                    $cms->update([
                        'festival_id' => $festivalId,
                        'title'       => $pageData['title'],
                        'content'     => $pageData['content'],
                        'parent_id'   => $pageData['parent_id'] ?? null,
                    ]);
                }
            } else {
                // Create a new record
                CMS::create([
                    'festival_id' => $festivalId,
                    'title'       => $pageData['title'],
                    'content'     => $pageData['content'],
                    'parent_id'   => $pageData['parent_id'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'CMS content updated successfully!');
    }



    // Remove CMS content
    public function cmsRemoveContent(Request $request, $festivalId)
    {
        $validated = $request->validate([
            'cms_id' => 'required|exists:cms,id',
        ]);

        $cms = CMS::find($validated['cms_id']);
        if ($cms) {
            $cms->delete();
            return redirect()->back()->with('success', 'CMS content removed successfully!');
        }

        return redirect()->back()->withErrors(['error' => 'CMS content not found.']);
    }

    // New: Create a subpage and redirect to the EditSubpage view.
    public function createSubpage($festivalId, $parentId)
    {
        $festival = Festival::findOrFail($festivalId);
        $parent = CMS::findOrFail($parentId);

        $subpage = CMS::create([
            'festival_id' => $festival->id,
            'parent_id'   => $parent->id,
            'title'       => 'New Subpage',
            'content'     => '',
        ]);

        // Return JSON instead of a redirect
        return response()->json([
            'success'    => true,
            'subpageId'  => $subpage->id,
        ]);
    }


    // New: Render the EditSubpage view.
    public function editSubpage($festivalId, $cmsId): Response
    {
        $festival = Festival::findOrFail($festivalId);
        $parentPage = CMS::findOrFail($cmsId);

        if ($parentPage->festival_id !== $festival->id) {
            abort(404);
        }

        return Inertia::render('Admin/EditSubpage', [
            'festival'   => $festival,
            'parentPage' => $parentPage,
        ]);
    }

    // Update the subpage content (save changes from the EditSubpage view).
    public function cmsUpdateSubpage(Request $request, $festivalId, $cmsId): RedirectResponse
    {
        $festival = Festival::findOrFail($festivalId);
        $subpage = CMS::findOrFail($cmsId);

        if ($subpage->festival_id !== $festival->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'   => 'required|string',
            'content' => 'required|string',
        ]);

        $subpage->update([
            'title'   => $validated['title'],
            'content' => $validated['content'],
        ]);

        return redirect()->back()->with('success', 'Subpage updated successfully!');
    }
}
