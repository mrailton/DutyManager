<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Member::factory()->count(25)->create();

        Vehicle::factory()->count(2)->create();
    }
}
