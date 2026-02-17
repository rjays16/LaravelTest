<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    public function store(Request $request, int $ticket_id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $ticket = Ticket::findOrFail($ticket_id);
        $availableQuantity = $ticket->getAvailableQuantity();

        if ($request->quantity > $availableQuantity) {
            return response()->json([
                'message' => 'Not enough tickets available',
                'available' => $availableQuantity,
            ], 400);
        }

        $existingBooking = Booking::where('user_id', $request->user()->id)
            ->where('ticket_id', $ticket_id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingBooking) {
            return response()->json([
                'message' => 'You already have a booking for this ticket',
            ], 400);
        }

        $booking = $request->user()->bookings()->create([
            'ticket_id' => $ticket_id,
            'quantity' => $request->quantity,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking->load('ticket.event'),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()->bookings()->with('ticket.event', 'payment')->get();

        return response()->json($bookings);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking already cancelled'], 400);
        }

        $booking->update(['status' => 'cancelled']);

        Cache::forget("booking_{$id}");

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ]);
    }
}
