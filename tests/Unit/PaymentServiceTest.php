<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    public function test_payment_is_successful(): void
    {
        $ticket = Ticket::factory()->create([
            'price' => 100.00,
            'quantity' => 50,
        ]);

        $booking = Booking::factory()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $amount = $ticket->price * $booking->quantity;

        $result = $this->paymentService->processPayment($booking, $amount);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['payment']);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => $amount,
            'status' => 'success',
        ]);
    }

    public function test_payment_creates_correct_amount(): void
    {
        $ticket = Ticket::factory()->create([
            'price' => 50.00,
            'quantity' => 100,
        ]);

        $booking = Booking::factory()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 3,
            'status' => 'pending',
        ]);

        $amount = $ticket->price * $booking->quantity;

        $result = $this->paymentService->processPayment($booking, $amount);

        $this->assertEquals(150.00, $result['payment']->amount);
    }

    public function test_refund_processes_correctly(): void
    {
        $ticket = Ticket::factory()->create([
            'price' => 100.00,
            'quantity' => 50,
        ]);

        $booking = Booking::factory()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'confirmed',
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 200.00,
            'status' => 'success',
        ]);

        $result = $this->paymentService->processRefund($payment);

        $this->assertTrue($result);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);
    }

    public function test_refund_fails_for_non_successful_payment(): void
    {
        $ticket = Ticket::factory()->create([
            'price' => 100.00,
            'quantity' => 50,
        ]);

        $booking = Booking::factory()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 200.00,
            'status' => 'failed',
        ]);

        $result = $this->paymentService->processRefund($payment);

        $this->assertFalse($result);
    }
}
