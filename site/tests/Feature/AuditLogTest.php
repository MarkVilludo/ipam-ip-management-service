<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsUser(User $user): self
    {
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token);

        return $this;
    }

    public function test_audit_logs_index_requires_super_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAsUser($user)->getJson('/api/audit-logs');
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Access denied. Super admin role required.');
    }

    public function test_audit_logs_dashboard_requires_super_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAsUser($user)->getJson('/api/audit-logs/dashboard');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_audit_logs_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $response = $this->actingAsUser($admin)->getJson('/api/audit-logs/dashboard');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure(['success', 'data' => ['total_logs']]);
    }

    public function test_audit_logs_cannot_be_deleted(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Audit logs cannot be deleted');

        $activity = new Activity;
        $activity->delete();
    }

    public function test_audit_logs_force_delete_also_throws(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Audit logs cannot be deleted');

        $activity = new Activity;
        $activity->forceDelete();
    }
}
