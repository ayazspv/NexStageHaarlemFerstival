<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'date' => 'required|date',
            'session' => 'required|string',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'special_request' => 'nullable|string|max:1000',
        ]);

        $reservation = Reservation::create($data);

        return response()->json(['success' => true, 'reservation' => $reservation]);
    }
}
