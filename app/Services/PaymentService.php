<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function processPayment(Booking $booking, float $amount): array
    {
        try {
            $success = $this->simulatePayment();

            $status = $success ? 'success' : 'failed';

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $amount,
                'status' => $status,
            ]);

            Log::info('Payment processed', [
                'booking_id' => $booking->id,
                'amount' => $amount,
                'status' => $status,
            ]);

            return [
                'success' => $success,
                'payment' => $payment,
            ];
        } catch (\Exception $e) {
            Log::error('Payment failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'payment' => null,
            ];
        }
    }

    private function simulatePayment(): bool
    {
        return true;
    }

    public function processRefund(Payment $payment): bool
    {
        if ($payment->status !== 'success') {
            return false;
        }

        $payment->update(['status' => 'refunded']);

        Log::info('Payment refunded', [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);

        return true;
    }
}
