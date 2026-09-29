<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeAIBhouiyanUserSeeder extends Seeder
{
    private const TARGET_EMAIL = 'a.i.bhouiyan@gmail.com';

    /**
     * Tables with these columns own user-scoped records and should be deleted.
     */
    private const OWNERSHIP_COLUMNS = [
        'user_id',
    ];

    /**
     * These columns can point at a user on shared records; null them instead.
     */
    private const NULLABLE_REFERENCE_COLUMNS = [
        'target_user_id',
        'changed_by_user_id',
        'granted_by_user_id',
        'assignee_user_id',
        'reviewed_by_user_id',
        'created_by',
        'updated_by',
    ];

    public function run(): void
    {
        $user = User::query()->where('email', self::TARGET_EMAIL)->first();

        if (! $user) {
            $this->command?->warn('No user found for '.self::TARGET_EMAIL.'.');

            return;
        }

        $tables = $this->listTables();
        $userId = (int) $user->id;
        $email = (string) $user->email;

        DB::transaction(function () use ($tables, $userId, $email, $user): void {
            $this->deletePersonalAccessTokens($userId);
            $this->deleteSessions($userId);
            $this->deletePasswordResetTokens($email);

            foreach ($tables as $table) {
                if ($table === 'users') {
                    continue;
                }

                foreach (self::OWNERSHIP_COLUMNS as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::table($table)->where($column, $userId)->delete();
                    }
                }

                foreach (self::NULLABLE_REFERENCE_COLUMNS as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::table($table)->where($column, $userId)->update([$column => null]);
                    }
                }
            }

            $user->delete();
        });

        $this->command?->info('Deleted user and related records for '.self::TARGET_EMAIL.'.');
    }

    /**
     * @return list<string>
     */
    protected function listTables(): array
    {
        $database = DB::getDatabaseName();
        $rows = DB::select('SHOW TABLES');
        $key = 'Tables_in_'.$database;

        return collect($rows)
            ->map(fn (object $row) => (string) ($row->{$key} ?? ''))
            ->filter(fn (string $table) => $table !== '' && $table !== 'migrations')
            ->values()
            ->all();
    }

    protected function deletePersonalAccessTokens(int $userId): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->delete();
    }

    protected function deleteSessions(int $userId): void
    {
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        DB::table('sessions')->where('user_id', $userId)->delete();
    }

    protected function deletePasswordResetTokens(string $email): void
    {
        if (! Schema::hasTable('password_reset_tokens')) {
            return;
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}
