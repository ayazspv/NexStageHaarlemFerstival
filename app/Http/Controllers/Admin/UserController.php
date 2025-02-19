<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class UserController
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users', [
            'users' => User::all()
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'phoneNumber' => 'nullable|string|max:20',
            'role' => 'required|in:admin,user'
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back();
    }

    public function destroy($id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $user->delete();
        return redirect()->back();
    }
}