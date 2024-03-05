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
class Updater{

    public static function BeforeUpdate(): void 
    {
        echo "Before update loading...";

        /*$composerJsonPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'composer.json';
        $composer = file_get_contents($composerJsonPath);

        if ($composer !== false) {
            $composer = json_decode($composer, true);

            if (!isset($composer['require']['nanoblocktech/psr-cache'])) {
                echo "Updating Composer.json\n";
                $composer['require']['nanoblocktech/psr-cache'] = "^1.0";
                $jsonString = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if ($jsonString !== false) {
                    file_put_contents($composerJsonPath, $jsonString);
                } else {
                    echo "Error encoding JSON.\n";
                }
            }
        }*/
    }

    public static function AfterUpdate(): void 
    {
        if (getenv('LM_DEBUG_MODE') === false) { 
            static::moveFrameworkDirectory('system/Psr/Cache', 'system/plugins/nanoblocktech/psr-cache/', 'src/');
        }
    }

    public static function InstallFiles(): void 
    {
        if (getenv('LM_DEBUG_MODE') === false) {
            //self::moveFrameworkDirectory('system/Psr/Cache', 'system/plugins/nanoblocktech/psr-cache/', 'src/');
            self::checkAndCopyFile('.env', 'samples/.env');
            self::checkAndCopyFile('meta.config.json', 'samples/meta.config.json');
            self::checkAndCopyFile('app/Controllers/Config/Session.php', 'samples/Session.php');
            self::checkAndCopyFile('app/Controllers/Config/Cookie.php', 'samples/Cookie.php');
            self::checkAndCopyFile('app/Controllers/Config/Config.php', 'samples/Config.php');
            self::checkAndCopyFile('app/Controllers/Config/Template.php', 'samples/Template.php');
            self::checkAndCopyFile('app/Controllers/Utils/Global.php', 'samples/Global.php');
            self::checkAndCopyFile('app/Controllers/Utils/Func.php', 'samples/Func.php');
            self::checkAndCopyFile('app/Controllers/Application.php', 'samples/Application.php');
           // self::checkAndCopyFile('system/autoload.api.php', 'samples/autoload.api.php');
           // self::checkAndCopyFile('public/index.php', 'samples/index.php');
            self::checkAndCopyFile('public/.htaccess', 'samples/.htaccess');
            self::checkAndCopyFile('public/robots.txt', 'samples/robots.txt');
           //self::checkAndCopyDirectory('public/api', 'samples/api');
           // self::makeDirectory('public/assets/');

           
           self::backwardProjectDirectory();
        }
    }

    public static function renameProjectRoot(): void {
        $composerJsonPath = __DIR__ . '/../composer.json';
        if(file_exists($composerJsonPath)){
            $projectDir = dirname(dirname(dirname($composerJsonPath)));
            $composerData = json_decode(file_get_contents($composerJsonPath), true);
            $projectDestination = dirname($projectDir) . "/my-project.com";
            if (isset($composerData['name'])) {
                list($vendor, $name) = explode("/", $composerData['name']);
                if ($name === basename($projectDir) && rename($projectDir, $projectDestination)){
                    echo "Renamed project directory to my-project.com\n";
                }
            }
        }
    }    
 
    public static function backwardProjectDirectory(): void 
    {
        $composerJsonPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'composer.json';

        if (file_exists($composerJsonPath)) {
            $composerData = json_decode(file_get_contents($composerJsonPath), true);
            if (!isset($composerData['name'])) {
                return;
            }

            $currentProjectDir = dirname(dirname(dirname($composerJsonPath)));

            [$vendor, $projectName] = explode("/", $composerData['name']);

            if ($projectName === basename($currentProjectDir)) {
                $documentRoot = basename(dirname(dirname(dirname(realpath(__DIR__)))));
                $projectDestinationDir = dirname($currentProjectDir);
                $projectDestinationName = basename($projectDestinationDir);

                if ($projectDestinationName === $documentRoot) {
                    $newProjectDir = "{$projectDestinationDir}/my-project.com";
                    if(rename($currentProjectDir, $newProjectDir)){
                        echo "Renamed project directory to my-project.com\n";
                    } else {
                        echo "Failed to rename project directory.\n";
                    }
                } else {
                    self::checkAndMoveDirectory($projectDestinationDir, $currentProjectDir);
                }
            }
        }
    }


