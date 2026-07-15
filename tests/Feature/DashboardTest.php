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

        $response = $this->actingAs($user)->get('/?start_date=' . now()->format('Y-m-d') . '&end_date=' . now()->subYear()->format('Y-m-d'));

        $response->assertOk();
    }

    #[Test]
    public function it_uses_default_dates_when_no_params_given(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertViewHas('startDate');
        $response->assertViewHas('endDate');
    }

    #[Test]
    public function it_calculates_busiest_month(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();
        $duty = Duty::factory()->create([
            'start_time' => now()->subMonth(),
            'end_time' => now()->subMonth()->addHours(4),
        ]);
        $duty->members()->attach($member);

        $response = $this->actingAs($user)->get('/');

        $response->assertViewHas('busiestMonth');
    }

    #[Test]
    public function it_shows_null_busiest_month_when_no_duties(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertViewHas('busiestMonth', null);
    }

    #[Test]
    public function it_shows_zero_averages_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertViewHas('totalDuties', 0);
        $response->assertViewHas('averageMembersPerDuty', 0);
        $response->assertViewHas('averageDutiesPerMember', 0);
        $response->assertViewHas('busiestVehicle', null);
    }

    #[Test]
    public function it_shows_busiest_vehicle(): void
    {
        $user = User::factory()->create();
        $vehicleA = Vehicle::factory()->create(['name' => 'Van']);
        $vehicleB = Vehicle::factory()->create(['name' => 'Truck']);
        $duty1 = Duty::factory()->create(['start_time' => now()->subMonth(), 'end_time' => now()->subMonth()->addHours(2)]);
        $duty2 = Duty::factory()->create(['start_time' => now()->subMonth(), 'end_time' => now()->subMonth()->addHours(2)]);
        $duty3 = Duty::factory()->create(['start_time' => now()->subMonth(), 'end_time' => now()->subMonth()->addHours(2)]);
        $duty1->vehicles()->attach($vehicleA);
        $duty2->vehicles()->attach($vehicleA);
        $duty3->vehicles()->attach($vehicleB);

        $response = $this->actingAs($user)->get('/');

        $response->assertViewHas('busiestVehicle');
        $this->assertEquals('Van', $response->viewData('busiestVehicle')->name);
    }

    #[Test]
    public function it_shows_busiest_members(): void
    {
        $user = User::factory()->create();
        $members = Member::factory()->count(5)->create();
        $busyMember = $members[0];
        $duties = Duty::factory()->count(3)->create([
            'start_time' => now()->subMonth(),
            'end_time' => now()->subMonth()->addHours(2),
        ]);
        foreach ($duties as $duty) {
            $duty->members()->attach($busyMember);
        }
        $duties = Duty::factory()->count(5)->create([
            'start_time' => now()->subMonth(),
            'end_time' => now()->subMonth()->addHours(2),
        ]);
        foreach ($duties as $i => $duty) {
            $duty->members()->attach($members[$i % 5]);
        }

        $response = $this->actingAs($user)->get('/');

        $response->assertViewHas('busiestMembers');
        $this->assertCount(5, $response->viewData('busiestMembers'));
        $this->assertEquals($busyMember->id, $response->viewData('busiestMembers')->first()->id);
    }
}
