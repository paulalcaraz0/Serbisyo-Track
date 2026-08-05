<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Policies\ServicePolicy;
use App\Policies\ServiceRequestPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(ServiceRequest::class, ServiceRequestPolicy::class);

        RateLimiter::for('resident-submissions', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
        ]);

        RateLimiter::for('resident-tracking', fn (Request $request) => [
            Limit::perMinute(30)->by('tracking-ip|'.$request->ip()),
            Limit::perMinute(6)->by($request->ip().'|'.strtoupper((string) $request->input('reference'))),
        ]);

        RateLimiter::for('resident-downloads', fn (Request $request) => [
            Limit::perMinute(20)->by($request->ip()),
        ]);
    }
}
