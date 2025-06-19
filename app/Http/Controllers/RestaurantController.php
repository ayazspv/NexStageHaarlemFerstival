<?php

namespace App\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class RestaurantController
{
    public function index()
    {
        return Restaurant::with('foodTypes')->get();
    }

    public function show(): Response|RedirectResponse
    {
        return Inertia::render('Festivals/Restaurants/Restaurant');
    }
}
