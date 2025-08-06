<?php

use Illuminate\Support\Facades\File;

return [
    'icon' => '/images/logo.png',

    'background' => '/images/login_banner.jpg',

    'support_url' => '',

    'server' => [
        'php' => [
            'name' => 'PHP Version >= 8.1.0',
            'version' => '>= 8.1.0',
            'check' => [
                'type' => 'php',
                'value' => 80100,
            ],
        ],
        'pdo' => [
            'name' => 'PDO',
            'check' => [
                'type' => 'extension',
                'value' => 'pdo_mysql',
            ],
        ],
        'mbstring' => [
            'name' => 'Mbstring extension',
            'check' => [
                'type' => 'extension',
                'value' => 'mbstring',
            ],
        ],
        'fileinfo' => [
            'name' => 'Fileinfo extension',
            'check' => [
                'type' => 'extension',
                'value' => 'fileinfo',
            ],
        ],
        'openssl' => [
            'name' => 'OpenSSL extension',
            'check' => [
                'type' => 'extension',
                'value' => 'openssl',
            ],
        ],
        'tokenizer' => [
            'name' => 'Tokenizer extension',
            'check' => [
                'type' => 'extension',
                'value' => 'tokenizer',
            ],
        ],
        'json' => [
            'name' => 'Json extension',
            'check' => [
                'type' => 'extension',
                'value' => 'json',
            ],
        ],
        'curl' => [
            'name' => 'Curl extension',
            'check' => [
                'type' => 'extension',
                'value' => 'curl',
            ],
        ],
        'zip' => [
            'name' => 'ZipArchive Extension',
            'check' => [
                'type' => 'extension',
                'value' => 'zip',
            ],
        ]
    ],

    'folders' => [
        'storage.framework' => [
            'name' => base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework',
            'check' => [
                'type' => 'directory',
                'value' => base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework',
            ],
        ],
        'storage.logs' => [
            'name' => base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
            'check' => [
                'type' => 'directory',
                'value' => base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
            ],
        ],
        'storage.cache' => [
            'name' => base_path() . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache',
            'check' => [
                'type' => 'directory',
                'value' => base_path() . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache',
            ],
        ],
    ],

    'database' => [
        'seeders' => true,
    ],

    'commands' => ['db:seed --class=DatabaseSeeder'],

    'admin_area' => [
        'user' => [
            'email' => 'admin',
            'password' => 'admin123',
        ],
    ],
    'login' => '/login',
];
