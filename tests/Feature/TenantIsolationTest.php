<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Models\Parcel;
use App\Models\PickupRule;
use App\Models\PlanChangeLog;
use App\Models\User;
use App\Models\WorkforceApproval;
use App\Models\WorkforceDailyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parcel_endpoints_are_scoped_to_authenticated_user(): void
    {
        $alphaAdmin = $this->makeUser('Alpha', 'admin');
        $betaStaff = $this->makeUser('Beta', 'staff');

        $alphaParcel = Parcel::create([
            'user_id' => $alphaAdmin->id,
            'recipient_name' => 'Alpha Recipient',
            'status' => 'pending',
            'tracking_code' => 'A-TRACK-001',
            'received_at' => now(),
        ]);
        $betaParcel = Parcel::create([
            'user_id' => $betaStaff->id,
            'recipient_name' => 'Beta Recipient',
            'status' => 'pending',
            'tracking_code' => 'B-TRACK-001',
            'received_at' => now(),
        ]);

        Sanctum::actingAs($alphaAdmin);

        $index = $this->getJson('/api/v1/parcels');
        $index->assertOk();
        $ids = collect($index->json('data.data'))->pluck('id')->all();
        $this->assertContains($alphaParcel->id, $ids);
        $this->assertNotContains($betaParcel->id, $ids);

        $this->getJson('/api/v1/parcels/' . $betaParcel->id)->assertStatus(404);
        $this->putJson('/api/v1/parcels/' . $betaParcel->id, ['status' => 'delivered'])->assertStatus(404);
        $this->postJson('/api/v1/parcels/scan', [
            'tracking_code' => $betaParcel->tracking_code,
            'status' => 'delivered',
        ])->assertStatus(404);
        $this->getJson('/api/v1/parcels/' . $betaParcel->id . '/history')->assertStatus(404);
    }

    public function test_dashboard_and_report_are_scoped_to_authenticated_user(): void
    {
        $alphaAdmin = $this->makeUser('Alpha', 'admin');
        $betaStaff = $this->makeUser('Beta', 'staff');

        Parcel::create([
            'user_id' => $alphaAdmin->id,
            'recipient_name' => 'Alpha One',
            'status' => 'pending',
            'tracking_code' => 'A-TRACK-100',
            'received_at' => now(),
        ]);
        Parcel::create([
            'user_id' => $alphaAdmin->id,
            'recipient_name' => 'Alpha Two',
            'status' => 'delivered',
            'tracking_code' => 'A-TRACK-200',
            'received_at' => now(),
            'delivered_at' => now(),
        ]);
        Parcel::create([
            'user_id' => $betaStaff->id,
            'recipient_name' => 'Beta Hidden',
            'status' => 'pending',
            'tracking_code' => 'B-TRACK-200',
            'received_at' => now(),
        ]);

        Sanctum::actingAs($alphaAdmin);

        $dashboard = $this->getJson('/api/v1/dashboard');
        $dashboard->assertOk();
        $this->assertSame(2, $dashboard->json('data.kpis.total'));
        $this->assertSame(1, $dashboard->json('data.kpis.pending'));

        $csv = $this->get('/api/v1/reports/parcels');
        $csv->assertOk();
        $content = $csv->streamedContent();
        $this->assertStringContainsString('Alpha One', $content);
        $this->assertStringContainsString('Alpha Two', $content);
        $this->assertStringNotContainsString('Beta Hidden', $content);
    }

    public function test_dashboard_active_users_fallback_does_not_leak_when_company_name_is_null(): void
    {
        $alpha = User::factory()->create([
            'company_name' => null,
            'selected_plan' => 'enterprise',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'company_name' => null,
            'selected_plan' => 'enterprise',
            'role' => 'staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($alpha);

        $dashboard = $this->getJson('/api/v1/dashboard');
        $dashboard->assertOk();
        $this->assertSame(1, $dashboard->json('data.plan.active_users'));
    }

    public function test_admin_user_management_is_company_scoped(): void
    {
        $alphaAdmin = $this->makeUser('Alpha', 'admin');
        $alphaStaff = $this->makeUser('Alpha', 'staff');
        $betaStaff = $this->makeUser('Beta', 'staff');

        Sanctum::actingAs($alphaAdmin);

        $list = $this->getJson('/api/v1/admin/users');
        $list->assertOk();
        $ids = collect($list->json('data.data'))->pluck('id')->all();
        $this->assertContains($alphaAdmin->id, $ids);
        $this->assertContains($alphaStaff->id, $ids);
        $this->assertNotContains($betaStaff->id, $ids);

        $this->patchJson('/api/v1/admin/users/' . $betaStaff->id, [
            'role' => 'manager',
        ])->assertStatus(404);

        $create = $this->postJson('/api/v1/admin/users', [
            'name' => 'New Alpha User',
            'email' => 'new-alpha-user@example.com',
            'password' => 'Password123!',
            'role' => 'staff',
        ]);
        $create->assertCreated();
        $this->assertSame('Alpha', $create->json('data.company_name'));
    }

    public function test_plan_history_and_notifications_are_company_scoped(): void
    {
        $alphaAdmin = $this->makeUser('Alpha', 'admin');
        $alphaStaff = $this->makeUser('Alpha', 'staff');
        $betaStaff = $this->makeUser('Beta', 'staff');

        PlanChangeLog::create([
            'user_id' => $alphaStaff->id,
            'from_plan' => 'basic',
            'to_plan' => 'standard',
            'changed_by_user_id' => $alphaAdmin->id,
        ]);
        PlanChangeLog::create([
            'user_id' => $betaStaff->id,
            'from_plan' => 'basic',
            'to_plan' => 'enterprise',
            'changed_by_user_id' => $betaStaff->id,
        ]);

        NotificationLog::create([
            'user_id' => $alphaStaff->id,
            'channel' => 'email',
            'status' => 'sent',
            'to' => 'alpha@example.com',
            'context' => 'test',
        ]);
        NotificationLog::create([
            'user_id' => $betaStaff->id,
            'channel' => 'email',
            'status' => 'sent',
            'to' => 'beta@example.com',
            'context' => 'test',
        ]);

        Sanctum::actingAs($alphaAdmin);

        $planHistory = $this->getJson('/api/v1/plans/history');
        $planHistory->assertOk();
        $planUserIds = collect($planHistory->json('data'))->pluck('user_id')->all();
        $this->assertContains($alphaStaff->id, $planUserIds);
        $this->assertNotContains($betaStaff->id, $planUserIds);

        $notifications = $this->getJson('/api/v1/notifications');
        $notifications->assertOk();
        $notificationUserIds = collect($notifications->json('data'))->pluck('user_id')->all();
        $this->assertContains($alphaStaff->id, $notificationUserIds);
        $this->assertNotContains($betaStaff->id, $notificationUserIds);
    }

    public function test_workforce_approval_index_is_company_and_role_scoped(): void
    {
        $alphaAdmin = $this->makeUser('Alpha', 'admin');
        $alphaStaff = $this->makeUser('Alpha', 'staff');
        $betaStaff = $this->makeUser('Beta', 'staff');

        $alphaReport = WorkforceDailyReport::create([
            'user_id' => $alphaStaff->id,
            'report_no' => 'DR-ALPHA-1',
            'report_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);
        $betaReport = WorkforceDailyReport::create([
            'user_id' => $betaStaff->id,
            'report_no' => 'DR-BETA-1',
            'report_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        WorkforceApproval::create([
            'user_id' => $alphaStaff->id,
            'entity_type' => 'daily_report',
            'entity_id' => $alphaReport->id,
            'status' => 'submitted',
            'step' => 'submit',
        ]);
        WorkforceApproval::create([
            'user_id' => $alphaAdmin->id,
            'entity_type' => 'daily_report',
            'entity_id' => $alphaReport->id,
            'status' => 'approved',
            'step' => 'approve',
        ]);
        WorkforceApproval::create([
            'user_id' => $betaStaff->id,
            'entity_type' => 'daily_report',
            'entity_id' => $betaReport->id,
            'status' => 'submitted',
            'step' => 'submit',
        ]);

        Sanctum::actingAs($alphaAdmin);
        $adminAlphaView = $this->getJson('/api/v1/workforce/approvals?entity_type=daily_report&entity_id=' . $alphaReport->id);
        $adminAlphaView->assertOk();
        $this->assertCount(2, $adminAlphaView->json('data'));

        $adminBetaView = $this->getJson('/api/v1/workforce/approvals?entity_type=daily_report&entity_id=' . $betaReport->id);
        $adminBetaView->assertOk();
        $this->assertCount(0, $adminBetaView->json('data'));

        Sanctum::actingAs($alphaStaff);
        $staffAlphaView = $this->getJson('/api/v1/workforce/approvals?entity_type=daily_report&entity_id=' . $alphaReport->id);
        $staffAlphaView->assertOk();
        $rows = $staffAlphaView->json('data');
        $this->assertCount(1, $rows);
        $this->assertSame($alphaStaff->id, $rows[0]['user_id']);
    }

    public function test_pickup_rules_are_scoped_to_owner_user(): void
    {
        $alphaAdmin = $this->makeUser('Alpha', 'admin');
        $betaAdmin = $this->makeUser('Beta', 'admin');

        $alphaRule = PickupRule::create([
            'user_id' => $alphaAdmin->id,
            'type' => 'front_desk',
            'allowed_roles' => ['admin'],
            'name' => 'Alpha Front Desk Rule',
            'window_hours' => 24,
            'sla_hours' => 24,
            'active' => true,
            'created_by' => $alphaAdmin->id,
            'updated_by' => $alphaAdmin->id,
        ]);
        $betaRule = PickupRule::create([
            'user_id' => $betaAdmin->id,
            'type' => 'front_desk',
            'allowed_roles' => ['admin'],
            'name' => 'Beta Front Desk Rule',
            'window_hours' => 24,
            'sla_hours' => 24,
            'active' => true,
            'created_by' => $betaAdmin->id,
            'updated_by' => $betaAdmin->id,
        ]);

        Sanctum::actingAs($alphaAdmin);

        $list = $this->getJson('/api/v1/pickup-rules');
        $list->assertOk();
        $ids = collect($list->json('data'))->pluck('id')->all();
        $this->assertContains($alphaRule->id, $ids);
        $this->assertNotContains($betaRule->id, $ids);

        $this->getJson('/api/v1/pickup-rules/' . $betaRule->id . '/audits')->assertStatus(404);

        $summary = $this->getJson('/api/v1/pickup-rules/sla/summary');
        $summary->assertOk();
        $this->assertSame(1, $summary->json('data.summary.front_desk.total'));
    }

    private function makeUser(string $companyName, string $role): User
    {
        return User::factory()->create([
            'company_name' => $companyName,
            'selected_plan' => 'enterprise',
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
