<?php

return [
    'free' => [
        'max_events'  => 1,
        'max_guests'  => 50,
        'email'       => false,
        'export'      => false,
        'table_assign'=> false,
        'storage_days'=> 365,
    ],
    'basic' => [
        'max_events'  => 5,
        'max_guests'  => 200,
        'email'       => true,
        'export'      => false,
        'table_assign'=> false,
        'storage_days'=> 365,
    ],
    'pro' => [
        'max_events'  => 10,
        'max_guests'  => 500,
        'email'       => true,
        'export'      => true,
        'table_assign'=> true,
        'storage_days'=> 365,
    ],
    'premium' => [
        'max_events'  => 20,
        'max_guests'  => 1000,
        'email'       => true,
        'export'      => true,
        'table_assign'=> true,
        'storage_days'=> null, // vĩnh viễn
    ],
];
