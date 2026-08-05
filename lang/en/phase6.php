<?php

return [
    'home' => [
        'footer_phase' => 'Phase 6 verified: accessible error handling, privacy review, security hardening, and comprehensive workflow QA.',
    ],
    'errors' => [
        'meta_title' => 'Something went wrong',
        'eyebrow' => 'Request status :status',
        'home' => 'Return home',
        'services' => 'Browse services',
        'track' => 'Track a request',
        'dashboard' => 'Open dashboard',
        'help' => 'If the problem continues, use the help page to contact the fictional office.',
        'statuses' => [
            '403' => [
                'title' => 'You do not have access to this page',
                'description' => 'Your account or secure tracking session does not have permission to view this resource.',
            ],
            '404' => [
                'title' => 'We could not find that page',
                'description' => 'The address may be incorrect, expired, or no longer available.',
            ],
            '419' => [
                'title' => 'Your secure session expired',
                'description' => 'For your protection, the form was not submitted. Return to the previous page and try again.',
            ],
            '429' => [
                'title' => 'Please wait before trying again',
                'description' => 'Too many requests were received in a short period. Nothing else is needed until the limit resets.',
            ],
            '500' => [
                'title' => 'We could not complete that request',
                'description' => 'An unexpected error occurred. No technical or personal details are shown on this page.',
            ],
            '503' => [
                'title' => 'The service is temporarily unavailable',
                'description' => 'Maintenance or a temporary interruption is in progress. Please try again later.',
            ],
        ],
    ],
];
