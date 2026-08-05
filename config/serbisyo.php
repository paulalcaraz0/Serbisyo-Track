<?php

return [
    'office_name' => env('OFFICE_NAME', 'Barangay Haraya'),

    'locales' => [
        'en' => 'English',
        'fil' => 'Filipino',
    ],

    'retention_days' => (int) env('DEMO_DATA_RETENTION_DAYS', 90),

    'tracking_access_minutes' => 15,

    'attachment_max_files' => 5,

    'attachment_max_kilobytes' => 5120,
];
