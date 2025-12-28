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
       Schema::create('apartments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
       $table->foreignId('governorate_id')->constrained('governorates')->onDelete('cascade');
    $table->foreignId('city_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('address');
    $table->integer('room_count');
    $table->decimal('price_per_month', 10, 2);
    $table->date('available_from')->nullable();
    $table->date('available_to')->nullable();
    $table->enum('status', ['available', 'rented', 'unavailable'])->default('available');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
