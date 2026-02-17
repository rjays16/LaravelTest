<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $event = Event::inRandomOrder()->first() ?? Event::factory()->create();
        
        return [
            'type' => fake()->randomElement(['VIP', 'Standard', 'Premium', 'Economy']),
            'price' => fake()->randomFloat(2, 10, 500),
            'quantity' => fake()->numberBetween(10, 100),
            'event_id' => $event->id,
        ];
    }
}
