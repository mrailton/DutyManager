<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_email_unique');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_email_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('email');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('last_login_at');
            $table->dropSoftDeletes();
        });
    }
};
