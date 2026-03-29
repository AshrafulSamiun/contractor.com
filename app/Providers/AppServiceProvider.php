<?php

namespace App\Providers;

use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Gate::define('owns-resource', function (User $user, mixed $resource): bool {
            if (!is_object($resource)) {
                return false;
            }

            $ownerId = data_get($resource, 'user_id');
            if ($ownerId === null) {
                return false;
            }

            return (int) $ownerId === (int) $user->id;
        });

        Gate::define('permission', function (User $user, string $module, string $action = 'read'): bool {
            if (!(bool) config('permissions.enforce', false)) {
                return true;
            }

            return app(PermissionService::class)->can($user, $module, $action);
        });
    }
}
