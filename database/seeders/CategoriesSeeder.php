<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'شقة مفروشة',
            'استوديو',
            'دوبلكس',
            'شقة فاخرة',
            'شقة عائلية',
            'شقة اقتصادية',
            'شقة مع حديقة',
            'شقة بجانب البحر',
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
