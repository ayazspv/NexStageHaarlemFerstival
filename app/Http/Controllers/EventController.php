<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Display a listing of all events
    public function index()
    {
        $events = Event::all();
        return response()->json($events);
    }

    // Retrieve all Jazz events (festival_id = 1)
    public function getJazzEvent()
    {
        $events = Event::where('festival_id', 1)->get();
        return response()->json($events);
    }

    // Retrieve all Food events (festival_id = 2)
    public function getFoodEvent()
    {
        $events = Event::where('festival_id', 2)->get();
        return response()->json($events);
    }

    // Retrieve all Dance events (festival_id = 3)
    public function getDanceEvent()
    {
        $events = Event::where('festival_id', 3)->get();
        return response()->json($events);
    }

    // Retrieve all History events (festival_id = 4)
    public function getHistoryEvent()
    {
        $events = Event::where('festival_id', 4)->get();
        return response()->json($events);
    }

    // Show the form for creating a new event
    public function create()
    {
        return response()->json(['message' => 'Create event form']);
    }

    // Store a newly created event in the database
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'details' => 'nullable|string',
            'time' => 'required|datetime',
            'ticket_price' => 'required|numeric',
            'image' => 'nullable|string|max:255',
            'second_image' => 'nullable|string|max:255',
            'day' => 'required|integer',
            'festival_id' => 'required|integer|exists:festivals,id',
        ]);

        $event = Event::create($data);

        return response()->json(['message' => 'Event created successfully', 'event' => $event]);
    }

    // Display the specified event
    public function show($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json($event);
    }

    // Show the form for editing the specified event
    public function edit($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json(['message' => 'Edit event form', 'event' => $event]);
    }

    // Update the specified event in the database
    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'details' => 'nullable|string',
            'time' => 'required|datetime',
            'ticket_price' => 'required|numeric',
            'image' => 'nullable|string|max:255',
            'second_image' => 'nullable|string|max:255',
            'day' => 'required|integer',
            'festival_id' => 'required|integer|exists:festivals,id',
        ]);

        $event->update($data);

        return response()->json(['message' => 'Event updated successfully', 'event' => $event]);
    }

    // Remove the specified event from the database
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}