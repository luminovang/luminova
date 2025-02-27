<?php 
/**
 * Luminova Framework application filesystem configuration.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\BaseConfig;

final class Files extends BaseConfig
{
    /**
     * Default file permissions (Unix-style).  
     * 
     * - **0644** → Owner can read/write, others can only read.  
     * 
     * @var int $filePermissions Default: `0644`
     */
    public static int $filePermissions = 0644;

    /**
     * Default directory permissions (Unix-style).  
     * 
     * - **0755** → Owner can read/write/execute, others can only read/execute.  
     * 
     * @var int $dirPermissions Default: `0755`
     */
    public static int $dirPermissions = 0755;

    /**
     * File extension mappings based on MIME types.  
     * 
     * - Define custom extensions if needed.  
     * - Example: `'image/jpeg' => 'jpg'`.  
     * 
     * {@inheritdoc}
     * @var array<string,string> $extensions
     */
    protected static array $extensions = [
        // 'image/jpeg' => 'jpg',
    ];
}