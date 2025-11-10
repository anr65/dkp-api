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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['generated', 'draft'])->default('draft');
            $table->date('date');
            $table->string('city');
            $table->foreignId('seller_id')->constrained('people')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('people')->onDelete('cascade');
            $table->decimal('price', 12, 2);
            $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
