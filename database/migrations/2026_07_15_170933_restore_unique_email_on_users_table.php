<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasIndex('users', 'users_email_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex('users_email_index');
            });
        }

        if ( ! Schema::hasIndex('users', 'users_email_unique', 'unique')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_email_unique', 'unique')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_email_unique');
            });
        }

        if ( ! Schema::hasIndex('users', 'users_email_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->index('email');
            });
        }
    }
};
