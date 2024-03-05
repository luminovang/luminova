<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace Luminova\Composer;
use Luminova\Composer\BaseComposer;
use Luminova\Config\DotEnv;
class Builder extends BaseComposer
{  
    private static $projectFiles = [
        "/app",
        "/system",
        "/public",
        "/resources",
        "/writeable",
        ".env",
        ".gitkeep",
        "composer.json",
        "meta.config.json"
    ];
    
    private static $systemIgnoreFiles = [
        "/system/log",
        "/writeable/caches",
        /*"/system/plugins/phpstan",
        "/system/plugins/bin/php-parse",
        "/system/plugins/bin/phpstan",
        "/system/plugins/bin/phpstan.phar",
        "/system/plugins/nikic",
        "/system/plugins/peterujah/php-functions",*/
        "/phpstan.includes.php",
        "/phpstan.neon",
        "/builder.phar",
        "/rector.php",
        "/command.phar",
        "/composer.lock",
        "/git.ssh",
        "/LICENSE",
        "/README.md",
        "/.DS_Store"
    ];

    private static $projectIgnoreFiles = [
        "composer.lock",
        "LICENSE",
        "LICENSE.txt",
        "LICENSE.md",
        "README",
        "README.md",
        "README.txt",
        ".DS_Store",
        ".fleet",
        ".idea",
        ".vscode",
        ".git"
    ];


    public static function buildProject(string $destinationDir = "build"): void
    {
        DotEnv::register(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env');

        self::$systemIgnoreFiles[] = $destinationDir;
        $destinationDir = "{$destinationDir}/v-" . parent::appVersion();

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        parent::progress(10, function ($step) use ($destinationDir) {
            //echo "Copying file $step of 10 from '.' to '$destinationDir'\n";
            self::copyFiles('.', $destinationDir);
            return $destinationDir;
        }, 
        function($dir, $results){
            if (chdir($dir)) {
                $project_link = "http://" . strtolower(gethostname());
                $project_link .= "/" . basename(dirname(__DIR__, 2));
                $project_link .= "/" . $dir . "/public";
                $envFilePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR  . '.env';
                //$project_link = urlencode($project_link);

                exec('LM_DEBUG_MODE=1 composer install --no-dev', $output, $returnCode);
                echo "Cleaning and updating project dependency...:\n";
                foreach ($output as $line) {
                    echo $line . "\n";
                }
            
                if ($returnCode === 0) {
                    exec('LM_DEBUG_MODE=1 composer dump-autoload --optimize --no-dev');
                    echo "\033[32mDumping project development files...\033[0m\n";
                    foreach ($output as $line) {
                        echo $line . "\n";
                    }
         
                    echo "Updating environment variables...\n";

                    $envFile = file_get_contents($envFilePath);
                    if ($envFile === false) {
                        echo "\033[31mFailed to read the .env file.\033[0m\n"; 
                    }
                    $updatedEnvFile = preg_replace('/^app\.environment\.mood\s*=\s*development/m', 'app.environment.mood = production', $envFile);
                    if (file_put_contents($envFilePath, $updatedEnvFile) === false) {
                        echo "\033[31mFailed to write to the .env file.\033[0m\n"; 
                    }
                    echo "\033[32mProject build completed successfully.\033[0m\n";
                    echo "To view your project, click the below link:\n";
                    //echo "\033]8;;$project_link\033\\$project_link\033]8;;\033\\";
                    echo "\033[34m" . $project_link . "\033[0m\n";

                } else {
                    echo "\033[31mFail to build project failed.\033[0m\n"; 
                }
            } else {
                echo "\033[31mFailed to change to the build production directory.\033[0m\n";
                echo "To build the project, please follow these steps:\n";
                echo "1. Navigate to the directory: $dir\n";
                echo "2. Run the following commands:\n";
                echo "   - \033[32mLM_DEBUG_MODE=1 composer install --no-dev\033[0m\n";
                echo "   - \033[32mLM_DEBUG_MODE=1 composer dump-autoload --optimize --no-dev\033[0m\n";
                exit(1);
    
            } 
        },
        "Project production files copied to: $destinationDir");                  
    }

    public static function buildArchiveProject(string $zipFileName, string $buildDir = "builds"): void
    {
        DotEnv::register(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env');

        self::$systemIgnoreFiles[] = $zipFileName;
        $zip = new \ZipArchive();
        $buildDir = "{$buildDir}/v-" . parent::appVersion();
   
        try {
            if(!is_dir($buildDir)){
                mkdir($buildDir, 0755, true); 
            }
            if ($zip->open("{$buildDir}/{$zipFileName}", \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Error creating zip file.");
                echo "Error creating project archive file.\n";
                die(0);
            }

            echo "Creating a zip archive for the project...\n";
            self::addToZip($zip, '.', '');
            echo "Archiving project...\n";
            $zip->close();

            echo "Project archive exported successfully: $zipFileName\n";
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    private static function addToZip(\ZipArchive $zip, string $folder, string $zipFolder): void
    {
        echo "Scanning folder: $folder\n";
        $files = scandir($folder);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $folder . '/' . $file;
            $relativePath = $zipFolder . '/' . $file;
            //$relativePath = $zipFolder === '' ? $file : $zipFolder . '/' . $file; 
            echo "Processing: $filePath\n";
            if (is_dir($filePath)) {
                if (self::shouldBeIncluded($relativePath) && !self::shouldBeIgnored($relativePath)) {
                    echo "Adding directory: " . basename($relativePath) . "\n";
                    $zip->addEmptyDir($relativePath);
                    self::addToZip($zip, $filePath, $relativePath);
                } else {
                    echo "Skipping folder: " . basename($relativePath) . "\n";
                }
            } else {
                if (!self::shouldBeIgnored($relativePath) && !self::shouldBeSkipped(basename($relativePath))) {
                    echo "Adding file: " . basename($relativePath) . "\n";
                    $zip->addFile($filePath, $relativePath);
                } else {
                    echo "Skipping file: " . basename($relativePath) . "\n";
                }
            }

        }
    }

    private static function copyFiles(string $source, string $destination): void
    {
        $files = scandir($source);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $sourcePath = $source . '/' . $file;
            $destinationPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                if (self::shouldBeIncluded($sourcePath) && !self::shouldBeIgnored($sourcePath)) {
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                        echo "- Created folder: " . $destinationPath . "\n";
                    }
                    echo "- Copying folder: " . $sourcePath . "\n";
                    self::copyFiles($sourcePath, $destinationPath);
                }
            } else {
                if (!self::shouldBeIgnored($sourcePath) && !self::shouldBeSkipped(basename($sourcePath))) {
                    copy($sourcePath, $destinationPath);
                    echo "- Copied file: " . $sourcePath . "\n";
                }
            }
        }
    }

    private static function shouldBeIncluded(string $path): bool
    {
        foreach (self::$projectFiles as $projectFile) {
            if (fnmatch($projectFile, parent::parseLocation($path)) || parent::isParentOrEqual($projectFile, parent::parseLocation($path)) ) {
               return true;
            }
        }
        return false;
    }

    private static function shouldBeIgnored(string $path): bool
    {

        foreach (self::$systemIgnoreFiles as $ignoreFile) {
            if (fnmatch($ignoreFile, parent::parseLocation($path))) {
                return true;
            }
        }
        return false;
    }

    private static function shouldBeSkipped(string $path): bool
    {

        foreach (self::$projectIgnoreFiles as $skipFile) {
            if (fnmatch(strtolower($skipFile), strtolower(parent::parseLocation($path)))) {
                return true;
            }
        }
        return false;
    }
}