<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function store(Request $request, int $booking_id): JsonResponse
    {
        $booking = Booking::findOrFail($booking_id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Cannot pay for cancelled booking'], 400);
        }

        if ($booking->payment) {
            return response()->json(['message' => 'Payment already made'], 400);
        }

        $amount = $booking->ticket->price * $booking->quantity;

        $result = $this->paymentService->processPayment($booking, $amount);

        if ($result['success']) {
            $booking->update(['status' => 'confirmed']);
            $booking->user->notify(new BookingConfirmedNotification($booking));
        }

        return response()->json([
            'message' => $result['success'] ? 'Payment successful' : 'Payment failed',
            'payment' => $result['payment'],
        ], $result['success'] ? 201 : 400);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);

        if ($payment->booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($payment->load('booking'));
    }
}
