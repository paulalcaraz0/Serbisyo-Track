<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FoundationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_localized_content_and_a_visible_disclaimer(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('welcome')
                ->where('locale', 'en')
                ->where('auth.user', null)
                ->where('translations.home.disclaimer_label', 'Demonstration only')
                ->where('supportedLocales.fil', 'Filipino'));
    }

    public function test_locale_can_be_changed_and_invalid_values_are_rejected(): void
    {
        $this->from('/')
            ->post('/locale', ['locale' => 'fil'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'fil');

        $this->withSession(['locale' => 'fil'])
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'fil')
                ->where('translations.home.disclaimer_label', 'Demonstration lamang'));

        $this->from('/')
            ->post('/locale', ['locale' => 'invalid'])
            ->assertSessionHasErrors('locale')
            ->assertRedirect('/');
    }

    public function test_security_headers_are_applied_to_web_responses(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Content-Security-Policy', "base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'");
    }

    public function test_authenticated_shared_user_data_is_allow_listed(): void
    {
        $user = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.id', $user->id)
                ->where('auth.user.role', UserRole::Administrator->value)
                ->missing('auth.user.is_active')
                ->missing('auth.user.last_login_at')
                ->missing('auth.user.password'));
    }
}