    private static function checkAndCopyFile(string $destination, string $source): void
    {
        if (!file_exists($destination)) {
            copy($source, $destination);
            echo "Copied: $source to $destination\n";
        }
    }

    private static function checkAndMoveDirectory(string $destination, string $source): void
    {
        static::makeDirectoryIfNotExist($destination);
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = rtrim($source, '/') . "/$file";
                $dstFile = rtrim($destination, '/') . "/$file";
                if (!is_dir($srcFile)) {
                    if(rename($srcFile, $dstFile)){
                        echo "Successfully moved: " . static::displayPath($dstFile) . "\n";
                    }else{
                        echo "Unable to move: " . static::displayPath($dstFile) . "\n";
                    }
                } else {
                    static::checkAndMoveDirectory($dstFile, $srcFile); 
                }
            }
        }

       // rmdir($source);
        echo "Removed directory: $source\n";
    }

    private static function checkAndCopyDirectory(string $destination, string $source): void
    {
        static::makeDirectoryIfNotExist($destination);
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = "$source/$file";
                $dstFile = "$destination/$file";
                if (!is_dir($srcFile) && !file_exists($dstFile)) {
                    copy($srcFile, $dstFile);
                    echo "Copied: $srcFile to $dstFile\n";
                } else if(is_dir($srcFile)){
                    self::checkAndCopyDirectory($dstFile, $srcFile);
                }
            }
        }
    }


    private static function removeRecursive(string $dir, ?string $main = null): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = rtrim($dir, '/') . "/$file";
            if (is_dir($path)) {
                self::removeRecursive($path, $main);
            } else {
                unlink($path);
            }
        }

        if ($main === null || (basename($dir) !== $main && is_dir($dir))) {
            rmdir($dir); 
        }
    }    

    

    private static function displayPath(string $path): string 
    {
        return dirname($path) . '/' . basename($path);
    }

    private static function makeDirectoryIfNotExist(string $path): void 
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            echo "Created directory: " . dirname($path) . "\n";
        }
    }

    //"nanoblocktech/psr-cache": "^1.0",
    private static function moveFrameworkDirectory(string $destination, string $source,  string $codes): void
    {
        $fullSource = $source . $codes;
        if(file_exists($fullSource)){
            static::makeDirectoryIfNotExist($destination);
            $files = scandir($fullSource);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $srcFile = rtrim($fullSource, '/') . "/$file";
                    $dstFile = rtrim($destination, '/') . "/$file";
                    
                    if (!is_dir($srcFile)) {
                        if(file_exists($dstFile)){
                            echo "Removing old version of: " . static::displayPath($dstFile) . "\n";
                            unlink($dstFile);
                        }

                        if (copy($srcFile, $dstFile)) {
                            echo "Copying " . static::displayPath($dstFile) . " ...\n";
                            if(unlink($srcFile)){
                                echo "Copy completed: " . static::displayPath($dstFile) . "\n";
                            }
                        } else {
                            echo "Failed to copy: " . static::displayPath($srcFile) . "\n";
                        }
                    } else if (is_dir($srcFile)) {
                        static::checkAndMoveDirectory($dstFile, $srcFile);
                    }
                }
            }

           /* echo "Removing  psr-cache\n";
            exec('LM_DEBUG_MODE=1 composer remove nanoblocktech/psr-cache --no-update', $output, $returnCode);

            $composerJsonPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'composer.json';
            $composer = file_get_contents($composerJsonPath);

            if ($composer !== false) {
                $composer = json_decode($composer, true);

                if (isset($composer['require']['nanoblocktech/psr-cache'])) {
                    echo "Updating Composer.json\n";
                    unset($composer['require']['nanoblocktech/psr-cache']);
                    $jsonString = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    if ($jsonString !== false) {
                        file_put_contents($composerJsonPath, $jsonString);
                    } else {
                        echo "Error encoding JSON.\n";
                    }
                }
            }

            // After copying all files, delete the original directory
            //self::removeRecursive($source);
          
            echo "Cleaning and updating framework dependency...:\n";
            foreach ($output as $line) {
                echo $line . "\n";
            }
        
            if ($returnCode === 0) {
                echo "Updating dumpautoload\n";
                exec('LM_DEBUG_MODE=1 composer dumpautoload');
                self::removeRecursive(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $source);
                echo "Framework updated: $source\n";
            }*/
            self::removeRecursive(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $source, 'psr-cache');
            exec('composer dumpautoload');
        }
    }
}
