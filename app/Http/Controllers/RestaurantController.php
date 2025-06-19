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
use App\Models\FoodType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RestaurantController
{
    public function index()
    {
        return Restaurant::with('foodTypes')->get();
    }

    public function show($id): Response|RedirectResponse
    {
        $restaurant = Restaurant::with('foodTypes')->findOrFail($id);
        return Inertia::render('Festivals/Restaurants/Restaurant', [
            'restaurant' => $restaurant,
        ]);
    }

    public function getYummyHomepageContent(){
        if (!Storage::disk('public')->exists('main/yummy/yummy.json')) {
            abort(404, 'File not found');
        }

        $json = Storage::disk('public')->get('main/yummy/yummy.json');
        $data = json_decode($json, true);
        

        return response()->json($data);
    }

    public function getFoodTypes() {
        return response()->json(FoodType::all());
    }

    public function getAllRestaurants() {
        return response()->json(Restaurant::with('foodTypes')->get());
    }

    public function getRestaurantById($id) {
        $restaurant = Restaurant::with('foodTypes')->findOrFail($id);
        return response()->json($restaurant);
    }
}
