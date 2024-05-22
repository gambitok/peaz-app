<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keys = collect([
            'name',
        ]);
        $values = [
            [
                'Over 18',
            ],
            [
                'Under 18',
            ],
            [
                'Both',
            ],
        ];
        foreach ($values as $key => $value) {
            $data = $keys->combine($value);
            DB::table('categories')->insert($data->all());
        }
    }
}
