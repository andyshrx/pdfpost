<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_registration_closes_once_an_account_exists(): void
    {
        User::factory()->create();

        $this->get('/register')->assertForbidden();
    }

    public function test_register_action_is_blocked_once_an_account_exists(): void
    {
        User::factory()->create();

        // the abort may surface as an exception or a livewire error response
        // depending on the runner, so just assert the outcome either way
        try {
            Volt::test('auth.register')
                ->set('name', 'Intruder')
                ->set('email', 'intruder@example.com')
                ->set('password', 'password')
                ->set('password_confirmation', 'password')
                ->call('register');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertGuest();
        $this->assertSame(1, User::count());
    }

    public function test_the_operator_can_reopen_registration(): void
    {
        config(['pdfpost.allow_registration' => true]);

        User::factory()->create();

        $this->get('/register')->assertOk();

        Volt::test('auth.register')
            ->set('name', 'Second User')
            ->set('email', 'second@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors();

        $this->assertSame(2, User::count());
    }

    public function test_login_page_only_offers_signup_while_registration_is_open(): void
    {
        $this->get('/login')->assertSee('Sign up');

        User::factory()->create();

        $this->get('/login')->assertDontSee('Sign up');
    }
}
