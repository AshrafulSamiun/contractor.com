<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\EmailMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RolePermissionSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('roleMatrixProvider')]
    public function test_role_permission_smoke_matrix(string $role, array $expected): void
    {
        Mail::fake();

        $user = $this->makeUser($role);
        $calendarEvent = CalendarEvent::query()->create([
            'user_id' => $user->id,
            'title' => 'Smoke Calendar Event',
            'description' => 'Permission smoke event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'all_day' => false,
            'timezone' => 'UTC',
            'location' => 'HQ',
            'color' => '#3b82f6',
            'status' => 'scheduled',
            'visibility' => 'private',
        ]);

        $announcement = Announcement::query()->create([
            'user_id' => $user->id,
            'title' => 'Smoke Announcement',
            'body' => 'Permission smoke announcement body',
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

        $message = EmailMessage::query()->create([
            'user_id' => $user->id,
            'folder' => 'drafts',
            'direction' => 'outbound',
            'status' => 'draft',
            'to_email' => 'recipient@example.com',
            'subject' => 'Smoke Draft',
            'body_html' => '<p>Smoke body</p>',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/users')
            ->assertStatus($expected['users_read']);

        $this->postJson('/api/v1/admin/users', [
            'name' => ucfirst($role) . ' Created User',
            'email' => "{$role}-created-" . uniqid() . '@example.com',
            'password' => 'Password123!',
            'role' => 'staff',
        ])->assertStatus($expected['users_create']);

        $this->putJson('/api/v1/settings/date-time', [
            'timezone' => 'UTC',
            'date_format' => 'MM/DD/YYYY',
            'time_format' => '12h',
            'week_start' => 'Monday',
        ])->assertStatus($expected['settings_edit']);

        $this->deleteJson('/api/v1/calendar-events/' . $calendarEvent->id)
            ->assertStatus($expected['calendar_delete']);

        $this->getJson('/api/v1/facilities')
            ->assertStatus($expected['facilities_read']);

        $this->postJson('/api/v1/facilities', [
            'facility_name' => ucfirst($role) . ' Facility',
            'status' => 'Active',
        ])->assertStatus($expected['facilities_create']);

        $this->getJson('/api/v1/pickup-rules')
            ->assertStatus($expected['pickup_read']);

        $this->getJson('/api/v1/pickup-rules/sla/export')
            ->assertStatus($expected['pickup_export']);

        $this->getJson('/api/v1/workforce/approvals?entity_type=daily_report&entity_id=1')
            ->assertStatus($expected['workforce_read']);

        $this->getJson('/api/v1/workforce/approvals/export')
            ->assertStatus($expected['workforce_export']);

        $this->getJson('/api/v1/reports/parcels')
            ->assertStatus($expected['reports_export']);

        $this->getJson('/api/v1/settings/notifications')
            ->assertStatus($expected['settings_notifications_read']);

        $this->postJson('/api/v1/announcements', [
            'title' => 'Matrix Announcement',
            'body' => 'Body',
            'priority' => 'normal',
            'status' => 'draft',
            'audience' => 'all',
            'target_roles' => [],
            'requires_approval' => false,
            'approval_status' => null,
            'publish_at' => null,
            'expires_at' => null,
            'pinned' => false,
        ])->assertStatus($expected['announcement_create']);

        $this->postJson('/api/v1/announcements/' . $announcement->id . '/publish')
            ->assertStatus($expected['announcement_publish']);

        $this->postJson('/api/v1/announcements/' . $announcement->id . '/approve')
            ->assertStatus($expected['announcement_approve']);

        $this->postJson('/api/v1/email/messages/' . $message->id . '/send', [
            'to_email' => 'recipient@example.com',
            'subject' => 'Smoke Sent',
            'body_html' => '<p>Sent body</p>',
        ])->assertStatus($expected['email_send']);

        $this->deleteJson('/api/v1/email/messages/' . $message->id)
            ->assertStatus($expected['email_delete']);
    }

    public static function roleMatrixProvider(): array
    {
        return [
            'admin' => [
                'role' => 'admin',
                'expected' => [
                    'users_read' => 200,
                    'users_create' => 201,
                    'settings_edit' => 200,
                    'calendar_delete' => 200,
                    'facilities_read' => 200,
                    'facilities_create' => 201,
                    'pickup_read' => 200,
                    'pickup_export' => 200,
                    'workforce_read' => 200,
                    'workforce_export' => 200,
                    'reports_export' => 200,
                    'settings_notifications_read' => 200,
                    'announcement_create' => 201,
                    'announcement_publish' => 200,
                    'announcement_approve' => 200,
                    'email_send' => 200,
                    'email_delete' => 200,
                ],
            ],
            'manager' => [
                'role' => 'manager',
                'expected' => [
                    'users_read' => 200,
                    'users_create' => 403,
                    'settings_edit' => 403,
                    'calendar_delete' => 403,
                    'facilities_read' => 200,
                    'facilities_create' => 201,
                    'pickup_read' => 200,
                    'pickup_export' => 200,
                    'workforce_read' => 200,
                    'workforce_export' => 200,
                    'reports_export' => 200,
                    'settings_notifications_read' => 200,
                    'announcement_create' => 201,
                    'announcement_publish' => 200,
                    'announcement_approve' => 403,
                    'email_send' => 200,
                    'email_delete' => 403,
                ],
            ],
            'staff' => [
                'role' => 'staff',
                'expected' => [
                    'users_read' => 403,
                    'users_create' => 403,
                    'settings_edit' => 403,
                    'calendar_delete' => 403,
                    'facilities_read' => 200,
                    'facilities_create' => 403,
                    'pickup_read' => 200,
                    'pickup_export' => 403,
                    'workforce_read' => 200,
                    'workforce_export' => 403,
                    'reports_export' => 403,
                    'settings_notifications_read' => 403,
                    'announcement_create' => 403,
                    'announcement_publish' => 403,
                    'announcement_approve' => 403,
                    'email_send' => 200,
                    'email_delete' => 403,
                ],
            ],
        ];
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'name' => ucfirst($role) . ' Smoke User',
            'email' => "{$role}-smoke-" . uniqid() . '@example.com',
            'company_name' => 'Smoke Co',
            'selected_plan' => 'enterprise',
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
