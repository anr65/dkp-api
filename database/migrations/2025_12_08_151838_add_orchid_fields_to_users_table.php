<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->after('email_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email', 'password', 'email_verified_at', 'remember_token', 'permissions']);
        });
    }
};
