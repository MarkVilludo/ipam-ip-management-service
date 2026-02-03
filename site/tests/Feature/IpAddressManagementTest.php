<?php

namespace Tests\Feature;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class IpAddressManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsUser(User $user): self
    {
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token);

        return $this;
    }

    public function test_list_ip_addresses_requires_authentication(): void
    {
        $response = $this->getJson('/api/ip-addresses');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_ip_addresses(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAsUser($user)->getJson('/api/ip-addresses');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_authenticated_user_can_create_ip_address(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAsUser($user)
            ->postJson('/api/ip-addresses', [
                'ip_address' => '192.168.1.1',
                'label' => 'Test Router',
                'comment' => 'Optional comment',
            ]);
        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.ip_address', '192.168.1.1');
        $response->assertJsonPath('data.label', 'Test Router');
        $response->assertJsonPath('data.comment', 'Optional comment');

        $this->assertDatabaseHas('ip_addresses', [
            'ip_address' => '192.168.1.1',
            'label' => 'Test Router',
            'created_by' => $user->id,
        ]);
    }

    public function test_create_rejects_invalid_ip(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAsUser($user)
            ->postJson('/api/ip-addresses', [
                'ip_address' => 'not-an-ip',
                'label' => 'Bad',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ip_address']);
    }

    public function test_regular_user_can_update_own_ip_address(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $ip = IpAddress::create([
            'ip_address' => '10.0.0.1',
            'label' => 'Original',
            'comment' => null,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAsUser($user)
            ->putJson("/api/ip-addresses/{$ip->id}", [
                'label' => 'Updated Label',
                'comment' => 'Updated comment',
            ]);
        $response->assertStatus(200);
        $response->assertJsonPath('data.label', 'Updated Label');

        $ip->refresh();
        $this->assertSame('Updated Label', $ip->label);
    }

    public function test_regular_user_cannot_update_other_users_ip_address(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $ip = IpAddress::create([
            'ip_address' => '10.0.0.2',
            'label' => 'Owner IP',
            'comment' => null,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAsUser($other)
            ->putJson("/api/ip-addresses/{$ip->id}", [
                'label' => 'Hacked',
                'comment' => null,
            ]);
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'You do not have permission to update this IP address');
    }

    public function test_super_admin_can_update_any_ip_address(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->superAdmin()->create();
        $ip = IpAddress::create([
            'ip_address' => '10.0.0.3',
            'label' => 'User IP',
            'comment' => null,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAsUser($admin)
            ->putJson("/api/ip-addresses/{$ip->id}", [
                'label' => 'Admin Updated',
                'comment' => null,
            ]);
        $response->assertStatus(200);
        $response->assertJsonPath('data.label', 'Admin Updated');
    }

    public function test_regular_user_cannot_delete_ip_address(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $ip = IpAddress::create([
            'ip_address' => '10.0.0.4',
            'label' => 'My IP',
            'comment' => null,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAsUser($user)->deleteJson("/api/ip-addresses/{$ip->id}");
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'You do not have permission to delete IP addresses');
        $this->assertDatabaseHas('ip_addresses', ['id' => $ip->id]);
    }

    public function test_super_admin_can_delete_any_ip_address(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->superAdmin()->create();
        $ip = IpAddress::create([
            'ip_address' => '10.0.0.5',
            'label' => 'To Delete',
            'comment' => null,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAsUser($admin)->deleteJson("/api/ip-addresses/{$ip->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('message', 'IP address deleted successfully');
        $this->assertDatabaseMissing('ip_addresses', ['id' => $ip->id]);
    }
}
