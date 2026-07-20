<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Duty;
use App\Models\Member;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
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
        $response->assertSee('Uncovered Upcoming Duties (30 Days)');
        $response->assertSee('Assigned Hours by Clinical Level');
        $response->assertSee('Duty Duration Insights');
        $response->assertSee('Period-over-Period Change');
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
        $busiestMonthDate = now()->subMonths(2)->startOfMonth()->addDay();
        $quieterMonthDate = now()->subMonth()->startOfMonth()->addDay();

        Duty::factory()->count(3)->create([
            'start_time' => $busiestMonthDate->copy()->addHours(8),
            'end_time' => $busiestMonthDate->copy()->addHours(12),
        ]);

        Duty::factory()->count(1)->create([
            'start_time' => $quieterMonthDate->copy()->addHours(8),
            'end_time' => $quieterMonthDate->copy()->addHours(12),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertViewHas('busiestMonth');
        $this->assertSame(
            $busiestMonthDate->format('Y-m'),
            $response->viewData('busiestMonth')->format('Y-m')
        );
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
        $response->assertViewHas('uncoveredUpcomingDuties', 0);
        $response->assertViewHas('upcomingUncoveredDuties', fn ($duties) => $duties->isEmpty());
        $response->assertViewHas('assignedHoursByClinicalLevel', []);
        $response->assertViewHas('durationInsights', fn (array $insights) => 0.0 === $insights['average_hours']);
        $response->assertViewHas('periodChanges');
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
        $this->assertEquals('8 hours', $response->viewData('busiestMembers')->first()->assigned_hours);
    }

    #[Test]
    public function volunteer_hours_and_busiest_members_only_use_completed_duties(): void
    {
        $this->travelTo(Carbon::parse('2026-07-15 12:00:00'));

        $user = User::factory()->create();
        $memberCompleted = Member::factory()->create(['clinical_level' => 'EMT']);
        $memberFuture = Member::factory()->create(['clinical_level' => 'CFR']);

        $completedDutyOne = Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-10 08:00:00'),
            'end_time' => Carbon::parse('2026-07-10 10:00:00'),
        ]);
        $completedDutyTwo = Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-11 08:00:00'),
            'end_time' => Carbon::parse('2026-07-11 09:00:00'),
        ]);
        $futureDutyOne = Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-20 08:00:00'),
            'end_time' => Carbon::parse('2026-07-20 12:00:00'),
        ]);
        $futureDutyTwo = Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-21 08:00:00'),
            'end_time' => Carbon::parse('2026-07-21 12:00:00'),
        ]);
        $futureDutyThree = Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-22 08:00:00'),
            'end_time' => Carbon::parse('2026-07-22 12:00:00'),
        ]);

        $completedDutyOne->members()->attach($memberCompleted->id);
        $completedDutyTwo->members()->attach($memberCompleted->id);
        $futureDutyOne->members()->attach($memberFuture->id);
        $futureDutyTwo->members()->attach($memberFuture->id);
        $futureDutyThree->members()->attach($memberFuture->id);

        $response = $this->actingAs($user)->get('/?start_date=2026-07-01&end_date=2026-07-31');

        $response->assertViewHas('totalVolunteerHours', 3);
        $response->assertViewHas('assignedHoursByClinicalLevel', fn (array $rows): bool => 1 === count($rows)
                && 'EMT' === $rows[0]['level']
                && 3 === $rows[0]['hours']);
        $response->assertViewHas('durationInsights', fn (array $insights): bool => 1.5 === $insights['average_hours']
                && '1h 30m' === $insights['average_label']
                && '2h 0m' === $insights['longest']['duration_label']
                && '1h 0m' === $insights['shortest']['duration_label']);
        $response->assertViewHas('busiestMembers');
        $this->assertEquals($memberCompleted->id, $response->viewData('busiestMembers')->first()->id);
        $this->assertEquals(2, $response->viewData('busiestMembers')->first()->duties_count);
        $this->assertEquals('3 hours', $response->viewData('busiestMembers')->first()->assigned_hours);

        $this->travelBack();
    }

    #[Test]
    public function it_segments_total_duties_into_completed_and_upcoming(): void
    {
        $this->travelTo(Carbon::parse('2026-07-15 12:00:00'));

        $user = User::factory()->create();

        Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-10 08:00:00'),
            'end_time' => Carbon::parse('2026-07-10 10:00:00'),
        ]);

        Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-16 08:00:00'),
            'end_time' => Carbon::parse('2026-07-16 10:00:00'),
        ]);

        Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-15 10:00:00'),
            'end_time' => Carbon::parse('2026-07-15 14:00:00'),
        ]);

        $response = $this->actingAs($user)->get('/?start_date=2026-07-01&end_date=2026-07-31');

        $response->assertViewHas('totalDuties', 3);
        $response->assertViewHas('completedDuties', 1);
        $response->assertViewHas('upcomingDuties', 2);
        $response->assertSee('Completed: 1');
        $response->assertSee('Upcoming: 2');

        $this->travelBack();
    }

    #[Test]
    public function it_calculates_requested_new_dashboard_metrics(): void
    {
        $this->travelTo(Carbon::parse('2026-07-15 12:00:00'));

        $user = User::factory()->create();
        $members = Member::factory()->count(10)->create(['clinical_level' => 'EMT']);
        $members[1]->update(['clinical_level' => 'CFR']);
        $vehicleOne = Vehicle::factory()->create();
        $vehicleTwo = Vehicle::factory()->create();

        $dutyA = Duty::factory()->create([
            'name' => 'Duty A',
            'start_time' => Carbon::parse('2026-07-10 08:00:00'),
            'end_time' => Carbon::parse('2026-07-10 12:00:00'),
            'covered' => true,
        ]);
        $dutyB = Duty::factory()->create([
            'name' => 'Duty B',
            'start_time' => Carbon::parse('2026-07-11 08:00:00'),
            'end_time' => Carbon::parse('2026-07-11 10:00:00'),
            'covered' => false,
        ]);
        $dutyC = Duty::factory()->create([
            'name' => 'Duty C',
            'start_time' => Carbon::parse('2026-07-12 10:00:00'),
            'end_time' => Carbon::parse('2026-07-12 14:00:00'),
            'covered' => true,
        ]);
        $dutyD = Duty::factory()->create([
            'name' => 'Duty D',
            'start_time' => Carbon::parse('2026-07-12 12:00:00'),
            'end_time' => Carbon::parse('2026-07-12 16:00:00'),
            'covered' => false,
        ]);

        $previousDuty = Duty::factory()->create([
            'name' => 'Previous Duty',
            'start_time' => Carbon::parse('2026-06-20 08:00:00'),
            'end_time' => Carbon::parse('2026-06-20 10:00:00'),
            'covered' => true,
        ]);

        $dutyA->members()->attach([$members[0]->id, $members[1]->id]);
        $dutyB->members()->attach([$members[0]->id]);
        $dutyC->members()->attach([$members[0]->id]);
        $dutyD->members()->attach([$members[0]->id]);
        $previousDuty->members()->attach([$members[1]->id]);

        $dutyA->vehicles()->attach([$vehicleTwo->id]);
        $dutyC->vehicles()->attach([$vehicleOne->id]);
        $dutyD->vehicles()->attach([$vehicleOne->id]);

        Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-16 09:00:00'),
            'end_time' => Carbon::parse('2026-07-16 11:00:00'),
            'covered' => false,
        ]);
        Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-18 09:00:00'),
            'end_time' => Carbon::parse('2026-07-18 11:00:00'),
            'covered' => false,
        ]);
        Duty::factory()->create([
            'start_time' => Carbon::parse('2026-07-19 09:00:00'),
            'end_time' => Carbon::parse('2026-07-19 11:00:00'),
            'covered' => true,
        ]);

        $response = $this->actingAs($user)->get('/?start_date=2026-07-01&end_date=2026-07-15');

        $response->assertViewHas('uncoveredUpcomingDuties', 2);
        $response->assertViewHas('upcomingUncoveredDuties', fn ($duties): bool => 2 === $duties->count());
        $response->assertSee('Uncovered Upcoming Duties (30 Days)');
        $response->assertSee(route('duties.show', Duty::where('covered', false)->whereDate('start_time', '2026-07-16')->firstOrFail()));
        $response->assertSee(route('duties.show', Duty::where('covered', false)->whereDate('start_time', '2026-07-18')->firstOrFail()));
        $response->assertViewHas('assignedHoursByClinicalLevel', fn (array $rows): bool => 'EMT' === $rows[0]['level']
                && 14 === $rows[0]['hours']
                && 'CFR' === $rows[1]['level']
                && 4 === $rows[1]['hours']);
        $response->assertViewHas('durationInsights', fn (array $insights): bool => 3.5 === $insights['average_hours']
                && 4.0 === $insights['longest']['hours']
                && 2.0 === $insights['shortest']['hours']);
        $response->assertViewHas('periodChanges', fn (array $changes): bool => 300.0 === $changes['duties']
                && 800.0 === $changes['volunteer_hours']
                && 0.0 === $changes['average_members_per_duty']);

        $this->travelBack();
    }
}
