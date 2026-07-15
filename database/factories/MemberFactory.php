<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClinicalLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'clinical_level' => $this->faker->randomElement(ClinicalLevel::cases()),
            'driver' => $this->faker->boolean(),
        ];
    }
}
