<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Requisition;
use App\Policies\BusinessPolicy;
use App\Policies\RequisitionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Item;
use App\Policies\ItemPolicy;

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
        //
    }
} 