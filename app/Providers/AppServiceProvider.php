<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;


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
        $this->registerPolicies();

        Gate::define('view-all', function (User $user){
            return $user->role === 'ADM';
        });

        Gate::define('view-reports', function (User $user) {
            return in_array($user->role, ['ADM', 'DIR']);
        });
    }
}
