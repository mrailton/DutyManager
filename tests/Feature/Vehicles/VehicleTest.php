<?php

declare(strict_types=1);

namespace Tests\Feature\Vehicles;

use App\Models\User;
use App\Models\Vehicle;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    #[Test]
    public function theVehiclesIndexPageCanBeRendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/vehicles');

        $response->assertOk();
    }

    #[Test]
    public function aGuestCannotViewTheVehiclesIndex(): void
    {
        $response = $this->get('/vehicles');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function theIndexDisplaysExistingVehicles(): void
    {
        $user = User::factory()->create();
        $vehicles = Vehicle::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/vehicles');

        foreach ($vehicles as $vehicle) {
            $response->assertSee($vehicle->callsign);
        }
    }

    #[Test]
    public function aVehicleCanBeCreated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vehicles', [
            'callsign' => 'DUTY-1',
            'name' => 'London Alpha',
            'role' => 'RA',
        ]);

        $response->assertRedirect('/vehicles');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('vehicles', [
            'callsign' => 'DUTY-1',
            'name' => 'London Alpha',
            'role' => 'RA',
        ]);
    }

    #[Test]
    public function aGuestCannotCreateAVehicle(): void
    {
        $response = $this->post('/vehicles', [
            'callsign' => 'DUTY-1',
            'name' => 'London Alpha',
            'role' => 'RA',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('vehicles', ['callsign' => 'DUTY-1']);
    }

    #[Test]
    public function callsignIsRequired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vehicles', [
            'callsign' => '',
            'name' => 'London Alpha',
            'role' => 'RA',
        ]);

        $response->assertSessionHasErrors('callsign');
    }

    #[Test]
    public function nameIsRequired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vehicles', [
            'callsign' => 'DUTY-1',
            'name' => '',
            'role' => 'RA',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function roleIsRequired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vehicles', [
            'callsign' => 'DUTY-1',
            'name' => 'London Alpha',
            'role' => '',
        ]);

        $response->assertSessionHasErrors('role');
    }

    #[Test]
    public function roleMustBeValid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vehicles', [
            'callsign' => 'DUTY-1',
            'name' => 'London Alpha',
            'role' => 'INVALID_ROLE',
        ]);

        $response->assertSessionHasErrors('role');
    }
}
