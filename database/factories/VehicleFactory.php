<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VehicleRole;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'callsign' => 'DUTY-' . $this->faker->unique()->numberBetween(1, 50),
            'name' => $this->faker->city() . ' ' . $this->faker->randomElement(['Alpha', 'Bravo', 'Charlie', 'Delta']),
            'role' => $this->faker->randomElement(VehicleRole::cases()),
        ];
    }
}
