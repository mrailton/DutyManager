<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('duties', function (Blueprint $table): void {
            $table->integer('invoice_amount')->nullable();
            $table->date('invoice_sent')->nullable();
            $table->date('invoice_paid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('duties', function (Blueprint $table): void {
            $table->dropColumn('invoice_amount');
            $table->dropColumn('invoice_sent');
            $table->dropColumn('invoice_paid');
        });
    }
};
