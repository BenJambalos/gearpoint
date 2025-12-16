<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserSelfDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_self()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $admin->id));
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_other_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'cashier']);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $other->id));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }
}
