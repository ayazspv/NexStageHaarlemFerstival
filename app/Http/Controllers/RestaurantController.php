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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'cta_text' => 'required|string',
            'subheader_1' => 'nullable|string',
            'subheader_2' => 'nullable|string',
            'description' => 'nullable|string',
            'seats' => 'required|integer',
            'accessibility' => 'boolean',
            'vegan' => 'boolean',
            'gluten_free' => 'boolean',
            'halal' => 'boolean',
            'adult_price' => 'required|numeric',
            'children_price' => 'required|numeric',
            'location' => 'required|string',
            'contact_number' => 'required|string',
            'picture_1' => 'required|string',
            'picture_2' => 'nullable|string',
            'picture_3' => 'nullable|string',
            'picture_4' => 'nullable|string',
            'session_1_time' => 'nullable|string',
            'session_2_time' => 'nullable|string',
            'session_3_time' => 'nullable|string',
            'food_types' => 'nullable|string', // comma-separated
        ]);
        $restaurant = Restaurant::create($data);

        $this->syncFoodTypes($restaurant, $request->input('food_types'));

        return response()->json($restaurant->load('foodTypes'), 201);
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'cta_text' => 'required|string',
            'subheader_1' => 'nullable|string',
            'subheader_2' => 'nullable|string',
            'description' => 'nullable|string',
            'seats' => 'required|integer',
            'accessibility' => 'boolean',
            'vegan' => 'boolean',
            'gluten_free' => 'boolean',
            'halal' => 'boolean',
            'adult_price' => 'required|numeric',
            'children_price' => 'required|numeric',
            'location' => 'required|string',
            'contact_number' => 'required|string',
            'picture_1' => 'required|string',
            'picture_2' => 'nullable|string',
            'picture_3' => 'nullable|string',
            'picture_4' => 'nullable|string',
            'session_1_time' => 'nullable|string',
            'session_2_time' => 'nullable|string',
            'session_3_time' => 'nullable|string',
            'food_types' => 'nullable|string',
        ]);
        $restaurant->update($data);

        $this->syncFoodTypes($restaurant, $request->input('food_types'));

        return response()->json($restaurant->load('foodTypes'));
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Sync food types for a restaurant, create new if needed, and clean up unused.
     */
    protected function syncFoodTypes(Restaurant $restaurant, $foodTypesString)
    {
        $foodTypes = collect(explode(',', $foodTypesString))
            ->map(fn($type) => trim($type))
            ->filter()
            ->unique();

        $typeIds = [];
        foreach ($foodTypes as $typeName) {
            $type = FoodType::firstOrCreate(['name' => $typeName]);
            $typeIds[] = $type->id;
        }
        $restaurant->foodTypes()->sync($typeIds);

        // Clean up unused food types
        FoodType::doesntHave('restaurants')->delete();
    }
}
