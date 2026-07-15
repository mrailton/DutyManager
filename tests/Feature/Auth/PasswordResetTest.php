<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    #[Test]
    public function the_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee('Forgot password');
    }

    #[Test]
    public function an_authenticated_user_cannot_access_the_forgot_password_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/forgot-password');

        $response->assertRedirect('/');
    }

    #[Test]
    public function a_reset_link_can_be_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function a_reset_link_is_not_sent_for_unknown_email(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function the_reset_password_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->get('/reset-password/' . $token);

        $response->assertOk();
        $response->assertSee('Reset password');
    }

    #[Test]
    public function an_authenticated_user_cannot_access_the_reset_password_page(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->actingAs($user)->get('/reset-password/' . $token);

        $response->assertRedirect('/');
    }

    #[Test]
    public function a_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');
    }

    #[Test]
    public function a_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function password_is_required_for_reset(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function password_must_be_confirmed_for_reset(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
