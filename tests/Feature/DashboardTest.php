<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    #[Test]
    public function dashboardRedirectsGuestToLogin(): void
    {
        $this->actingAsGuest();
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
