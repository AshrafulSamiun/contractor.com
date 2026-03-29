<?php

namespace Tests\Feature;

use App\Models\Parcel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlanFeatureGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_plan_cannot_access_profiles_core_endpoint(): void
    {
        $user = $this->makeUser('basic', 'admin');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/facilities')
            ->assertStatus(403)
            ->assertJsonPath('data.required_plan', 'standard');
    }

    public function test_standard_plan_can_access_profiles_core_endpoint(): void
    {
        $user = $this->makeUser('standard', 'admin');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/facilities')
            ->assertOk();
    }

    public function test_standard_plan_cannot_access_enterprise_only_modules(): void
    {
        $user = $this->makeUser('standard', 'admin');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/users')
            ->assertStatus(403)
            ->assertJsonPath('data.required_plan', 'enterprise');

        $this->getJson('/api/v1/workforce/daily-reports')
            ->assertStatus(403)
            ->assertJsonPath('data.required_plan', 'enterprise');

        $this->getJson('/api/v1/notifications')
            ->assertStatus(403)
            ->assertJsonPath('data.required_plan', 'enterprise');
    }

    public function test_enterprise_plan_can_access_enterprise_modules(): void
    {
        $admin = $this->makeUser('enterprise', 'admin');
        $staff = $this->makeUser('enterprise', 'staff');
        Parcel::create([
            'user_id' => $admin->id,
            'recipient_name' => 'Demo Parcel',
            'status' => 'pending',
            'tracking_code' => 'ENT-1',
            'received_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/users')->assertOk();
        $this->getJson('/api/v1/workforce/daily-reports')->assertOk();
        $this->get('/api/v1/reports/parcels')->assertOk();
    }

    public function test_standard_plan_cannot_use_external_locker_pickup_rules(): void
    {
        $user = $this->makeUser('standard', 'admin');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/pickup-rules?type=external_locker')
            ->assertStatus(403);
    }

    public function test_enterprise_plan_can_use_external_locker_pickup_rules(): void
    {
        $user = $this->makeUser('enterprise', 'admin');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/pickup-rules?type=external_locker')
            ->assertOk();
    }

    private function makeUser(string $plan, string $role): User
    {
        return User::factory()->create([
            'company_name' => 'PlanGate Co',
            'selected_plan' => $plan,
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
