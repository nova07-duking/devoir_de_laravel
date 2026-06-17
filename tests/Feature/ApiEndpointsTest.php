<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_endpoints_are_accessible(): void
    {
        $response = $this->getJson('/api/posts');
        $response->assertOk()
            ->assertJson(['message' => 'Hello Swagger']);

        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        $response = $this->postJson('/api/login', [
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_user_can_manage_own_tickets_and_replies(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/tickets');
        $response->assertOk()
            ->assertJson([]);

        $response = $this->postJson('/api/tickets', [
            'title' => 'Bug login',
            'message' => 'Cannot login after password reset',
            'urgency' => 'high',
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Bug login')
            ->assertJsonPath('status', 'open')
            ->assertJsonPath('user_id', $user->id);

        $ticket = Ticket::where('user_id', $user->id)->firstOrFail();

        $response = $this->getJson('/api/tickets/' . $ticket->id);
        $response->assertOk()
            ->assertJsonPath('id', $ticket->id);

        $response = $this->postJson('/api/tickets/' . $ticket->id . '/replies', [
            'message' => 'I am checking this now',
        ]);
        $response->assertCreated()
            ->assertJsonPath('ticket_id', $ticket->id)
            ->assertJsonPath('message', 'I am checking this now');

        $response = $this->getJson('/api/tickets/' . $ticket->id . '/replies');
        $response->assertOk()
            ->assertJsonCount(1);

        $response = $this->postJson('/api/logout');
        $response->assertOk()
            ->assertJson(['message' => 'Déconnecté avec succès']);
    }

    public function test_agent_can_list_and_update_tickets(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent',
        ]);
        $ticket = Ticket::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Existing ticket',
            'message' => 'Existing ticket message',
            'urgency' => 'medium',
            'status' => 'open',
        ]);

        Sanctum::actingAs($agent, ['*']);

        $response = $this->getJson('/api/agent/tickets');
        $response->assertOk()
            ->assertJsonFragment(['id' => $ticket->id]);

        $response = $this->getJson('/api/agent/tickets/' . $ticket->id);
        $response->assertOk()
            ->assertJsonPath('id', $ticket->id);

        $response = $this->putJson('/api/agent/tickets/' . $ticket->id . '/status', [
            'status' => 'in_progress',
        ]);
        $response->assertOk()
            ->assertJsonPath('status', 'in_progress');

        $response = $this->getJson('/api/agent/tickets/' . $ticket->id . '/replies');
        $response->assertOk();

        $response = $this->postJson('/api/agent/tickets/' . $ticket->id . '/replies', [
            'message' => 'Agent reply',
        ]);
        $response->assertCreated()
            ->assertJsonPath('message', 'Agent reply');
    }

    public function test_user_cannot_access_other_users_tickets_and_agent_routes(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $ticket = Ticket::create([
            'user_id' => $owner->id,
            'title' => 'Private ticket',
            'message' => 'This should stay private',
            'urgency' => 'medium',
            'status' => 'open',
        ]);

        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->getJson('/api/tickets/' . $ticket->id);
        $response->assertStatus(403);

        $response = $this->getJson('/api/tickets/' . $ticket->id . '/replies');
        $response->assertStatus(403);

        $response = $this->postJson('/api/tickets/' . $ticket->id . '/replies', [
            'message' => 'should fail',
        ]);
        $response->assertStatus(403);

        $response = $this->getJson('/api/agent/tickets');
        $response->assertStatus(403);
    }
}
