<?php

return [
    'office_name' => env('OFFICE_NAME', 'Barangay Haraya'),

    'locales' => [
        'en' => 'English',
        'fil' => 'Filipino',
    ],

    'retention_days' => (int) env('DEMO_DATA_RETENTION_DAYS', 90),
];
