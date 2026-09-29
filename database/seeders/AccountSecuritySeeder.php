<?php

namespace Database\Seeders;

use App\Models\AccountSecurity;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSecuritySeeder extends Seeder
{
    public function run(): void
    {
        User::query()->whereNotNull('project_id')->each(function (User $user): void {
            AccountSecurity::updateOrCreate(
                ['project_id' => $user->project_id, 'user_id' => $user->id],
                [
                    'mfa_enabled' => true,
                    'verification_method' => 'Email & Phone',
                    'password_changed_at' => now()->subDays(17),
                    'pin_changed_at' => now()->subDays(17),
                    // Device, session, and login-history rows are created only by successful logins.
                    'registered_devices' => [],
                    'active_sessions' => [],
                    'login_history' => [],
                    'inserted_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );
        });
    }
}
