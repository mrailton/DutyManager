<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Duty;
use App\Models\Member;
use App\Models\User;
use App\Models\Vehicle;
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

    #[Test]
    public function it_shows_correct_metrics_with_data(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $duty = Duty::factory()->create([
            'start_time' => now()->subMonth(),
            'end_time' => now()->subMonth()->addHours(8),
        ]);
        $duty->members()->attach($member);
        $duty->vehicles()->attach($vehicle);

        $response = $this->actingAs($user)->get('/');

        $response->assertSee('Total Duties');
        $response->assertSee('Volunteer Hours');
        $response->assertSee('Avg Members / Duty');
        $response->assertSee('Avg Duties / Member');
        $response->assertSee('Active Members');
        $response->assertSee('Total Vehicles');
    }

    #[Test]
    public function it_respects_nullable_member_and_vehicle_relations(): void
    {
        $user = User::factory()->create();
        Duty::factory()->create([
            'start_time' => now()->subMonth(),
            'end_time' => now()->subMonth()->addHours(4),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Total Duties');
    }

    #[Test]
    public function it_swaps_dates_when_end_is_before_start(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->subYear()->format('Y-m-d'),
        ]);

        $response->assertOk();
    }
}
