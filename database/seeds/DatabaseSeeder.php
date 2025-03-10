<?php

use Database\Seeders\PostsTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(General_Setting_Seeder::class);
        $this->call(Users_Seeder::class);
        $this->call(ContentSeeder::class);
        $this->call(InterestedListSeeder::class);
        $this->call(ReportSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(TagSeeder::class);
        $this->call(DietarySeeder::class);
        $this->call(CuisineSeeder::class);
        $this->call(ReportSeeder::class);
        $this->call(PostsTableSeeder::class);
    }
}
