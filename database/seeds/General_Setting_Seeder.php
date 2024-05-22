<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class General_Setting_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keys = collect([
            'label',
            'unique_name',
            'type',
            'value',
            'options',
            'extra',
            'hint',
        ]);
        $values = [
            [
                'Site Name',
                'site_name',
                'text',
                'name',
                null,
                json_encode([
                    // 'required' => 'required',
                    "onkeypress"=>'return((event.which > 64 && event.which < 91) || (event.which > 96 && event.which < 123) || event.which == 8 || event.which == 32 || (event.which >= 48 && event.which <= 57)) ',
                ]),
                'Please enter site name',
            ],
            [
                'Site Logo',
                'site_logo',
                'file',
                '',
                null,
                json_encode([
                    'accept' => "image/*",
                ]),
                'Site logo main'
            ],
            [
                'Small Site Logo',
                'small_site_logo',
                'file',
                '',
                null,
                json_encode([
                    'accept' => "image/*",
                ]),
                'Site small logo main'
            ],
            [
                'Fav Icon',
                'Favicon',
                'file',
                '',
                null,
                json_encode([
                    'accept' => "image/*",
                ]),
                'Fav icon for site'
            ],
            [
                'Footer Text',
                'footer_text',
                'textarea',
                'Footer Text',
                null,
                json_encode([
                    'maxlength' => "255",
                    // 'required' => 'required',
                ]),
                'Please enter site footer text'
            ],
            [
                'Admin Email',
                'ADMIN_EMAIL',
                'email',
                'admin@gmail.com',
                null,
                json_encode([
                    'maxlength' => "255",
                    // 'required' => 'required',
                ]),
                'Please enter email address for admin'
            ],
            [
                'Android Version',
                'Android_Version',
                'number',
                '1',
                null,
                json_encode([
                    'step' => "0.01",
                    // 'required' => 'required',
                    'min' => 1,
                ]),
                'Please enter android current version'
            ],
            [
                'Android Force Update',
                'Android_Force_Update',
                'select',
                '0',
                json_encode([
                    ['name' => 'Yes', 'value' => 1],
                    ['name' => 'No', 'value' => 0],
                ]),
                null,
                'is android update is forced'
            ],
            [
                'Ios Version',
                'IOS_Version',
                'number',
                '1',
                null,
                json_encode([
                    'step' => "0.01",
                    // 'required' => 'required',
                    'min' => 1,
                ]),
                'Please enter ios current version'
            ],
            [
                'Ios Force Update',
                'IOS_Force_Update',
                'select',
                '0',
                json_encode([
                    ['name' => 'Yes', 'value' => 1],
                    ['name' => 'No', 'value' => 0],
                ]),
                null,
                'is Ios update is forced'
            ],
           
        ];
        foreach ($values as $value) {
            $data = $keys->combine($value);
            DB::table('general_settings')->insert($data->all());
        }

    }
}
