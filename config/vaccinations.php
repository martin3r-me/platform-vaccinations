<?php

return [
    'name'        => 'Impfungen',
    'description' => 'Katalog der Impfungen/Impfleistungen (frei pflegbar).',
    'version'     => '1.0.0',

    'routing' => [
        'prefix'     => 'vaccinations',
        'middleware' => ['web', 'auth'],
    ],

    'guard' => 'web',

    'navigation' => [
        'route' => 'vaccinations.index',
        'icon'  => 'heroicon-o-shield-check',
        'order' => 47,
    ],

    'sidebar' => [
        [
            'group' => 'Allgemein',
            'items' => [
                ['label' => 'Impfungen', 'route' => 'vaccinations.index', 'icon' => 'heroicon-o-shield-check'],
            ],
        ],
    ],
];
