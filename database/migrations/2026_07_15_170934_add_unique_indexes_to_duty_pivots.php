<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('duty_members', function (Blueprint $table): void {
            $table->unique(['member_id', 'duty_id']);
        });

        Schema::table('duty_vehicles', function (Blueprint $table): void {
            $table->unique(['vehicle_id', 'duty_id']);
        });
    }

    public function down(): void
    {
        Schema::table('duty_members', function (Blueprint $table): void {
            $table->dropUnique('duty_members_member_id_duty_id_unique');
        });

        Schema::table('duty_vehicles', function (Blueprint $table): void {
            $table->dropUnique('duty_vehicles_vehicle_id_duty_id_unique');
        });
    }
};
