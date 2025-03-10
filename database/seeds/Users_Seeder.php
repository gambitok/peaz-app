<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Users_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = ['active', 'inactive'];
        $membership_levels = ['individual', 'business'];

        DB::table('users')->insert([
            [
                'name' => 'admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('admin'),
                'status' => 'active',
                'membership_level' => 'admin',
                'verified' => 1,
                'type' => 'admin',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'John Doe',
                'username' => 'johndoe',
                'email' => 'johndoe@example.com',
                'password' => bcrypt('password1'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Jane Smith',
                'username' => 'janesmith',
                'email' => 'janesmith@example.com',
                'password' => bcrypt('password2'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Alice Johnson',
                'username' => 'alicejohnson',
                'email' => 'alicejohnson@example.com',
                'password' => bcrypt('password3'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Bob Brown',
                'username' => 'bobbrown',
                'email' => 'bobbrown@example.com',
                'password' => bcrypt('password4'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Charlie Davis',
                'username' => 'charliedavis',
                'email' => 'charliedavis@example.com',
                'password' => bcrypt('password5'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Diana Evans',
                'username' => 'dianaevans',
                'email' => 'dianaevans@example.com',
                'password' => bcrypt('password6'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Eve Foster',
                'username' => 'evefoster',
                'email' => 'evefoster@example.com',
                'password' => bcrypt('password7'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Frank Green',
                'username' => 'frankgreen',
                'email' => 'frankgreen@example.com',
                'password' => bcrypt('password8'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Grace Hill',
                'username' => 'gracehill',
                'email' => 'gracehill@example.com',
                'password' => bcrypt('password9'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Hank Ingram',
                'username' => 'hankingram',
                'email' => 'hankingram@example.com',
                'password' => bcrypt('password10'),
                'status' => $statuses[array_rand($statuses)],
                'membership_level' => $membership_levels[array_rand($membership_levels)],
                'verified' => rand(0, 1),
                'type' => 'user',
                'remember_token' => Str::random(10),
            ],
        ]);

    }
}
