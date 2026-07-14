<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    #[Test]
    public function theLoginPageCanBeRendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    #[Test]
    public function anAuthenticatedUserIsRedirectedFromLogin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    #[Test]
    public function aUserCanLogInWithValidCredentials(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    #[Test]
    public function aUserCannotLogInWithAnInvalidPassword(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function aUserCannotLogInWithANonexistentEmail(): void
    {
        $response = $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function aUserCanLogOut(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    #[Test]
    public function logoutFlashesASuccessMessage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertSessionHas('success', 'You have been logged out.');
    }

    #[Test]
    public function aGuestIsRedirectedToLogin(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
