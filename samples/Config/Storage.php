<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
return [
    'default' => [
        'visibility' => 'public',
        'directory_visibility' => 'public'
    ],

    'local' => [
        'base' => root(__DIR__, 'writeable/storages/'),
        'assets' => root(__DIR__, 'public/assets/'),
        'urls' => [
            'public_url' => 'https://localhost/luminova.ng/public/assets/'
        ],
        'lock_flags' => LOCK_EX,
        'disallow_links' => true,
        'visibility' => [
            'file' => [
                'public' => 0644,
                'private' => 0644,
            ],
            'dir' => [
                'public' => 0740,
                'private' => 7604,
            ],
        ]
    ],

    'ftp' => [
        'host' => 'hostname', // required
        'root' => '/root/path/', // required
        'username' => 'username', // required
        'password' => 'password', // required
    ],

    'sftp-v3' => [
        'root' => 'http://your-webdav-server.org/',
        'username' => 'your-bucket-name',
        'password' => 'path/prefix',
    ],

    'memory' => [
        'readonly' => false
    ],

    'aws-s3' => [
        'configuration' => [
            'credentials' => [
                'key'    => 'your-key',
                'secret' => 'your-secret',
            ],
            'region' => 'your-region',
            'version' => 'latest',
        ],
        'bucket' => 'bucket-name',
        'base' => 'path/prefix',
        'visibility' => 'public',
    ],

    'aws-async-s3' => [
        'configuration' => [
            'credentials' => [
                'key'    => 'your-key',
                'secret' => 'your-secret',
            ],
            'region' => 'your-region',
            'version' => 'latest',
        ],
        'bucket' => 'bucket-name',
        'base' => 'path/prefix',
        'visibility' => 'public',
    ],

    'azure-blob' => [
        'dns' => 'DSN-STRING-HERE',
        'container' => 'container-name',
        'base' => 'path/prefix',
        'visibility' => 'public',
    ],

    'google-cloud' => [
        'configuration' => [],
        'bucket' => 'your-bucket-name',
        'base' => 'path/prefix',
    ],

    'web-dev' => [
        'baseurl' => 'http://your-webdav-server.org/',
        'username' => 'your-bucket-name',
        'password' => 'path/prefix',
    ],

    'zip-archive' => [
        'path' => '/path/to/zip/',
    ]
];