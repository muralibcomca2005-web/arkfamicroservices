<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('only-admin', function(User $user){
            return $user->role === UserRole::ADMIN;
        });
        Gate::define('only-teacher', function(User $user){
            return $user->role === UserRole::TEACHER;
        });
        Gate::define('only-student', function(User $user){
            return $user->role === UserRole::STUDENT;
        });
    }
}
