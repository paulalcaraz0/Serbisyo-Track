<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inertia page components
    |--------------------------------------------------------------------------
    |
    | The React application and Vite resolver use a lowercase `pages`
    | directory. Defining the same path here keeps Inertia's component finder
    | consistent on case-sensitive CI and production filesystems.
    |
    */

    'page_paths' => [
        resource_path('js/pages'),
    ],

    'page_extensions' => [
        'js',
        'jsx',
        'svelte',
        'ts',
        'tsx',
        'vue',
    ],

    'testing' => [
        'ensure_pages_exist' => true,

        'page_paths' => [
            resource_path('js/pages'),
        ],

        'page_extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],
    ],
];
