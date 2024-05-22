<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestedListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keys = collect([
            'type',
            'title',
        ]);
        $values = [
            [
                '1',
                'Indian',
            ],
            [
                '1',
                'Thai',
            ],
            [
                '1',
                'French',
            ],
            [
                '2',
                'Spicy food',
            ],
            [
                '2',
                'Breackfast',
            ],
            [
                '3',
                'Vegan',
            ],
            [
                '3',
                'Vegetarian',
            ]

        ];

        foreach ($values as $key => $value) {
            $data = $keys->combine($value);
            DB::table('interested_list')->insert($data->all());
        }
    }
}
