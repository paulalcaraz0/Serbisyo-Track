<?php

namespace App\Support;

use Illuminate\Http\Request;

class ResidentTrackingAccess
{
    private const SESSION_KEY = 'resident_tracking_grants';

    public static function grant(Request $request, string $reference): void
    {
        $grants = self::activeGrants($request);
        $grants[$reference] = now()->addMinutes((int) config('serbisyo.tracking_access_minutes', 15))->timestamp;

        $request->session()->put(self::SESSION_KEY, $grants);
    }

    public static function allows(Request $request, string $reference): bool
    {
        $grants = self::activeGrants($request);
        $request->session()->put(self::SESSION_KEY, $grants);

        return isset($grants[$reference]);
    }

    /** @return array<string, int> */
    private static function activeGrants(Request $request): array
    {
        $stored = $request->session()->get(self::SESSION_KEY, []);

        if (! is_array($stored)) {
            return [];
        }

        $now = now()->timestamp;
        $active = [];

        foreach ($stored as $reference => $expiresAt) {
            if (is_string($reference) && is_int($expiresAt) && $expiresAt >= $now) {
                $active[$reference] = $expiresAt;
            }
        }

        return $active;
    }
}
