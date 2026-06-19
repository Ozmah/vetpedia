<?php

declare(strict_types=1);

return [
    'seed_users' => [
        'gabriel' => [
            'name' => env('VETPEDIA_GABRIEL_NAME'),
            'email' => env('VETPEDIA_GABRIEL_EMAIL'),
            'password' => env('VETPEDIA_GABRIEL_PASSWORD'),
        ],

        'carlos' => [
            'name' => env('VETPEDIA_CARLOS_NAME'),
            'email' => env('VETPEDIA_CARLOS_EMAIL'),
            'password' => env('VETPEDIA_CARLOS_PASSWORD'),
        ],

        'dummy_password' => env('VETPEDIA_DUMMY_USER_PASSWORD'),
    ],
];
