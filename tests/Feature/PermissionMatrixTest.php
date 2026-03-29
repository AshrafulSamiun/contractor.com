<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\EmailMessage;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_read_users_but_cannot_create_user_by_default(): void
    {
        $manager = $this->makeUser('manager');

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/admin/users')->assertOk();
        $this->postJson('/api/v1/admin/users', [
            'name' => 'Blocked Manager Create',
            'email' => 'blocked-manager-create@example.com',
            'password' => 'Password123!',
            'role' => 'staff',
        ])->assertStatus(403);
    }

    public function test_user_override_can_allow_manager_to_create_users(): void
    {
        $manager = $this->makeUser('manager');
        $permission = Permission::query()->where('module', 'users')->where('action', 'create')->firstOrFail();

        UserPermission::query()->create([
            'user_id' => $manager->id,
            'permission_id' => $permission->id,
            'allowed' => true,
            'granted_by_user_id' => $manager->id,
        ]);

        Sanctum::actingAs($manager);

        $create = $this->postJson('/api/v1/admin/users', [
            'name' => 'Allowed Manager Create',
            'email' => 'allowed-manager-create@example.com',
            'password' => 'Password123!',
            'role' => 'staff',
        ]);

        $create->assertCreated();
        $this->assertSame('Allowed Manager Create', $create->json('data.name'));
    }

    public function test_user_override_can_deny_admin_access(): void
    {
        $admin = $this->makeUser('admin');
        $permission = Permission::query()->where('module', 'users')->where('action', 'read')->firstOrFail();

        UserPermission::query()->create([
            'user_id' => $admin->id,
            'permission_id' => $permission->id,
            'allowed' => false,
            'granted_by_user_id' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/users')->assertStatus(403);
    }

    public function test_admin_can_view_and_update_permission_matrix(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser('staff', 'target-staff@example.com');

        Sanctum::actingAs($admin);

        $view = $this->getJson('/api/v1/admin/users/' . $target->id . '/permissions');
        $view->assertOk();
        $rows = collect($view->json('data.rows'));
        $this->assertTrue($rows->contains(fn ($row) => $row['module'] === 'users' && $row['action'] === 'read'));

        $update = $this->putJson('/api/v1/admin/users/' . $target->id . '/permissions', [
            'permissions' => [
                ['module' => 'users', 'action' => 'read', 'allowed' => true],
                ['module' => 'users', 'action' => 'create', 'allowed' => false],
                ['module' => 'reports', 'action' => 'read', 'allowed' => true],
            ],
        ]);
        $update->assertOk();

        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $target->id,
            'allowed' => 1,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'target_user_id' => $target->id,
            'module' => 'users',
            'action' => 'permissions',
        ]);
    }

    public function test_user_edit_action_creates_activity_log(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser('staff', 'target-edit@example.com');

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/admin/users/' . $target->id, [
            'role' => 'manager',
            'is_active' => false,
        ])->assertOk();

        $log = ActivityLog::query()
            ->where('module', 'users')
            ->where('action', 'edit')
            ->where('target_user_id', $target->id)
            ->first();

        $this->assertNotNull($log);
    }

    public function test_staff_cannot_create_profiles_records_by_default(): void
    {
        $staff = $this->makeUser('staff', 'staff-profile-deny@example.com');

        Sanctum::actingAs($staff);

        $this->postJson('/api/v1/facilities', [
            'facility_name' => 'Restricted Facility',
        ])->assertStatus(403);
    }

    public function test_manager_can_create_profiles_records_by_default(): void
    {
        $manager = $this->makeUser('manager', 'manager-profile-allow@example.com');

        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/facilities', [
            'facility_name' => 'Allowed Facility',
        ])->assertCreated();
    }

    public function test_manager_can_create_calendar_event_but_cannot_delete_by_default(): void
    {
        $manager = $this->makeUser('manager', 'manager-calendar@example.com');

        Sanctum::actingAs($manager);

        $create = $this->postJson('/api/v1/calendar-events', $this->calendarPayload([
            'title' => 'Ops Standup',
        ]));
        $create->assertCreated();
        $eventId = (int) $create->json('data.id');

        $this->putJson('/api/v1/calendar-events/' . $eventId, $this->calendarPayload([
            'title' => 'Ops Standup Updated',
        ]))->assertOk();

        $this->deleteJson('/api/v1/calendar-events/' . $eventId)->assertStatus(403);
    }

    public function test_staff_can_read_announcements_but_cannot_create_or_publish(): void
    {
        $staff = $this->makeUser('staff', 'staff-announcements@example.com');
        $owner = $this->makeUser('manager', 'owner-announcement@example.com');

        $announcement = Announcement::query()->create([
            'user_id' => $owner->id,
            'title' => 'Public Notice',
            'body' => 'Read-only notice for all users.',
            'priority' => 'normal',
            'status' => 'draft',
            'audience' => 'all',
            'target_roles' => [],
            'requires_approval' => false,
            'approval_status' => null,
            'publish_at' => null,
            'expires_at' => null,
            'pinned' => false,
        ]);

        Sanctum::actingAs($staff);

        $this->getJson('/api/v1/announcements')->assertOk();
        $this->postJson('/api/v1/announcements', $this->announcementPayload())->assertStatus(403);
        $this->postJson('/api/v1/announcements/' . $announcement->id . '/publish')->assertStatus(403);
    }

    public function test_manager_can_publish_announcements_but_cannot_approve(): void
    {
        $manager = $this->makeUser('manager', 'manager-announcements@example.com');

        Sanctum::actingAs($manager);

        $create = $this->postJson('/api/v1/announcements', $this->announcementPayload([
            'title' => 'Manager Bulletin',
        ]));
        $create->assertCreated();
        $announcementId = (int) $create->json('data.id');

        $this->postJson('/api/v1/announcements/' . $announcementId . '/publish')->assertOk();
        $this->postJson('/api/v1/announcements/' . $announcementId . '/approve')->assertStatus(403);
    }

    public function test_manager_can_send_email_but_cannot_delete_message_by_default(): void
    {
        Mail::fake();

        $manager = $this->makeUser('manager', 'manager-email@example.com');

        Sanctum::actingAs($manager);

        $create = $this->postJson('/api/v1/email/messages', [
            'to_email' => 'recipient@example.com',
            'subject' => 'Draft Message',
            'body_html' => '<p>Draft body</p>',
            'status' => 'draft',
        ]);
        $create->assertCreated();
        $messageId = (int) $create->json('data.id');

        $this->postJson('/api/v1/email/messages/' . $messageId . '/send', [
            'to_email' => 'recipient@example.com',
            'subject' => 'Sent Message',
            'body_html' => '<p>Email body</p>',
        ])->assertOk();

        $this->deleteJson('/api/v1/email/messages/' . $messageId)->assertStatus(403);
    }

    public function test_user_override_can_allow_manager_to_delete_email_messages(): void
    {
        $manager = $this->makeUser('manager', 'manager-email-delete@example.com');
        $permission = Permission::query()->where('module', 'email')->where('action', 'delete')->firstOrFail();

        $message = EmailMessage::query()->create([
            'user_id' => $manager->id,
            'folder' => 'drafts',
            'direction' => 'outbound',
            'status' => 'draft',
            'to_email' => 'draft@example.com',
            'subject' => 'Draft to delete',
        ]);

        Sanctum::actingAs($manager);
        $this->deleteJson('/api/v1/email/messages/' . $message->id)->assertStatus(403);

        UserPermission::query()->create([
            'user_id' => $manager->id,
            'permission_id' => $permission->id,
            'allowed' => true,
            'granted_by_user_id' => $manager->id,
        ]);

        $this->deleteJson('/api/v1/email/messages/' . $message->id)->assertOk();
        $this->assertDatabaseMissing('email_messages', ['id' => $message->id]);
    }

    private function makeUser(string $role, ?string $email = null): User
    {
        return User::factory()->create([
            'name' => ucfirst($role) . ' User',
            'email' => $email ?: "{$role}-" . uniqid() . '@example.com',
            'company_name' => 'Permission Co',
            'selected_plan' => 'enterprise',
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function calendarPayload(array $overrides = []): array
    {
        $start = now()->addDay()->setTime(9, 0, 0);
        $end = (clone $start)->addHour();

        return array_merge([
            'title' => 'Team Meeting',
            'description' => 'Daily sync-up',
            'start_at' => $start->toISOString(),
            'end_at' => $end->toISOString(),
            'all_day' => false,
            'timezone' => 'UTC',
            'location' => 'Operations Desk',
            'color' => '#3b82f6',
            'status' => 'scheduled',
            'visibility' => 'private',
            'recurrence_freq' => null,
            'recurrence_interval' => null,
            'recurrence_until' => null,
            'reminders_json' => [10],
            'attendees_json' => ['ops@example.com'],
            'exceptions_json' => [],
        ], $overrides);
    }

    private function announcementPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Operations Update',
            'body' => 'Important announcement body',
            'priority' => 'normal',
            'status' => 'draft',
            'audience' => 'all',
            'target_roles' => [],
            'requires_approval' => false,
            'approval_status' => null,
            'publish_at' => null,
            'expires_at' => null,
            'pinned' => false,
        ], $overrides);
    }
}
