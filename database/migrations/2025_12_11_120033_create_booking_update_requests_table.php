<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_update_requests', function (Blueprint $table) {
            $table->id();

            // الحجز نفسه
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            // التواريخ المطلوبة الجديدة
            $table->date('requested_start_date');
            $table->date('requested_end_date');

            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_update_requests');
    }
};
