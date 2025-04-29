<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $tagIds = DB::table('tags')->pluck('id')->toArray();
        $cuisineIds = DB::table('cuisines')->pluck('id')->toArray();
        $dietaryIds = DB::table('dietaries')->pluck('id')->toArray();

        $titles = [
            'Spaghetti Carbonara', 'Chicken Alfredo', 'Beef Stroganoff',
            'Vegetable Stir Fry', 'Fish Tacos', 'Shrimp Scampi',
            'Lamb Chops', 'Eggplant Parmesan', 'Chicken Tikka Masala',
            'Beef Wellington'
        ];
        $captions = [
            'A classic Italian pasta dish made with eggs, cheese, pancetta, and pepper.',
            'A creamy pasta dish made with chicken, cream, and cheese.',
            'A Russian dish of sautéed pieces of beef served in a sauce with smetana (sour cream).',
            'A quick and healthy stir fry made with a variety of vegetables.',
            'Delicious tacos made with fresh fish, topped with a zesty slaw.',
            'A classic Italian-American dish of shrimp sautéed in garlic, butter, and white wine.',
            'Juicy lamb chops seasoned with fresh herbs and grilled to perfection.',
            'A vegetarian Italian dish made with layers of fried eggplant, cheese, and tomato sauce.',
            'A popular Indian dish made with marinated chicken cooked in a spiced curry sauce.',
            'A classic English dish made with beef fillet coated with pâté and duxelles, wrapped in puff pastry.'
        ];

        for ($i = 0; $i < 10; $i++) {
            $postId = DB::table('posts')->insertGetId([
                'user_id' => $userIds[array_rand($userIds)],
                'title' => $titles[$i],
                'caption' => $captions[$i],
                'serving_size' => rand(1, 10),
                'hours' => rand(0, 4),
                'minutes' => rand(0, 59),
                'verified' => rand(0, 1),
                'status' => rand(0, 1),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::table('post_tag')->insert([
                'post_id' => $postId,
                'tag_id' => $tagIds[array_rand($tagIds)]
            ]);

            DB::table('post_cuisine')->insert([
                'post_id' => $postId,
                'cuisine_id' => $cuisineIds[array_rand($cuisineIds)]
            ]);

            DB::table('post_dietary')->insert([
                'post_id' => $postId,
                'dietary_id' => $dietaryIds[array_rand($dietaryIds)]
            ]);
        }
    }
}
