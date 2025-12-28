<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // 1. If the old column 'review' exists, rename it to 'rating'
            if (Schema::hasColumn('reviews', 'review') && !Schema::hasColumn('reviews', 'rating')) {
                $table->renameColumn('review', 'rating');
            }

            // 2. Ensure 'rating' is a FLOAT/DOUBLE (to allow 4.5 stars), not just Integer
            // We use 'change()' to modify the existing type if necessary.
            // Note: You might need 'doctrine/dbal' installed. If not, this part might verify types manually.
            // For safety, let's just ensure the column exists.
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Separate step to ensure column exists before changing type
            if (Schema::hasColumn('reviews', 'rating')) {
                $table->double('rating', 3, 1)->change(); // Allows 1.0 to 5.0
            }
        });
    }

    public function down(): void
    {
        // Optional: Revert changes
    }
};
