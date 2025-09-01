<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Requisition;
use App\Policies\BusinessPolicy;
use App\Policies\RequisitionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Item;
use App\Policies\ItemPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Business::class => BusinessPolicy::class,
        Item::class => ItemPolicy::class,
        Requisition::class => RequisitionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
         $this->registerPolicies();

        // Load permissions from DB so we include both name + guard
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            // Example key: "pos.accept payments" or "backoffice.manage employees"
            $ability = "{$permission->guard_name}.{$permission->name}";

            Gate::define($ability, function ($user) use ($permission) {
                // Only allow if user is authenticated via the correct guard
                if ($user->guard_name !== $permission->guard_name) {
                    return false;
                }

                // Check Spatie's built-in permission handling
                return $user->can($permission->name, $permission->guard_name);
            });
        }

        // Optional: super admin bypass
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        });
    }
} 