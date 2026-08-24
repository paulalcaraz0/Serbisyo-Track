<?php

namespace Tests\Unit;

use App\Models\Holiday;
use App\Support\BusinessDayCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessDayCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekends_are_skipped(): void
    {
        // Friday, 2026-08-21 → one business day lands Monday, 2026-08-24.
        $due = BusinessDayCalculator::add(CarbonImmutable::parse('2026-08-21'), 1);

        $this->assertSame('2026-08-24', $due->toDateString());
    }

    public function test_declared_holidays_are_skipped(): void
    {
        Holiday::query()->create([
            'date' => '2026-08-24',
            'name_en' => 'National Heroes Day',
            'name_fil' => 'Araw ng mga Bayani',
            'is_recurring' => false,
        ]);

        $due = BusinessDayCalculator::add(CarbonImmutable::parse('2026-08-21'), 1);

        $this->assertSame('2026-08-25', $due->toDateString());
    }

    public function test_recurring_holidays_apply_to_every_year(): void
    {
        Holiday::query()->create([
            'date' => '1900-01-01',
            'name_en' => "New Year's Day",
            'name_fil' => 'Araw ng Bagong Taon',
            'is_recurring' => true,
        ]);

        // Thursday, 2026-12-31 → skips Friday (recurring New Year) and the weekend.
        $due = BusinessDayCalculator::add(CarbonImmutable::parse('2026-12-31'), 1);

        $this->assertSame('2027-01-04', $due->toDateString());
    }

    public function test_multiple_consecutive_holidays_and_a_weekend_are_skipped(): void
    {
        Holiday::query()->create([
            'date' => '2026-12-25',
            'name_en' => 'Christmas Day',
            'name_fil' => 'Araw ng Pasko',
            'is_recurring' => true,
        ]);
        Holiday::query()->create([
            'date' => '2026-12-28',
            'name_en' => 'Special non-working day',
            'name_fil' => 'Espesyal na di-araw ng trabaho',
            'is_recurring' => false,
        ]);

        // Friday, 2026-12-24 → skips Christmas (Fri), weekend, and Dec 28.
        $due = BusinessDayCalculator::add(CarbonImmutable::parse('2026-12-24'), 1);

        $this->assertSame('2026-12-29', $due->toDateString());
    }

    public function test_zero_or_negative_days_still_yield_the_next_business_day(): void
    {
        $due = BusinessDayCalculator::add(CarbonImmutable::parse('2026-08-21'), 0);

        $this->assertSame('2026-08-24', $due->toDateString());
    }
}
