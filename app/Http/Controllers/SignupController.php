<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SignupController
{
    public function show(): Response|RedirectResponse
    {
        // if (Auth::check()) {
        //     $user = Auth::user();

        //     if($user->role == 'admin') {
        //         return redirect()->intended('/admin/dashboard');
        //     }
        //     else {
        //         return redirect()->intended('/');
        //     }
        // }

        return Inertia::render('Admin/Signup');
    }

    public function signup(Request $request): RedirectResponse
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phoneNumber' => 'nullable|string|max:20',
            'role' => 'required|in:admin,user'
        ]);

        // Create a new user
        $user = User::create([
            'firstName' => $validatedData['firstName'],
            'lastName' => $validatedData['lastName'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'phoneNumber' => $validatedData['phoneNumber'],
            'role' => $validatedData['role'], // Assign the role from the request
        ]);

        // Log the user in
        Auth::login($user);

        // Redirect to the intended page
        return redirect()->intended('/');
    }
}
