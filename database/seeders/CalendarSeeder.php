<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Holiday;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Recurring national holidays use fixed month-day dates; floating
     * observances are seeded as dated, non-recurring entries.
     *
     * @return array<int, array{date: string, name_en: string, name_fil: string, is_recurring: bool}>
     */
    private function holidays(): array
    {
        return [
            ['date' => '2000-01-01', 'name_en' => "New Year's Day", 'name_fil' => 'Araw ng Bagong Taon', 'is_recurring' => true],
            ['date' => '2000-04-09', 'name_en' => 'Araw ng Kagitingan', 'name_fil' => 'Araw ng Kagitingan', 'is_recurring' => true],
            ['date' => '2000-05-01', 'name_en' => 'Labor Day', 'name_fil' => 'Araw ng Manggagawa', 'is_recurring' => true],
            ['date' => '2000-06-12', 'name_en' => 'Independence Day', 'name_fil' => 'Araw ng Kalayaan', 'is_recurring' => true],
            ['date' => '2026-08-31', 'name_en' => 'National Heroes Day', 'name_fil' => 'Araw ng mga Bayani', 'is_recurring' => false],
            ['date' => '2000-11-30', 'name_en' => 'Bonifacio Day', 'name_fil' => 'Araw ni Bonifacio', 'is_recurring' => true],
            ['date' => '2000-12-25', 'name_en' => 'Christmas Day', 'name_fil' => 'Araw ng Pasko', 'is_recurring' => true],
            ['date' => '2000-12-30', 'name_en' => 'Rizal Day', 'name_fil' => 'Araw ni Rizal', 'is_recurring' => true],
        ];
    }

    public function run(): void
    {
        foreach ($this->holidays() as $holiday) {
            Holiday::query()->firstOrCreate(
                ['date' => $holiday['date']],
                [
                    'name_en' => $holiday['name_en'],
                    'name_fil' => $holiday['name_fil'],
                    'is_recurring' => $holiday['is_recurring'],
                ],
            );
        }

        if (app()->environment('production')) {
            return;
        }

        Announcement::query()->firstOrCreate(
            ['message_en' => 'The barangay hall is open Monday to Friday, 8:00 AM to 5:00 PM.'],
            [
                'message_fil' => 'Ang barangay hall ay bukas Lunes hanggang Biyernes, 8:00 AM hanggang 5:00 PM.',
                'level' => 'info',
                'starts_at' => now()->startOfDay(),
                'ends_at' => null,
                'is_active' => true,
            ],
        );

        Announcement::query()->firstOrCreate(
            ['message_en' => 'Demonstration notice: the civil registry counter is closed this Friday afternoon for a staff assembly.'],
            [
                'message_fil' => 'Abisong demonstrasyon: sarado ang counter ng civil registry ngayong Biyernes ng hapon para sa staff assembly.',
                'level' => 'warning',
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addDays(14)->endOfDay(),
                'is_active' => true,
            ],
        );
    }
}
