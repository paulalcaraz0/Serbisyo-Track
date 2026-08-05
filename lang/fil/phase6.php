<?php

return [
    'home' => [
        'footer_phase' => 'Beripikadong Phase 6: accessible na error handling, privacy review, security hardening, at komprehensibong workflow QA.',
    ],
    'errors' => [
        'meta_title' => 'May hindi inaasahang nangyari',
        'eyebrow' => 'Katayuan ng request :status',
        'home' => 'Bumalik sa home',
        'services' => 'Tingnan ang mga serbisyo',
        'track' => 'I-track ang request',
        'dashboard' => 'Buksan ang dashboard',
        'help' => 'Kung magpatuloy ang problema, gamitin ang help page upang kontakin ang kathang-isip na tanggapan.',
        'statuses' => [
            '403' => [
                'title' => 'Wala kang access sa pahinang ito',
                'description' => 'Walang pahintulot ang iyong account o secure tracking session na tingnan ang resource na ito.',
            ],
            '404' => [
                'title' => 'Hindi namin makita ang pahinang iyon',
                'description' => 'Maaaring mali, nag-expire, o hindi na available ang address.',
            ],
            '419' => [
                'title' => 'Nag-expire ang iyong secure session',
                'description' => 'Para sa iyong proteksyon, hindi naisumite ang form. Bumalik sa naunang pahina at subukan muli.',
            ],
            '429' => [
                'title' => 'Maghintay bago subukan muli',
                'description' => 'Masyadong maraming request ang natanggap sa maikling oras. Maghintay lamang hanggang mag-reset ang limit.',
            ],
            '500' => [
                'title' => 'Hindi namin makumpleto ang request',
                'description' => 'May hindi inaasahang error. Walang teknikal o personal na detalye ang ipinapakita sa pahinang ito.',
            ],
            '503' => [
                'title' => 'Pansamantalang hindi available ang serbisyo',
                'description' => 'May maintenance o pansamantalang pagkaantala. Pakisubukan muli mamaya.',
            ],
        ],
    ],
];
