<?php

declare(strict_types=1);

namespace Tests\Feature\Vehicles;

use App\Models\Duty;
use App\Models\User;
use App\Models\Vehicle;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    #[Test]
    public function the_vehicles_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/vehicles');

        $response->assertOk();
    }

    #[Test]
    public function a_guest_cannot_view_the_vehicles_index(): void
    {
        $response = $this->get('/vehicles');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function the_index_displays_existing_vehicles(): void
    {
        $user = User::factory()->create();
        $vehicles = Vehicle::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/vehicles');

        foreach ($vehicles as $vehicle) {
            $response->assertSee($vehicle->callsign);
        }
    }

    #[Test]
    public function a_vehicle_can_be_created(): void
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
    public function a_guest_cannot_create_a_vehicle(): void
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
    public function callsign_is_required(): void
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
    public function name_is_required(): void
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
    public function role_is_required(): void
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
    public function role_must_be_valid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/vehicles', [
            'callsign' => 'DUTY-1',
            'name' => 'London Alpha',
            'role' => 'INVALID_ROLE',
        ]);

        $response->assertSessionHasErrors('role');
    }

    #[Test]
    public function a_vehicle_can_be_updated(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['callsign' => 'DUTY-1', 'name' => 'Original Name', 'role' => 'RA']);

        $response = $this->actingAs($user)->put('/vehicles/' . $vehicle->id, [
            'callsign' => 'DUTY-2',
            'name' => 'Updated Name',
            'role' => 'JEEP',
        ]);

        $response->assertRedirect('/vehicles');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'callsign' => 'DUTY-2',
            'name' => 'Updated Name',
            'role' => 'JEEP',
        ]);
    }

    #[Test]
    public function a_guest_cannot_update_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->put('/vehicles/' . $vehicle->id, [
            'callsign' => 'HACKED-1',
            'name' => 'Hacked Name',
            'role' => 'RA',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function the_show_page_displays_vehicle_details(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['callsign' => 'DUTY-5', 'name' => 'London Echo']);
        $duty = Duty::factory()->create(['name' => 'Night Patrol']);
        $vehicle->duties()->attach($duty);

        $response = $this->actingAs($user)->get('/vehicles/' . $vehicle->id);

        $response->assertOk();
        $response->assertSee('DUTY-5');
        $response->assertSee('London Echo');
        $response->assertSee('Night Patrol');
    }

    #[Test]
    public function a_guest_cannot_view_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->get('/vehicles/' . $vehicle->id);

        $response->assertRedirect('/login');
    }
}
