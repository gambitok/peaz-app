<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DietarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dietaryData = [
            ['id' => 1, 'name' => '🌽 Vegetarian'],
            ['id' => 2, 'name' => '🌱 Vegan'],
            ['id' => 3, 'name' => '🐟 Pescatarian'],
            ['id' => 4, 'name' => '🥖 Low-carb'],
            ['id' => 5, 'name' => '💪 High-protein'],
            ['id' => 6, 'name' => '🦐 Shellfish-free'],
            ['id' => 7, 'name' => '📈 Diabetes-friendly'],
            ['id' => 8, 'name' => '🥛 Dairy-free'],
            ['id' => 9, 'name' => '🌾 Gluten-free'],
            ['id' => 10, 'name' => '🏃 Weight loss'],
            ['id' => 11, 'name' => '🍷 Alcohol-free'],
            ['id' => 12, 'name' => '🥜 Nut-free'],
            ['id' => 13, 'name' => '🍢 Soy-free'],
            ['id' => 14, 'name' => '🥚 Egg-free'],
            ['id' => 15, 'name' => '🍔 Sesame-free']
        ];

        DB::table('dietaries')->insert($dietaryData);
    }
}
