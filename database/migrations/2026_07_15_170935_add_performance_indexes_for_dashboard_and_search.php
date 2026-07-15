<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('duties', function (Blueprint $table): void {
            $table->index('start_time');
            $table->index('name');
            $table->index('organiser');
        });

        Schema::table('members', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->index('callsign');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('duties', function (Blueprint $table): void {
            $table->dropIndex('duties_start_time_index');
            $table->dropIndex('duties_name_index');
            $table->dropIndex('duties_organiser_index');
        });

        Schema::table('members', function (Blueprint $table): void {
            $table->dropIndex('members_name_index');
        });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropIndex('vehicles_callsign_index');
            $table->dropIndex('vehicles_name_index');
        });
    }
};
