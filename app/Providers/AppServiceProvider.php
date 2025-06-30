<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Inertia;

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
        // Register broadcasting routes
        Broadcast::routes(['middleware' => ['auth']]);

        Inertia::share([
            'auth' => function () {
                $user = Auth::user();
                if (!$user) return null;
                $user->load('roles', 'roles.permissions');
                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                                'permissions' => $role->permissions->pluck('name')->toArray(),
                            ];
                        }),
                        'branch_id' => $user->branch_id,
                        'business_id' => $user->business_id,
                    ]
                ];
            }
        ]);
    }
}
