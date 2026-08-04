<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        /** @var array<string, string> $supportedLocales */
        $supportedLocales = config('serbisyo.locales');

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'locale' => app()->getLocale(),
            'supportedLocales' => $supportedLocales,
            'translations' => array_replace_recursive(Lang::get('serbisyo'), Lang::get('phase2')),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
            'auth' => [
                'user' => $user?->only(['id', 'name', 'email', 'email_verified_at', 'role']),
            ],
        ]);
    }
}
