<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::inRandomOrder()->first()->id,
            'amount' => fake()->randomFloat(2, 20, 1000),
            'status' => fake()->randomElement(['success', 'failed', 'refunded']),
        ];
    }
}
