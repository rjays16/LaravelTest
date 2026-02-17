<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_book_ticket(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id, 'quantity' => 100]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/bookings', [
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'booking',
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_book_more_tickets_than_available(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id, 'quantity' => 5]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/bookings', [
            'quantity' => 10,
        ]);

        $response->assertStatus(400);
    }

    public function test_customer_cannot_double_book_same_ticket(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id, 'quantity' => 100]);

        $token = $user->createToken('auth-token')->plainTextToken;

        Booking::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/bookings', [
            'quantity' => 2,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You already have a booking for this ticket',
            ]);
    }

    public function test_customer_can_view_their_bookings(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);
        
        Booking::factory()->count(3)->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_customer_can_cancel_booking(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create(['event_id' => $event->id]);
        
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'status' => 'pending',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/bookings/' . $booking->id . '/cancel');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Booking cancelled successfully',
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }
}
