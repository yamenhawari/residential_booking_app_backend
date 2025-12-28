<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Governorate;

class GovernoratesSeeder extends Seeder
{
    public function run(): void
    {
        Governorate::insert([
            ['name' => 'دمشق'],
            ['name' => 'حلب'],
            ['name' => 'حمص'],
             ['name' => 'ريف دمشق'],
              ['name' => 'درعا'],
               ['name' => 'اللاذقية'],
                ['name' => 'طرطوس '],
                 ['name' => 'القنيطرة'],
                  ['name' => 'دير الزور'],
                   ['name' => 'حماة']

        ]);
    }
}
