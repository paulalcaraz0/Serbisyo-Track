<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Holiday;
use App\Models\User;
use Database\Seeders\CalendarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_holiday_administration(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);

        $this->actingAs($staff)->get(route('admin.holidays.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.holidays.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('admin.holidays.store'), $this->payload())->assertForbidden();
    }

    public function test_guests_cannot_access_holiday_administration(): void
    {
        $this->get(route('admin.holidays.index'))->assertRedirect(route('login'));
    }

    public function test_administrator_can_list_holidays(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $this->seed(CalendarSeeder::class);

        $this->actingAs($admin)
            ->get(route('admin.holidays.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/holidays/index')
                ->has('holidays.data', 8)
                ->where('holidays.data.0.date', '2026-08-31'));
    }

    public function test_administrator_can_create_a_holiday_and_an_audit_event_is_recorded(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($admin)
            ->post(route('admin.holidays.store'), $this->payload())
            ->assertRedirect(route('admin.holidays.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('holidays', [
            'date' => '2026-09-14',
            'name_en' => 'Special non-working day',
            'name_fil' => 'Espesyal na di-araw ng trabaho',
            'is_recurring' => false,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'holiday.created',
            'subject_type' => 'holiday',
            'subject_identifier' => '2026-09-14',
        ]);
    }

    public function test_duplicate_dates_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        Holiday::query()->create([
            'date' => '2026-09-14',
            'name_en' => 'Existing holiday',
            'name_fil' => 'Umiiral na holiday',
            'is_recurring' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.holidays.create'))
            ->post(route('admin.holidays.store'), $this->payload())
            ->assertRedirect(route('admin.holidays.create'))
            ->assertSessionHasErrors(['date']);
    }

    public function test_bilingual_names_are_required(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($admin)
            ->from(route('admin.holidays.create'))
            ->post(route('admin.holidays.store'), ['date' => '2026-09-14', 'is_recurring' => false])
            ->assertRedirect(route('admin.holidays.create'))
            ->assertSessionHasErrors(['name_en', 'name_fil']);
    }

    public function test_administrator_can_update_a_holiday(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $holiday = Holiday::query()->create([
            'date' => '2026-09-14',
            'name_en' => 'Old name',
            'name_fil' => 'Lumang pangalan',
            'is_recurring' => false,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.holidays.update', $holiday), $this->payload([
                'name_en' => 'Renamed holiday',
                'name_fil' => 'Pinalitan ng pangalan',
                'is_recurring' => true,
            ]))
            ->assertRedirect(route('admin.holidays.index'));

        $holiday->refresh();
        $this->assertSame('Renamed holiday', $holiday->name_en);
        $this->assertTrue($holiday->is_recurring);
        $this->assertDatabaseHas('audit_events', ['action' => 'holiday.updated', 'subject_type' => 'holiday']);
    }

    public function test_administrator_can_remove_a_holiday(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $holiday = Holiday::query()->create([
            'date' => '2026-09-14',
            'name_en' => 'Removable holiday',
            'name_fil' => 'Matatanggal na holiday',
            'is_recurring' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.holidays.destroy', $holiday))
            ->assertRedirect(route('admin.holidays.index'));

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'holiday.deleted',
            'subject_type' => 'holiday',
            'subject_identifier' => '2026-09-14',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace([
            'date' => '2026-09-14',
            'name_en' => 'Special non-working day',
            'name_fil' => 'Espesyal na di-araw ng trabaho',
            'is_recurring' => false,
        ], $overrides);
    }
}
