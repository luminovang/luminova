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

use \Luminova\Base\Configuration;

final class Files extends Configuration
{
    /**
     * Default permissions for newly created files (Unix-style).
     * 
     * - **0644** → The file owner can read/write, everyone else can only read.  
     * - Good for most applications — it prevents others from modifying your files.
     *
     * @var int $filePermissions Default: `0644`
     */
    public static int $filePermissions = 0644;

    /**
     * Default permissions for newly created directories (Unix-style).
     * 
     * - **0755** → The directory owner can read/write/execute, 
     *   everyone else can only read/execute.  
     * - This is safe and allows the web server to read files inside while 
     *   preventing unauthorized modifications.
     *
     * @var int $dirPermissions Default: `0755`
     */
    public static int $dirPermissions = 0755;

    /**
     * Maps MIME types to preferred file extensions.
     * 
     * - Lets you define custom file extensions for uploaded or generated files.  
     * - Example: `'image/jpeg' => 'jpg'` will save JPEG files with a `.jpg` extension.  
     * - Leave empty if you don’t need custom mappings — defaults are already handled.
     *
     * @var array<string,string> $extensions
     */
    protected static array $extensions = [
        // 'image/jpeg' => 'jpg',
    ];
}