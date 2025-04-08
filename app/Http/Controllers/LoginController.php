<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController
{
    /*public function show(): Response
    {
        return Inertia::render('Login');
    }*/

    public function show(): Response|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            if($user->role == 'admin') {
                return redirect()->intended('/admin/dashboard');
            }
            else {
                return redirect()->intended('/');
            }
        }

        return Inertia::render('Login');
    }


    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if(Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))){

            $request->session()->regenerate();

            $user = Auth::user();
            Inertia::share('user', $user);

            if($user->role == 'admin') {
                return redirect()->intended('/admin/dashboard');
            }
            if ($user->role == 'user') {
                return redirect()->intended('/');
            }
            if ($user->role == 'employee') {
                return redirect()->intended('/qr-reader');
            }
        }
        else {
            error_log("Wrong email or password");
        }

        return back()->withErrors([
            'email' => 'Invalid email or password',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
