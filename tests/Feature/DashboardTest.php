<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    #[Test]
    public function dashboard_redirects_guest_to_login(): void
    {
        $this->actingAsGuest();
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function dashboard_loads_for_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('dashboard');
    }
}
