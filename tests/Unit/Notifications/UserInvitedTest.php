<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\UserInvited;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserInvitedTest extends TestCase
{
    #[Test]
    public function it_sends_via_mail(): void
    {
        $notification = new UserInvited();

        $channels = $notification->via(User::factory()->make());

        $this->assertSame(['mail'], $channels);
    }

    #[Test]
    public function it_has_the_correct_subject(): void
    {
        $notification = new UserInvited();
        $user = User::factory()->make(['email' => 'test@example.com']);

        $mail = $notification->toMail($user);

        $this->assertSame('You have been invited to Duty Manager', $mail->subject);
    }

    #[Test]
    public function it_includes_the_intro_line(): void
    {
        $notification = new UserInvited();
        $user = User::factory()->make();

        $mail = $notification->toMail($user);
        $rendered = (string) $mail->render();

        $this->assertStringContainsString('An account has been created for you on Duty Manager.', $rendered);
    }

    #[Test]
    public function it_includes_the_expiry_line(): void
    {
        $notification = new UserInvited();
        $user = User::factory()->make();

        $mail = $notification->toMail($user);
        $rendered = (string) $mail->render();

        $this->assertStringContainsString('This link will expire in 60 minutes.', $rendered);
    }

    #[Test]
    public function it_includes_the_ignore_line(): void
    {
        $notification = new UserInvited();
        $user = User::factory()->make();

        $mail = $notification->toMail($user);
        $rendered = (string) $mail->render();

        $this->assertStringContainsString('If you did not expect this invitation, you can ignore this email.', $rendered);
    }

    #[Test]
    public function it_contains_a_password_reset_link_with_the_users_email(): void
    {
        $notification = new UserInvited();
        $user = User::factory()->make(['email' => 'alice@example.com']);

        $mail = $notification->toMail($user);
        $rendered = (string) $mail->render();

        $this->assertStringContainsString('alice%40example.com', $rendered);
        $this->assertStringContainsString('reset-password', $rendered);
    }

    #[Test]
    public function the_action_button_text_is_set_your_password(): void
    {
        $notification = new UserInvited();
        $user = User::factory()->make();

        $mail = $notification->toMail($user);

        $this->assertSame('Set Your Password', $mail->actionText);
    }
}
