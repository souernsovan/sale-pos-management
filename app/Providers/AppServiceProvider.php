<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        // Livewire bundles its own Alpine.js. Force it to inject on every page
        // (not just pages with a Livewire component) so Breeze's x-data usage
        // (nav dropdown, mobile menu) keeps working without a second Alpine copy.
        Livewire::forceAssetInjection();

        // Super Admin always passes every permission check, even one added
        // later that wasn't backfilled onto the role.
        Gate::before(fn ($user, $ability) => $user->hasRole('Super Admin') ? true : null);
    }
}
