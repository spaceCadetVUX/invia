<?php

return [
    'free' => [
        'label'         => 'Free',
        'price'         => 0,
        'guests'        => 50,
        'max_events'    => 1,
        'email'         => false,
        'export'        => false,
        'table'         => false,
        'storage_years' => 1,
    ],
    'basic' => [
        'label'         => 'Basic',
        'price'         => 99_000,
        'guests'        => 200,
        'max_events'    => 5,
        'email'         => true,
        'export'        => false,
        'table'         => false,
        'storage_years' => 1,
    ],
    'pro' => [
        'label'         => 'Pro',
        'price'         => 199_000,
        'guests'        => 500,
        'max_events'    => 10,
        'email'         => true,
        'export'        => true,
        'table'         => true,
        'storage_years' => 1,
    ],
    'premium' => [
        'label'         => 'Premium',
        'price'         => 399_000,
        'guests'        => 1000,
        'max_events'    => 20,
        'email'         => true,
        'export'        => true,
        'table'         => true,
        'storage_years' => null,
    ],
    'extra' => [
        'label' => 'Extra +100 khách',
        'price' => 49_000,
    ],
];
