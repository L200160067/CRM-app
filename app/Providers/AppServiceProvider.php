<?php

namespace App\Providers;

use App\Enums\Role;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // Paksa HTTPS pada semua server kecuali environment lokal (.test / localhost)
        $host = request()->getHost();
        if ($host && !str_ends_with($host, '.test') && $host !== 'localhost' && $host !== '127.0.0.1') {
            URL::forceScheme('https');
        }

        $this->configureDefaults();
        $this->configureGates();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    /**
     * Configure authorization gates.
     * SuperAdmin bypasses all policy checks.
     */
    protected function configureGates(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->role === Role::SuperAdmin) {
                return true;
            }
        });
    }
}
