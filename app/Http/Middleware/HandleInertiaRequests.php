<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\OfficeSetting;
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
        $locale = app()->getLocale();

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'locale' => $locale,
            'supportedLocales' => $supportedLocales,
            'translations' => array_replace_recursive(Lang::get('serbisyo'), Lang::get('phase2'), Lang::get('phase3'), Lang::get('phase4'), Lang::get('phase5'), Lang::get('phase6'), Lang::get('phase7')),
            'office' => function () use ($locale): array {
                $settings = OfficeSetting::current();

                return [
                    'name' => $locale === 'fil' ? $settings->office_name_fil : $settings->office_name_en,
                    'address' => $locale === 'fil' ? $settings->address_fil : $settings->address_en,
                    'email' => $settings->contact_email,
                    'phone' => $settings->contact_phone,
                ];
            },
            'announcements' => function () use ($locale): array {
                return Announcement::query()
                    ->active()
                    ->get()
                    ->map(fn (Announcement $announcement): array => [
                        'id' => $announcement->id,
                        'level' => $announcement->level,
                        'message' => $locale === 'fil' ? $announcement->message_fil : $announcement->message_en,
                        'starts_at' => $announcement->starts_at?->toIso8601String(),
                        'ends_at' => $announcement->ends_at?->toIso8601String(),
                    ])
                    ->values()
                    ->all();
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'auth' => [
                'user' => $user?->only(['id', 'name', 'email', 'email_verified_at', 'role']),
            ],
        ]);
    }
}
