<?php

namespace App\Http\Controllers\Admin;

use App\Models\Game;
use Illuminate\Http\Request;

class GameCMSController
{
    public function storeGame(Request $request, $festivalId)
    {

        $data = $request->validate([
            'title'          => 'required|string',
            'question'       => 'required|string',
            'option1'        => 'required|string',
            'option2'        => 'required|string',
            'option3'        => 'required|string',
            'option4'        => 'required|string',
            'correct_option' => 'required|integer|in:1,2,3,4',
            'hint'           => 'required|string',
            'thumbnail'      => 'nullable|image|max:2048',
            'stamp'          => 'nullable|image|max:2048',
        ]);

        error_log('passing');

        $data['festival_id'] = $festivalId;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('game/thumbnails', 'public');
        }

        if ($request->hasFile('stamp')) {
            $data['stamp'] = $request->file('stamp')->store('game/stamps', 'public');
        }

        Game::create($data);

        return redirect()->back()->with('success', 'Game added successfully!');
    }

    public function updateGame(Request $request, $gameId)
    {
        $game = Game::findOrFail($gameId);

        $data = $request->validate([
            'title'          => 'required|string',
            'question'       => 'required|string',
            'option1'        => 'required|string',
            'option2'        => 'required|string',
            'option3'        => 'required|string',
            'option4'        => 'required|string',
            'correct_option' => 'required|integer|in:1,2,3,4',
            'hint'           => 'required|string',
            'thumbnail'      => 'sometimes|nullable|image|max:2048',
            'stamp'          => 'sometimes|nullable|image|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('game/thumbnails', 'public');
        }

        if ($request->hasFile('stamp')) {
            $data['stamp'] = $request->file('stamp')->store('game/stamps', 'public');
        }

        $game->update($data);

        return redirect()->back()->with('success', 'Game updated successfully!');
    }

    public function deleteGame($gameId) {

        Game::where('id', $gameId)->delete();

        return redirect()->back()->with('success', 'Game deleted successfully!');
    }
}
