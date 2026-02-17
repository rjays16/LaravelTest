<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::with('tickets');

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $events = $query->paginate(10);

        return response()->json($events);
    }

    public function show(int $id): JsonResponse
    {
        $event = Cache::remember("event_{$id}", 60, function () use ($id) {
            return Event::with('tickets')->findOrFail($id);
        });

        return response()->json($event);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
        ]);

        $event = $request->user()->events()->create($request->all());

        Cache::forget('events_list');

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date' => 'sometimes|date',
            'location' => 'sometimes|string|max:255',
        ]);

        $event->update($request->all());

        Cache::forget("event_{$id}");
        Cache::forget('events_list');

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();

        Cache::forget("event_{$id}");
        Cache::forget('events_list');

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }
}
