<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admins = User::factory()->count(2)->create([
            'role' => 'admin',
        ]);

        $organizers = User::factory()->count(3)->create([
            'role' => 'organizer',
        ]);

        $customers = User::factory()->count(10)->create([
            'role' => 'customer',
        ]);

        $events = Event::factory()->count(5)->create([
            'created_by' => $organizers->random()->id,
        ]);

        $tickets = [];
        foreach ($events as $event) {
            $tickets[] = Ticket::factory()->count(3)->create([
                'event_id' => $event->id,
            ]);
        }
        $tickets = collect($tickets)->flatten();

        $bookings = [];
        foreach (range(1, 20) as $i) {
            $ticket = $tickets->random();
            $customer = $customers->random();
            
            $booking = Booking::factory()->create([
                'user_id' => $customer->id,
                'ticket_id' => $ticket->id,
            ]);

            if ($booking->status === 'confirmed') {
                Payment::factory()->create([
                    'booking_id' => $booking->id,
                    'amount' => $ticket->price * $booking->quantity,
                    'status' => 'success',
                ]);
            }

            $bookings[] = $booking;
        }
    }
}
