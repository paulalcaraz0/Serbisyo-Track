<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\CalendarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_announcement_administration(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $announcement = Announcement::query()->create($this->payload());

        $this->actingAs($staff)->get(route('admin.announcements.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.announcements.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.announcements.edit', $announcement))->assertForbidden();
        $this->actingAs($staff)->put(route('admin.announcements.update', $announcement), $this->payload())->assertForbidden();
        $this->actingAs($staff)->delete(route('admin.announcements.destroy', $announcement))->assertForbidden();
    }

    public function test_administrator_can_publish_and_list_announcements(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), $this->payload())
            ->assertRedirect(route('admin.announcements.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('announcements', [
            'message_en' => 'The civil registry counter is closed this Friday afternoon.',
            'level' => 'warning',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'announcement.created',
            'subject_type' => 'announcement',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.announcements.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/announcements/index')
                ->has('announcements.data', 1)
                ->where('announcements.data.0.level', 'warning'));
    }

    public function test_invalid_severity_and_missing_translations_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($admin)
            ->from(route('admin.announcements.create'))
            ->post(route('admin.announcements.store'), [
                'message_en' => 'Only English',
                'level' => 'banana',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.announcements.create'))
            ->assertSessionHasErrors(['message_fil', 'level']);
    }

    public function test_end_date_must_not_precede_start_date(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), $this->payload([
                'starts_at' => '2026-09-20T17:00',
                'ends_at' => '2026-09-19T08:00',
            ]))
            ->assertSessionHasErrors(['ends_at']);
    }

    public function test_administrator_can_update_an_announcement(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $announcement = Announcement::query()->create($this->payload());

        $this->actingAs($admin)
            ->put(route('admin.announcements.update', $announcement), $this->payload([
                'level' => 'critical',
                'is_active' => false,
            ]))
            ->assertRedirect(route('admin.announcements.index'));

        $announcement->refresh();
        $this->assertSame('critical', $announcement->level);
        $this->assertFalse($announcement->is_active);
        $this->assertDatabaseHas('audit_events', ['action' => 'announcement.updated']);
    }

    public function test_administrator_can_remove_an_announcement(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $announcement = Announcement::query()->create($this->payload());

        $this->actingAs($admin)
            ->delete(route('admin.announcements.destroy', $announcement))
            ->assertRedirect(route('admin.announcements.index'));

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'announcement.deleted']);
    }

    public function test_active_announcements_are_shared_with_public_pages_in_the_current_locale(): void
    {
        $this->seed(CalendarSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('welcome')
                ->has('announcements', 2)
                ->where('announcements.0.level', 'warning')
                ->whereType('announcements.0.message', 'string'));
    }

    public function test_inactive_future_and_expired_announcements_are_hidden_from_public_pages(): void
    {
        Announcement::query()->create($this->payload());
        Announcement::query()->create($this->payload([
            'is_active' => false,
        ]));
        Announcement::query()->create($this->payload([
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(4),
        ]));
        Announcement::query()->create($this->payload([
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(5),
        ]));

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('welcome')
                ->has('announcements', 1));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace([
            'message_en' => 'The civil registry counter is closed this Friday afternoon.',
            'message_fil' => 'Sarado ang counter ng civil registry ngayong Biyernes ng hapon.',
            'level' => 'warning',
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ], $overrides);
    }
}
