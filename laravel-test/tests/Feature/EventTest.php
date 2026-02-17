<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_create_event(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'date' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'location' => 'Test Location',
        ]);

        $response->assertStatus(403);
    }

    public function test_organizer_can_create_event(): void
    {
        $user = User::factory()->create(['role' => 'organizer']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'date' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'location' => 'Test Location',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'event',
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'created_by' => $user->id,
        ]);
    }

    public function test_can_list_events(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        Event::factory()->count(5)->create(['created_by' => $organizer->id]);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'total',
            ]);
    }

    public function test_can_search_events(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        Event::factory()->create([
            'title' => 'Summer Concert',
            'created_by' => $organizer->id,
        ]);
        Event::factory()->create([
            'title' => 'Winter Festival',
            'created_by' => $organizer->id,
        ]);

        $response = $this->getJson('/api/events?search=Summer');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_view_event_details(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::factory()->create(['created_by' => $organizer->id]);

        $response = $this->getJson('/api/events/' . $event->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $event->id,
                'title' => $event->title,
            ]);
    }
}
