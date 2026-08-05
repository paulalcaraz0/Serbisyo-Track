<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\ServiceRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_service_administration(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $service = Service::factory()->create();

        $this->actingAs($staff)->get(route('admin.services.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.services.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.services.edit', $service))->assertForbidden();
        $this->actingAs($staff)->put(route('admin.services.update', $service), $this->payload())->assertForbidden();
    }

    public function test_administrator_can_list_search_filter_sort_and_paginate_services(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        Service::factory()->count(11)->create();
        $target = Service::factory()->inactive()->create(['name_en' => 'Target Residency Service']);

        $this->actingAs($admin)
            ->get(route('admin.services.index', [
                'search' => 'Target Residency',
                'status' => 'inactive',
                'sort' => 'name',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/services/index')
                ->has('services.data', 1)
                ->where('services.data.0.slug', $target->slug)
                ->where('filters.status', 'inactive')
                ->where('summary.active', 11)
                ->where('summary.inactive', 1));
    }

    public function test_administrator_can_create_a_service_with_a_unique_public_slug(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($admin)->post(route('admin.services.store'), $this->payload())
            ->assertRedirect();
        $this->actingAs($admin)->post(route('admin.services.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('services', ['slug' => 'sample-community-service', 'created_by' => $admin->id]);
        $this->assertDatabaseHas('services', ['slug' => 'sample-community-service-2', 'created_by' => $admin->id]);
        $this->assertDatabaseCount('service_requirements', 2);
    }

    public function test_service_creation_requires_complete_bilingual_content(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($admin)
            ->from(route('admin.services.create'))
            ->post(route('admin.services.store'), [
                'name_en' => 'Incomplete service',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.services.create'))
            ->assertSessionHasErrors([
                'name_fil',
                'description_en',
                'description_fil',
                'eligibility_en',
                'eligibility_fil',
                'procedure_steps_en',
                'procedure_steps_fil',
                'requirements',
            ]);
    }

    public function test_administrator_can_update_content_and_replace_requirements(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $service = Service::factory()->create();
        ServiceRequirement::factory()->for($service)->create(['name_en' => 'Old requirement']);
        $payload = $this->payload([
            'name_en' => 'Updated community service',
            'requirements' => [[
                'name_en' => 'New requirement',
                'name_fil' => 'Bagong requirement',
                'details_en' => null,
                'details_fil' => null,
                'is_required' => false,
            ]],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.services.update', $service), $payload)
            ->assertRedirect(route('admin.services.edit', $service));

        $this->assertDatabaseHas('services', ['id' => $service->id, 'name_en' => 'Updated community service', 'updated_by' => $admin->id]);
        $this->assertDatabaseMissing('service_requirements', ['name_en' => 'Old requirement']);
        $this->assertDatabaseHas('service_requirements', ['service_id' => $service->id, 'name_en' => 'New requirement', 'is_required' => false]);
    }

    public function test_archiving_hides_a_service_and_restoring_keeps_it_inactive(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.services.archive', $service))
            ->assertRedirect(route('admin.services.index'));

        $service->refresh();
        $this->assertFalse($service->is_active);
        $this->assertNotNull($service->archived_at);
        $this->get(route('services.show', $service))->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.services.restore', $service))
            ->assertRedirect(route('admin.services.edit', $service));

        $service->refresh();
        $this->assertFalse($service->is_active);
        $this->assertNull($service->archived_at);
    }

    public function test_services_have_no_delete_route_and_policy_denies_deletion(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $service = Service::factory()->create();

        $this->assertFalse($admin->can('delete', $service));
        $this->actingAs($admin)->delete("/admin/services/{$service->slug}")->assertMethodNotAllowed();
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace([
            'name_en' => 'Sample Community Service',
            'name_fil' => 'Halimbawang Serbisyo ng Komunidad',
            'description_en' => 'A complete plain-language demonstration service description.',
            'description_fil' => 'Isang kumpletong paglalarawan ng demonstrasyong serbisyo.',
            'eligibility_en' => 'Residents of fictional Barangay Haraya.',
            'eligibility_fil' => 'Mga residente ng kathang-isip na Barangay Haraya.',
            'fee_centavos' => 2500,
            'processing_time_en' => '1 to 2 business days',
            'processing_time_fil' => '1 hanggang 2 araw ng trabaho',
            'target_business_days' => 2,
            'office_hours_en' => 'Monday to Friday, 8:00 AM to 5:00 PM',
            'office_hours_fil' => 'Lunes hanggang Biyernes, 8:00 AM hanggang 5:00 PM',
            'procedure_steps_en' => ['Review requirements.', 'Submit the request.'],
            'procedure_steps_fil' => ['Suriin ang requirements.', 'Isumite ang kahilingan.'],
            'appointment_required' => false,
            'contact_email' => 'help@barangayharaya.test',
            'contact_phone' => '(02) 8123 4567',
            'is_active' => true,
            'requirements' => [[
                'name_en' => 'Fictional local address',
                'name_fil' => 'Kathang-isip na lokal na tirahan',
                'details_en' => 'Do not provide a real government ID.',
                'details_fil' => 'Huwag magbigay ng tunay na government ID.',
                'is_required' => true,
            ]],
        ], $overrides);
    }
}
