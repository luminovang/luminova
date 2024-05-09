<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

/**
 * Tips: 
 * 
 * The key `base` is like a document root for your files.
 * @see https://luminova.ng/docs/0.0.0/configs/storage
 * @see https://luminova.ng/docs/0.0.0/files/storages
 * 
 * @return array<string,mixed> Storage adapter configurations. 
*/
return [
    /**
     * Default configuration
    */
    'default' => [
        'visibility' => 'public',
        'directory_visibility' => 'public'
    ],

    /**
     * Local Filesystem storage configuration.
    */
    'local' => [
        'base' => root(__DIR__, 'writeable/storages/'),
        'assets' => root(__DIR__, 'public/assets/'),
        'urls' => [
            'public_url' => start_url('assets/')
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

    /**
     * Memory storage configuration.
    */
    'memory' => [
        'readonly' => false
    ],

    /**
     * AWS-S3 storage configuration.
    */
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
        'base' => 'path/to/base/storage',
        'visibility' => 'public',
        'urls' => [
            'public_url' => []
        ],
    ],

    /**
     * AWS-ASYNC-S3 storage configuration.
    */
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
        'base' => 'path/to/base/storage',
        'visibility' => 'public',
        'urls' => [
            'public_url' => []
        ],
    ],

    /**
     * AZURE-BLOB storage configuration.
    */
    'azure-blob' => [
        'dns' => 'DSN-STRING-HERE',
        'container' => 'container-name',
        'base' => 'path/to/base/storage',
        'visibility' => 'public',
        'urls' => [
            'public_url' => []
        ],
    ],

    /**
     * Google Cloud storage configuration.
    */
    'google-cloud' => [
        'configuration' => [],
        'bucket' => 'your-bucket-name',
        'base' => 'path/to/base/storage',
        'urls' => [
            'public_url' => []
        ],
    ],

    /**
     * Web Dev storage configuration.
    */
    'web-dev' => [
        'baseurl' => 'http://your-webdav-server.org/',
        'username' => 'your-bucket-name',
        'password' => 'your-password', // required
        'urls' => [
            'public_url' => []
        ]
    ],

    /**
     * FTB storage configuration.
    */
    'ftp' => [
        'host' => 'hostname', // required
        'root' => '/root/path/', // required
        'username' => 'your-username', // required
        'password' => 'your-password', // required
    ],

    /**
     * SFTP storage configuration.
    */
    'sftp-v3' => [
        'host' => 'localhost', // required
        'username' => 'your-username', // required
        'password' => 'your-password', // required
        'port' => 22
    ],

    /**
     * ZIP Archive storage configuration.
    */
    'zip-archive' => [
        'path' => '/path/to/zip/'
    ]
];