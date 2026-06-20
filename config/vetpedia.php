<?php

declare(strict_types=1);

return [
    'seed_users' => [
        'superadmin' => [
            'name' => env('VETPEDIA_SUPERADMIN_NAME'),
            'email' => env('VETPEDIA_SUPERADMIN_EMAIL'),
            'password' => env('VETPEDIA_SUPERADMIN_PASSWORD'),
        ],

        'admin' => [
            'name' => env('VETPEDIA_ADMIN_NAME'),
            'email' => env('VETPEDIA_ADMIN_EMAIL'),
            'password' => env('VETPEDIA_ADMIN_PASSWORD'),
        ],

        'dummy_password' => env('VETPEDIA_DUMMY_USER_PASSWORD'),
    ],
];
