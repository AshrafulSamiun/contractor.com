<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Facility;
use App\Models\Permission;
use App\Models\Recipient;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnnouncementBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('permissions.enforce', true);
    }

    public function test_cross_company_announcement_is_not_visible_even_when_audience_is_all(): void
    {
        $viewer = $this->makeUser('manager', 'viewer-alpha@example.com', 'Alpha Co');
        $owner = $this->makeUser('manager', 'owner-beta@example.com', 'Beta Co');

        $announcement = Announcement::query()->create([
            'user_id' => $owner->id,
            'company_name' => 'beta co',
            'title' => 'Cross Company Notice',
            'body' => 'Should not be visible outside company scope.',
            'priority' => 'normal',
            'status' => 'published',
            'audience' => 'all',
            'target_roles' => [],
            'requires_approval' => false,
            'approval_status' => null,
            'publish_at' => now(),
            'expires_at' => null,
            'pinned' => false,
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/announcements/' . $announcement->id)->assertStatus(403);
        $this->getJson('/api/v1/announcements')
            ->assertOk()
            ->assertJsonMissing(['id' => $announcement->id]);
    }

    public function test_store_rejects_direct_published_status_without_publish_permission(): void
    {
        $manager = $this->makeUser('manager', 'manager-no-publish@example.com', 'Alpha Co');

        $publishPermission = Permission::query()->firstOrCreate(
            ['module' => 'announcements', 'action' => 'publish'],
            ['key' => 'announcements.publish']
        );

        UserPermission::query()->create([
            'user_id' => $manager->id,
            'permission_id' => $publishPermission->id,
            'allowed' => false,
            'granted_by_user_id' => $manager->id,
        ]);

        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/announcements', $this->announcementPayload([
            'status' => 'published',
        ]))->assertStatus(403);

        $this->postJson('/api/v1/announcements', $this->announcementPayload([
            'status' => 'draft',
        ]))->assertCreated();
    }

    public function test_role_based_audience_requires_target_roles(): void
    {
        $manager = $this->makeUser('manager', 'manager-roles@example.com', 'Alpha Co');

        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/announcements', $this->announcementPayload([
            'audience' => 'roles',
            'target_roles' => [],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['target_roles']);
    }

    public function test_recipient_mode_one_requires_exactly_one_recipient(): void
    {
        $manager = $this->makeUser('manager', 'manager-recipient-one@example.com', 'Alpha Co');

        Facility::query()->create([
            'user_id' => $manager->id,
            'facility_name' => 'Main Facility',
            'status' => 'Active',
        ]);

        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/announcements', $this->announcementPayload([
            'recipient_mode' => 'one',
            'recipient_ids' => [],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_ids']);
    }

    public function test_required_action_row_must_include_action_and_due_datetime(): void
    {
        $manager = $this->makeUser('manager', 'manager-required-actions@example.com', 'Alpha Co');

        $recipient = Recipient::query()->create([
            'user_id' => $manager->id,
            'recipient_name' => 'Recipient One',
            'recipient_types' => ['residential'],
            'facility_name' => 'Main Facility',
            'is_active' => true,
        ]);

        Sanctum::actingAs($manager);

        $this->postJson('/api/v1/announcements', $this->announcementPayload([
            'recipient_mode' => 'one',
            'recipient_ids' => [$recipient->id],
            'required_actions' => [
                ['action' => 'Call resident', 'due_at' => null],
            ],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['required_actions.0']);
    }

    private function makeUser(string $role, string $email, string $company): User
    {
        return User::factory()->create([
            'name' => ucfirst($role) . ' User',
            'email' => $email,
            'company_name' => $company,
            'selected_plan' => 'enterprise',
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
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
            'announcement_no' => null,
            'occurred_at' => now()->toISOString(),
            'facility_id' => null,
            'recipient_mode' => 'all',
            'recipient_ids' => [],
            'required_actions' => [],
            'requires_approval' => false,
            'approval_status' => null,
            'publish_at' => null,
            'expires_at' => null,
            'pinned' => false,
        ], $overrides);
    }
}
