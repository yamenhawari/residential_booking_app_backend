<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Fix: Rename 'review' to 'rating'
            $table->renameColumn('review', 'rating');

            // Fix: Ensure 'rating' can hold standard values (e.g. double or integer)
            // If it was TinyInteger, it's fine for 1-5 stars.
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->renameColumn('rating', 'review');
        });
    }
};
