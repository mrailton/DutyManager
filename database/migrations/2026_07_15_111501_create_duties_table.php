<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('duties', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('organiser');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('covered')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duties');
    }
};
