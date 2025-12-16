<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ManagerCreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_only_see_cashier_role_on_create()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->get(route('users.create'));
        $response->assertStatus(200);
        $response->assertSee('option value="cashier"');
        $response->assertDontSee('option value="admin"');
        $response->assertDontSee('option value="manager"');
    }

    public function test_manager_cannot_create_non_cashier()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Bad Manager',
            'email' => 'badmanager@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'manager',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'badmanager@example.com']);
    }

    public function test_manager_can_create_cashier()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'New Cashier',
            'email' => 'newcashier@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'cashier',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'newcashier@example.com', 'role' => 'cashier']);
    }
}
