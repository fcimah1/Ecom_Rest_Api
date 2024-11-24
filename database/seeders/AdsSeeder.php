<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Ad::factory(20)->create();
    }
}
