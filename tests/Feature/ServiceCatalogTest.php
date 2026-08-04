<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequirement;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_exposes_only_active_non_archived_services_without_database_ids(): void
    {
        $visible = Service::factory()->create(['name_en' => 'Visible Service']);
        ServiceRequirement::factory()->for($visible)->create();
        Service::factory()->inactive()->create(['name_en' => 'Inactive Service']);
        Service::factory()->archived()->create(['name_en' => 'Archived Service']);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('services/index')
                ->has('services.data', 1)
                ->where('services.data.0.slug', $visible->slug)
                ->where('services.data.0.name', 'Visible Service')
                ->missing('services.data.0.id')
                ->missing('services.data.0.created_by')
                ->missing('services.data.0.requirements'));
    }

    public function test_service_details_include_all_resident_guidance_and_redact_admin_data(): void
    {
        $service = Service::factory()->create();
        ServiceRequirement::factory()->for($service)->create([
            'name_en' => 'Proof of fictional address',
            'details_en' => 'Do not upload a real ID.',
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('services/show')
                ->where('service.data.slug', $service->slug)
                ->where('service.data.eligibility', $service->eligibility_en)
                ->where('service.data.fee_centavos', $service->fee_centavos)
                ->where('service.data.processing_time', $service->processing_time_en)
                ->where('service.data.office_hours', $service->office_hours_en)
                ->has('service.data.procedure_steps', 2)
                ->has('service.data.requirements', 1)
                ->where('service.data.requirements.0.name', 'Proof of fictional address')
                ->missing('service.data.id')
                ->missing('service.data.is_active')
                ->missing('service.data.archived_at'));
    }

    public function test_inactive_and_archived_service_details_return_not_found(): void
    {
        $inactive = Service::factory()->inactive()->create();
        $archived = Service::factory()->archived()->create();

        $this->get(route('services.show', $inactive))->assertNotFound();
        $this->get(route('services.show', $archived))->assertNotFound();
    }

    public function test_service_content_switches_to_filipino_without_changing_the_url(): void
    {
        $service = Service::factory()->create([
            'name_en' => 'Certificate of Residency',
            'name_fil' => 'Katibayan ng Paninirahan',
        ]);
        ServiceRequirement::factory()->for($service)->create([
            'name_en' => 'Local address',
            'name_fil' => 'Lokal na tirahan',
        ]);

        $this->withSession(['locale' => 'fil'])
            ->get(route('services.show', $service))
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'fil')
                ->where('service.data.name', 'Katibayan ng Paninirahan')
                ->where('service.data.requirements.0.name', 'Lokal na tirahan'));
    }

    public function test_locale_redirect_accepts_only_internal_application_paths(): void
    {
        $this->from(route('services.index'))
            ->post(route('locale.update'), ['locale' => 'fil', 'redirect_to' => '/services'])
            ->assertRedirect('/services');

        $this->from(route('services.index'))
            ->post(route('locale.update'), ['locale' => 'en', 'redirect_to' => 'https://example.com'])
            ->assertSessionHasErrors('redirect_to')
            ->assertRedirect(route('services.index'));
    }

    public function test_privacy_accessibility_and_help_pages_are_available_in_both_languages(): void
    {
        foreach (['privacy', 'accessibility', 'help'] as $pageKey) {
            $this->withSession(['locale' => 'en'])
                ->get(route($pageKey))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('public/info')
                    ->where('pageKey', $pageKey)
                    ->has("translations.info.{$pageKey}.sections"));

            $this->withSession(['locale' => 'fil'])
                ->get(route($pageKey))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->where('locale', 'fil')
                    ->has("translations.info.{$pageKey}.sections"));
        }
    }

    public function test_service_seeder_creates_six_complete_fictional_services(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->assertDatabaseCount('services', 6);
        $this->assertSame(6, Service::query()->publiclyVisible()->count());
        $this->assertSame(12, ServiceRequirement::query()->count());
    }
}
