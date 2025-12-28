<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedTinyInteger('review'); // من 1 إلى 5
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
