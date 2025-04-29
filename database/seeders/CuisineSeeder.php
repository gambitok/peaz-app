<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuisineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cuisineData = [
            ['id' => 1, 'name' => '🍕 Italian'],
            ['id' => 2, 'name' => '🌮 Mexican'],
            ['id' => 3, 'name' => '🇹🇭 Thai'],
            ['id' => 4, 'name' => '🇬🇷 Greek'],
            ['id' => 5, 'name' => '🇬🇧 British'],
            ['id' => 6, 'name' => '🇺🇸 American'],
            ['id' => 7, 'name' => '🍜 Southeast Asian'],
            ['id' => 8, 'name' => '🇸🇪 Nordic'],
            ['id' => 9, 'name' => '🥐 French'],
            ['id' => 10, 'name' => '🇮🇳 Indian'],
            ['id' => 11, 'name' => '🇪🇸 Spanish'],
            ['id' => 12, 'name' => '🇨🇳 Chinese'],
            ['id' => 13, 'name' => '🇧🇷 South American'],
            ['id' => 14, 'name' => '🏝️ Caribbean'],
            ['id' => 15, 'name' => '🇺🇦 East European'],
            ['id' => 16, 'name' => '🇯🇵 Japanese'],
            ['id' => 17, 'name' => '🧆 Middle Eastern'],
            ['id' => 18, 'name' => '🇰🇷 Korean'],
            ['id' => 19, 'name' => '🥘 African'],
            ['id' => 20, 'name' => '🇵🇹 Portuguese'],
            ['id' => 21, 'name' => '🇦🇺 Australasia'],
            ['id' => 22, 'name' => '🪄 Fusion']
        ];

        DB::table('cuisines')->insert($cuisineData);
    }
}
