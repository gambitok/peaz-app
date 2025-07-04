<?php

return [
    'empty_object' => new stdClass(),
    'google_map_key' => 'AIzaSyBRR40Ie35qkoC1F5-v3YsZ1eWt51F3Qqg',
    'asset_url' => env('APP_URL'),
    'upload_type' => 'local',
    'default' => [
        'image' => 'uploads/user/user.png',
        'user_image' => 'assets/general/images/no-profile.png',
        'no_image_available' => 'assets/general/images/no-profile.png',
    ],
    'upload_paths' => [
        'exception_upload' => 'uploads/exception',
        'user_profile_image' => 'uploads/user',
        'admin_upload' => 'uploads/admin',
        'user_instruction_image'=>'uploads/instruction',
        'user_instruction_thumbnail'=>'uploads/instruction/thumbnail',
        'user_post_thumbnail'=>'uploads/post/thumbnail',
        'user_post_image'=>'uploads/post',
        'interest_image'=>'uploads/interestlist'
    ],
    'push_log' => true,
    'firebase_server_key' => '',
];
