<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_to_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sub_dur_id')->constrained('subscription_durations')->onDelete('cascade');
            $table->foreignId('sub_id')->constrained('subscriptions')->onDelete('cascade');
            $table->timestamp('valid_thru');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_to_user');
    }
};