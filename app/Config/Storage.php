<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use function \Luminova\Funcs\{
    root,
    start_url,
};

/**
 * Tips:
 *
 * The key `base` is like a document root for your remote or local files.
 * Comment or remove storages configuration you are not using.
 * 
 * @see https://luminova.ng/docs/0.0.0/configs/storage
 * @see https://luminova.ng/docs/0.0.0/files/cloud-storages
 *
 * @return array<string,mixed> Returns the storage adapter configurations.
 */

// ------------------------------------------------------------
// Default storage configuration
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG = [
    'default' => [
        'visibility' => 'public',
        'directory_visibility' => 'public'
    ]
];

// ------------------------------------------------------------
// Local Filesystem storage
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['local'] = [
    'base' => root('writeable/storages/'),
    'assets' => root('public/assets/'),
    'urls' => [
        'public_url' => start_url('assets/')
    ],
    'lock_flags' => LOCK_EX,
    'disallow_links' => true,
    'visibility' => [
        'file' => [
            'public'  => 0644,
            'private' => 0644,
        ],
        'dir' => [
            'public'  => 0740,
            'private' => 7604,
        ],
    ]
];

// ------------------------------------------------------------
// Memory storage
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['memory'] = [
    'readonly' => false
];

// ------------------------------------------------------------
// AWS S3
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['aws-s3'] = [
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
];

// ------------------------------------------------------------
// AWS ASYNC S3
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['aws-async-s3'] = [
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
];

// ------------------------------------------------------------
// Azure Blob
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['azure-blob'] = [
    'dns' => 'DSN-STRING-HERE',
    'container' => 'container-name',
    'base' => 'path/to/base/storage',
    'visibility' => 'public',
    'urls' => [
        'public_url' => []
    ],
];

// ------------------------------------------------------------
// Google Cloud
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['google-cloud'] = [
    'configuration' => [],
    'bucket' => 'your-bucket-name',
    'base' => 'path/to/base/storage',
    'urls' => [
        'public_url' => []
    ],
];

// ------------------------------------------------------------
// WebDav
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['web-dev'] = [
    'baseurl'  => 'http://your-webdav-server.org/',
    'username' => 'your-bucket-name',
    'password' => 'your-password',
    'urls' => [
        'public_url' => []
    ]
];

// ------------------------------------------------------------
// FTP
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['ftp'] = [
    'host'     => 'hostname',
    'root'     => '/root/path/',
    'username' => 'your-username',
    'password' => 'your-password',
];

// ------------------------------------------------------------
// SFTP
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['sftp-v3'] = [
    'host'     => 'localhost',
    'username' => 'your-username',
    'password' => 'your-password',
    'root'     => '/root/path/',
    'port'     => 22,
    'visibility' => []
];

// ------------------------------------------------------------
// ZIP Archive
// ------------------------------------------------------------
$__CLOUD_STORAGE_CONFIG['zip-archive'] = [
    'path' => '/path/to/zip/'
];

return $__CLOUD_STORAGE_CONFIG;