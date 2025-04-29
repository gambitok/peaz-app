<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tagData = [
            ['id' => 1, 'name' => '🍳 Breakfast'],
            ['id' => 2, 'name' => '⏱️ Quick Meal'],
            ['id' => 3, 'name' => '🌶️ Spicy food'],
            ['id' => 4, 'name' => '🥕 Healthy'],
            ['id' => 5, 'name' => '🍖 BBQ'],
            ['id' => 6, 'name' => '🥧 Baking'],
            ['id' => 7, 'name' => '🐟 Seafood'],
            ['id' => 8, 'name' => '🍏 Smoothies & juices'],
            ['id' => 9, 'name' => '🍣 Sushi & sashimi'],
            ['id' => 10, 'name' => '🌮 Tacos'],
            ['id' => 11, 'name' => '🍰 Cake'],
            ['id' => 12, 'name' => '🍪 Cookies'],
            ['id' => 13, 'name' => '🍉 Fruit'],
            ['id' => 14, 'name' => '🥧 Pie'],
            ['id' => 15, 'name' => '🍦 Ice cream'],
            ['id' => 16, 'name' => '🍷 Wine'],
            ['id' => 17, 'name' => '🍹 Cocktails'],
            ['id' => 18, 'name' => '🧂 Tequila'],
            ['id' => 19, 'name' => '🍕 Pizza'],
            ['id' => 20, 'name' => '🍔 Burgers'],
            ['id' => 21, 'name' => '🍝 Pasta'],
            ['id' => 22, 'name' => '🍮 Desserts'],
            ['id' => 23, 'name' => '🥗 Salads'],
            ['id' => 24, 'name' => '🛒 5 ingredient meals'],
            ['id' => 25, 'name' => '🍚 Fried rice'],
            ['id' => 26, 'name' => '🥢 Stir-fry'],
            ['id' => 27, 'name' => '💰 Budget-friendly'],
            ['id' => 28, 'name' => '🥳 Good for groups'],
            ['id' => 29, 'name' => '🐌 Slow cooker recipes'],
            ['id' => 30, 'name' => '🍞 Bread & pastries'],
            ['id' => 31, 'name' => '🌹 Date night'],
            ['id' => 32, 'name' => '🍜 Noodles'],
            ['id' => 33, 'name' => '🍾 Champagne'],
            ['id' => 34, 'name' => '🍸 Vodka'],
            ['id' => 35, 'name' => '🥦 Vegetables'],
            ['id' => 36, 'name' => '🍗 Meat'],
            ['id' => 37, 'name' => '🥔 Potatoes'],
            ['id' => 38, 'name' => '🌯 Sandwiches & wraps'],
            ['id' => 39, 'name' => '🥒 Low calorie meal'],
            ['id' => 40, 'name' => '🥟 Appetizers'],
            ['id' => 41, 'name' => '🍲 Soups & stews'],
            ['id' => 42, 'name' => '🍽️ Meal prep'],
            ['id' => 43, 'name' => '🍟 Comfort dishes'],
            ['id' => 44, 'name' => '🥘 One-pot meals'],
            ['id' => 45, 'name' => '🍅 Sauces & marinades'],
            ['id' => 46, 'name' => '🍛 Curry'],
            ['id' => 47, 'name' => '🏴‍☠️ Rum'],
            ['id' => 48, 'name' => '🥃 Whisky']
        ];

        DB::table('tags')->insert($tagData);
    }
}
