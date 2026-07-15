<?php

declare(strict_types=1);

namespace Tests\Feature\Duties;

use App\Models\Member;
use App\Models\User;
use App\Models\Vehicle;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DutyTest extends TestCase
{
    #[Test]
    public function theDutiesIndexPageCanBeRendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/duties');

        $response->assertOk();
    }

    #[Test]
    public function aGuestCannotViewTheDutiesIndex(): void
    {
        $response = $this->get('/duties');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function theIndexDisplaysExistingDuties(): void
    {
        $user = User::factory()->create();
        $duty = \App\Models\Duty::factory()->create(['name' => 'Night Shift']);

        $response = $this->actingAs($user)->get('/duties');

        $response->assertSee('Night Shift');
    }

    #[Test]
    public function aDutyCanBeCreated(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Morning Duty',
            'organiser' => 'John Smith',
            'start_time' => '2026-07-20T08:00',
            'end_time' => '2026-07-20T16:00',
            'covered' => '1',
            'notes' => 'Some notes.',
            'member_ids' => [$member->id],
            'vehicle_ids' => [$vehicle->id],
        ]);

        $response->assertRedirect('/duties');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('duties', [
            'name' => 'Morning Duty',
            'organiser' => 'John Smith',
        ]);

        $duty = \App\Models\Duty::where('name', 'Morning Duty')->first();
        $this->assertTrue($duty->members->contains($member));
        $this->assertTrue($duty->vehicles->contains($vehicle));
    }

    #[Test]
    public function aDutyCanBeCreatedWithoutMembersOrVehicles(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Solo Duty',
            'organiser' => 'Jane Doe',
            'start_time' => '2026-07-20T08:00',
            'end_time' => '2026-07-20T16:00',
        ]);

        $response->assertRedirect('/duties');
        $this->assertDatabaseHas('duties', ['name' => 'Solo Duty']);
    }

    #[Test]
    public function aGuestCannotCreateADuty(): void
    {
        $response = $this->post('/duties', [
            'name' => 'Morning Duty',
            'organiser' => 'John Smith',
            'start_time' => '2026-07-20T08:00',
            'end_time' => '2026-07-20T16:00',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function endTimeMustBeAfterStartTime(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Bad Duty',
            'organiser' => 'John Smith',
            'start_time' => '2026-07-20T16:00',
            'end_time' => '2026-07-20T08:00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    #[Test]
    public function nameIsRequired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => '',
            'organiser' => 'John Smith',
            'start_time' => '2026-07-20T08:00',
            'end_time' => '2026-07-20T16:00',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function organiserIsRequired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Test Duty',
            'organiser' => '',
            'start_time' => '2026-07-20T08:00',
            'end_time' => '2026-07-20T16:00',
        ]);

        $response->assertSessionHasErrors('organiser');
    }

    #[Test]
    public function theShowPageDisplaysDutyDetails(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $duty = \App\Models\Duty::factory()->create(['name' => 'Event Duty']);
        $duty->members()->attach($member);
        $duty->vehicles()->attach($vehicle);

        $response = $this->actingAs($user)->get('/duties/' . $duty->id);

        $response->assertOk();
        $response->assertSee('Event Duty');
        $response->assertSee($member->name);
        $response->assertSee($vehicle->callsign);
    }

    #[Test]
    public function aGuestCannotViewADuty(): void
    {
        $duty = \App\Models\Duty::factory()->create();

        $response = $this->get('/duties/' . $duty->id);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function aDutyCanBeUpdated(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $duty = \App\Models\Duty::factory()->create(['name' => 'Original Name']);
        $duty->members()->attach($member);
        $duty->vehicles()->attach($vehicle);

        $newMember = Member::factory()->create();
        $newVehicle = Vehicle::factory()->create();

        $response = $this->actingAs($user)->put('/duties/' . $duty->id, [
            'name' => 'Updated Name',
            'organiser' => 'Updated Organiser',
            'start_time' => '2026-07-21T08:00',
            'end_time' => '2026-07-21T16:00',
            'covered' => '0',
            'notes' => 'Updated notes.',
            'member_ids' => [$newMember->id],
            'vehicle_ids' => [$newVehicle->id],
        ]);

        $response->assertRedirect('/duties/' . $duty->id);
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('duties', [
            'id' => $duty->id,
            'name' => 'Updated Name',
            'organiser' => 'Updated Organiser',
        ]);

        $duty->refresh();
        $this->assertFalse($duty->members->contains($member));
        $this->assertTrue($duty->members->contains($newMember));
        $this->assertFalse($duty->vehicles->contains($vehicle));
        $this->assertTrue($duty->vehicles->contains($newVehicle));
    }

    #[Test]
    public function aGuestCannotUpdateADuty(): void
    {
        $duty = \App\Models\Duty::factory()->create();

        $response = $this->put('/duties/' . $duty->id, [
            'name' => 'Hacked Name',
            'organiser' => 'Hacker',
            'start_time' => '2026-07-21T08:00',
            'end_time' => '2026-07-21T16:00',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function aDutyCanBeCreatedWithSplitDateAndTime(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Split Time Duty',
            'organiser' => 'Test User',
            'start_date' => '2026-08-01',
            'start_hour' => '09',
            'start_minute' => '00',
            'end_date' => '2026-08-01',
            'end_hour' => '17',
            'end_minute' => '30',
            'covered' => '1',
        ]);

        $response->assertRedirect('/duties');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('duties', ['name' => 'Split Time Duty']);

        $duty = \App\Models\Duty::where('name', 'Split Time Duty')->first();
        $this->assertEquals('2026-08-01 09:00:00', $duty->start_time->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-08-01 17:30:00', $duty->end_time->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function aDutyCanBeUpdatedWithSplitDateAndTime(): void
    {
        $user = User::factory()->create();
        $duty = \App\Models\Duty::factory()->create(['name' => 'Before Update']);

        $response = $this->actingAs($user)->put('/duties/' . $duty->id, [
            'name' => 'After Update',
            'organiser' => 'Updated User',
            'start_date' => '2026-09-01',
            'start_hour' => '07',
            'start_minute' => '15',
            'end_date' => '2026-09-01',
            'end_hour' => '15',
            'end_minute' => '45',
        ]);

        $response->assertRedirect('/duties/' . $duty->id);
        $duty->refresh();
        $this->assertEquals('2026-09-01 07:15:00', $duty->start_time->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-09-01 15:45:00', $duty->end_time->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function splitDateWithInvalidHourFails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Bad Hour',
            'organiser' => 'Test User',
            'start_date' => '2026-08-01',
            'start_hour' => '99',
            'start_minute' => '00',
            'end_date' => '2026-08-01',
            'end_hour' => '17',
            'end_minute' => '00',
        ]);

        $response->assertSessionHasErrors('start_time');
    }

    #[Test]
    public function splitDateWithInvalidMinuteFails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Bad Minute',
            'organiser' => 'Test User',
            'start_date' => '2026-08-01',
            'start_hour' => '10',
            'start_minute' => '99',
            'end_date' => '2026-08-01',
            'end_hour' => '17',
            'end_minute' => '00',
        ]);

        $response->assertSessionHasErrors('start_time');
    }

    #[Test]
    public function splitEndTimeMustBeAfterStartTime(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/duties', [
            'name' => 'Bad Range',
            'organiser' => 'Test User',
            'start_date' => '2026-08-01',
            'start_hour' => '14',
            'start_minute' => '00',
            'end_date' => '2026-08-01',
            'end_hour' => '09',
            'end_minute' => '00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }
}
