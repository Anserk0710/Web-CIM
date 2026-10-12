<?php

return [

    'database' => [
        'engine' => env(
            'DB_INTEGRATION_EXPECTED_ENGINE',
            'MySQL',
        ),

        'version_prefix' => env(
            'DB_INTEGRATION_EXPECTED_VERSION_PREFIX',
            '8.4.',
        ),
    ],

];
