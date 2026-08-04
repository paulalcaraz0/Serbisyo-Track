<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ServiceSeeder::class);

        if (app()->environment('production')) {
            $this->command->warn('Demonstration credentials are never seeded in production.');

            return;
        }

        $administrator = User::query()->firstOrNew([
            'email' => 'admin@serbisyotrack.test',
        ]);

        $administrator->forceFill([
            'name' => 'Demo Administrator',
            'email_verified_at' => now(),
            'password' => Hash::make('SerbisyoTrack!2026'),
            'role' => UserRole::Administrator,
            'is_active' => true,
        ])->save();
    }
}
