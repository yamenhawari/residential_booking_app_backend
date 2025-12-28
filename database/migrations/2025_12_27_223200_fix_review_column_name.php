<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Check if 'review' exists, if so, rename it to 'rating'
            if (Schema::hasColumn('reviews', 'review')) {
                $table->renameColumn('review', 'rating');
            }
            // If neither exists (rare case), create 'rating'
            elseif (!Schema::hasColumn('reviews', 'rating')) {
                $table->double('rating')->default(5.0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'rating')) {
                $table->renameColumn('rating', 'review');
            }
        });
    }
};
