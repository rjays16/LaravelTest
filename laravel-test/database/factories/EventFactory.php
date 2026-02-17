<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $organizer = User::where('role', 'organizer')->inRandomOrder()->first() 
            ?? User::factory()->create(['role' => 'organizer']);
        
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'date' => fake()->dateTimeBetween('now', '+6 months'),
            'location' => fake()->city(),
            'created_by' => $organizer->id,
        ];
    }
}
