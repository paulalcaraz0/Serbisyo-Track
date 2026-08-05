<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\OfficeSetting;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\AuditEventPolicy;
use App\Policies\OfficeSettingPolicy;
use App\Policies\ServicePolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\UserPolicy;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(OfficeSetting::class, OfficeSettingPolicy::class);
        Gate::policy(AuditEvent::class, AuditEventPolicy::class);
        Gate::define('viewReports', fn (User $user): bool => $user->is_active && $user->role === UserRole::Administrator);

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

        RateLimiter::for('staff-mutations', fn (Request $request) => [
            Limit::perMinute(30)->by('staff|'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
        ]);

        RateLimiter::for('admin-mutations', fn (Request $request) => [
            Limit::perMinute(20)->by('admin|'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
        ]);
    }
}
