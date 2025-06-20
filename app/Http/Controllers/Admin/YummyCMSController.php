<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class YummyCMSController
{
    public function update(Request $request)
    {
        $data = $request->only([
            'header_text_1',
            'header_text_2',
            'line_1',
            'line_2',
            'button_text',
            'header_image',
            'description',
        ]);

        // Handle image upload
        if ($request->hasFile('header_image_file')) {
            $request->validate([
                'header_image_file' => 'image|max:2048', // 2MB
            ]);
            $file = $request->file('header_image_file');
            $filename = $file->getClientOriginalName();
            $file->storeAs('public/main/yummy', $filename);
            $data['header_image'] = $filename;
        }

        // Save to yummy.json
        Storage::disk('public')->put(
            'main/yummy/yummy.json',
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return response()->json(['success' => true, 'data' => $data]);
    }
}
