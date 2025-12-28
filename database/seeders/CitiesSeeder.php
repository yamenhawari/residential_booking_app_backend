<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {City::insert([
        // دمشق (governorate_id = 1)
        ['name' => 'المزة', 'governorate_id' => 1],
        ['name' => 'المهاجرين', 'governorate_id' => 1],
        ['name' => 'القدم', 'governorate_id' => 1],
        ['name' => 'كفر سوسة', 'governorate_id' => 1],
        ['name' => 'مساكن برزة', 'governorate_id' => 1],
        ['name' => 'الميدان', 'governorate_id' => 1],
        ['name' => 'الزاهرة', 'governorate_id' => 1],
        ['name' => 'القابون', 'governorate_id' => 1],
        ['name' => 'الشعلان', 'governorate_id' => 1],
        ['name' => 'ركن الدين', 'governorate_id' => 1],
        ['name' => 'مشروع دمر', 'governorate_id' => 1],
        ['name' => 'الدويلعة', 'governorate_id' => 1],

        // ريف دمشق (governorate_id = 2)
        ['name' => 'دوما', 'governorate_id' => 2],
        ['name' => 'عربين', 'governorate_id' => 2],
        ['name' => 'سقبا', 'governorate_id' => 2],
        ['name' => 'حرستا', 'governorate_id' => 2],
        ['name' => 'مسرابا', 'governorate_id' => 2],
        ['name' => 'الزبداني', 'governorate_id' => 2],
        ['name' => 'قطنا', 'governorate_id' => 2],
        ['name' => 'يبرود', 'governorate_id' => 2],
        ['name' => 'المليحة', 'governorate_id' => 2],
        ['name' => 'جوبر', 'governorate_id' => 2],

        // حلب (governorate_id = 3)
        ['name' => 'حلب', 'governorate_id' => 3],
        ['name' => 'اعزاز', 'governorate_id' => 3],
        ['name' => 'عفرين', 'governorate_id' => 3],
        ['name' => 'الباب', 'governorate_id' => 3],
        ['name' => 'جرابلس', 'governorate_id' => 3],
        ['name' => 'تل رفعت', 'governorate_id' => 3],

        // حمص (governorate_id = 4)
        ['name' => 'حمص', 'governorate_id' => 4],
        ['name' => 'الرستن', 'governorate_id' => 4],
        ['name' => 'الحولة', 'governorate_id' => 4],
        ['name' => 'القصير', 'governorate_id' => 4],
        ['name' => 'تلبيسة', 'governorate_id' => 4],

        // درعا (governorate_id = 5)

        ['name' => 'نوى', 'governorate_id' => 5],
        ['name' => 'الحراك', 'governorate_id' => 5],
        ['name' => 'صيدا', 'governorate_id' => 5],

        

        // اللاذقية (governorate_id = 6)
        ['name' => 'اللاذقية', 'governorate_id' => 6],
        ['name' => 'جبلة', 'governorate_id' => 6],
        ['name' => 'جبلة الساحلية', 'governorate_id' => 6],
        ['name' => 'الركبان', 'governorate_id' => 6],

        // طرطوس (governorate_id = 7)
        ['name' => 'طرطوس', 'governorate_id' => 7],
        ['name' => 'بانياس', 'governorate_id' => 7],
        ['name' => 'الصفصافة', 'governorate_id' => 7],

        // القنيطرة (governorate_id = 8)
        ['name' => 'القنيطرة', 'governorate_id' => 8],
        ['name' => 'خان أرنبة', 'governorate_id' => 8],
        ['name' => 'جبهة', 'governorate_id' => 8],

        // دير الزور (governorate_id = 9)
        ['name' => 'دير الزور', 'governorate_id' => 9],
        ['name' => 'البوكمال', 'governorate_id' => 9],
        ['name' => 'الميادين', 'governorate_id' => 9],

        // حماة (governorate_id = 10)
        ['name' => 'حماة', 'governorate_id' => 10],
        ['name' => 'السلمية', 'governorate_id' => 10],
        ['name' => 'مخيم الرستن', 'governorate_id' => 10],
    ]);

    }
}
