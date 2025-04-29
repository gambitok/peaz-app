<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keys = collect([

            'title',
            'status'
        ]);
        $values = [
            [

                "It's spam",
                'active'
            ],
            [

                'Nudity or sexual activity',
                'active',
            ],
            [

                "I just don't like it",
                "active"
            ],
            [

                'Scam or fraud',
                "active"
            ],
            [

                'False information',
                "active",
            ],
            [

                'Hate speech or symbols',
                "active"
            ],
        ];
        foreach ($values as $key => $value) {
            $data = $keys->combine($value);
            DB::table('reports')->insert($data->all());
        }
    }
}
