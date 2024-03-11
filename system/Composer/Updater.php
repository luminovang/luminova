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

use \Luminova\Command\Terminal;
use \Luminova\Base\BaseConfig;

class Updater
{
    /**
     * @var string $framework framework id 
    */
    private static string $framework = 'luminovang/framework';

    /**
     * @var string $frameworkPath framework directory
    */
    private static string $frameworkPath = 'system/plugins/luminovang/framework/';
    /**
     * @var Terminal $terminal 
    */
    private static ?Terminal $terminal = null;

    /**
     * Update framework 
     * 
     * @return void 
    */
    public static function update(): void 
    {
        if (getenv('LM_DEBUG_MODE') === false) { 
            if(static::onInstallAndUpdate('libraries/sys/', static::$frameworkPath, 'libraries/sys/')){
                static::onInstallAndUpdate('system/', static::$frameworkPath, 'src/', true);
            }
        }
    }

    /**
     * Install, update framework and configure project
     * 
     * @return void 
    */
    public static function install(): void 
    {
        if (getenv('LM_DEBUG_MODE') === false) {
            static::onInstallAndUpdate('system/', static::$frameworkPath, 'src/', true);
            static::checkAndCopyFile('.env', 'samples/.env');
            static::checkAndCopyFile('meta.config.json', 'samples/meta.config.json');
            static::checkAndCopyFile('class.config.php', 'samples/class.config.php');
            static::checkAndCopyFile('app/Controllers/Config/Session.php', 'samples/Session.php');
            static::checkAndCopyFile('app/Controllers/Config/Cookie.php', 'samples/Cookie.php');
            static::checkAndCopyFile('app/Controllers/Config/Config.php', 'samples/Config.php');
            static::checkAndCopyFile('app/Controllers/Config/Template.php', 'samples/Template.php');
            static::checkAndCopyFile('app/Controllers/Config/IPConfig.php', 'samples/IPConfig.php');
            static::checkAndCopyFile('app/Controllers/Config/Files.php', 'samples/Files.php');
            static::checkAndCopyFile('app/Controllers/Utils/Global.php', 'samples/Global.php');
            static::checkAndCopyFile('app/Controllers/Utils/Func.php', 'samples/Func.php');
            static::checkAndCopyFile('app/Controllers/Application.php', 'samples/Application.php');
            static::checkAndCopyFile('public/.htaccess', 'samples/.htaccess');
            static::checkAndCopyFile('public/robots.txt', 'samples/robots.txt');

            //static::makeDirectory('public/assets/');
            //static::backwardProjectDirectory();
        }
    }

    /**
     * Move project backward if installed in project name 
     * 
     * @return void 
    */
    private static function backwardProjectDirectory(): void 
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
                    static::checkAndMoveFolderRecursive($projectDestinationDir, $currentProjectDir);
                }
            }
        }
    }

    /**
     * Is the current destination updater
     * If yest skip it
     * 
     * @return bool 
    */
    private static function isUpdater(string $dest): bool 
    {
        $file = 'Composer/Updater.php';

        if(in_array($dest, [$file, '/' . $file, 'system/' . $file, '/system/' . $file])){
            return true;
        }

        return false;
    }

    /**
     * If source file exist copy it to destination
     * 
     * @param string $destination
     * @param string source
     * 
     * @return void 
    */
    private static function checkAndCopyFile(string $destination, string $source): void
    {
        if (file_exists($source) && !file_exists($destination)) {
            copy($source, $destination);
        }
    }

    /**
     * Get prepared cli instance 
     * 
     * @return Terminal 
    */
    private static function cli(): Terminal
    {
        return static::$terminal ??= new Terminal();
    }

    /**
     * Check and move files and directory to destination
     * 
     * @param string $destination
     * @param string source
     * 
     * @return void  
    */
    private static function checkAndMoveFolderRecursive(string $destination, string $source): void
    {
        static::makeDirectoryIfNotExist($destination);
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = rtrim($source, '/') . "/$file";
                $dstFile = rtrim($destination, '/') . "/$file";
                $display = static::displayPath($dstFile);
                if(static::isUpdater($display)){
                    continue;
                }
                
                if (!is_dir($srcFile)) {
                    if (!file_exists($dstFile) || static::fileChanged($srcFile, $dstFile)) {
                        rename($srcFile, $dstFile);
                    }
                } else {
                    static::checkAndMoveFolderRecursive($dstFile, $srcFile); 
                }
            }
        }
    }

    /**
     * Delete directory recursively 
     * 
     * @param string $dir
     * @param string|null $main main directory to ignore deletion
     * 
     * @return void  
    */
    private static function removeRecursive(string $dir, ?string $main = null): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = rtrim($dir, '/') . "/$file";
            if (is_dir($path)) {
                static::removeRecursive($path, $main);
            } else {
                unlink($path);
            }
        }

        if ($main === null || (basename($dir) !== $main && is_dir($dir))) {
            rmdir($dir); 
        }
    }    

    /**
     * Get relative path to print
     * 
     * @param string $path
     * 
     * @return string  
    */
    private static function displayPath(string $path): string 
    {
        return dirname($path) . '/' . basename($path);
    }

    /**
     * Create directory if not exist
     * 
     * @param string $path
     * 
     * @return void  
    */
    private static function makeDirectoryIfNotExist(string $path): void 
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Compare two files to see if any changes in the hash
     * 
     * @param string $srcFile
     * @param string $dstFile
     * 
     * @return bool  
    */
    private static function fileChanged(string $srcFile, string $dstFile): bool
    {
        return md5_file($srcFile) !== md5_file($dstFile);
    }

    /**
     * Update framework codes after installation and update 
     * 
     * @param string $destination
     * @param string $source
     * @param string $codes sub folder to start looking
     * @param bool $complete complete
     * 
     * @return bool  
    */
    private static function onInstallAndUpdate(string $destination, string $source,  string $codes, bool $complete = false): bool
    {
        $fullSource = $source . $codes;
        if(file_exists($fullSource)){
            static::makeDirectoryIfNotExist($destination);
            $files = scandir($fullSource);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $srcFile = rtrim($fullSource, '/') . "/$file";
                    $dstFile = rtrim($destination, '/') . "/$file";
                    $display = static::displayPath($dstFile);

    
                    if(static::isUpdater($display)){
                        continue;
                    }

                    if (!is_dir($srcFile)) {
                        if (!file_exists($dstFile) || static::fileChanged($srcFile, $dstFile)) {
                            if(file_exists($dstFile)){
                                unlink($dstFile);
                            }

                            if (copy($srcFile, $dstFile)) {
                                unlink($srcFile);
                            }
                        }
                    } else if (is_dir($srcFile)) {
                        static::checkAndMoveFolderRecursive($dstFile, $srcFile);
                    }
                }
            }


            if($complete){
                $base = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
                $toDos = $base . $source . 'TODO.md';
                $currentTodo = $base . 'TODO.md';
                $hasTodo = false;

                if(file_exists($toDos) && static::fileChanged($toDos, $currentTodo)){
                    $hasTodo = true;
                    if (copy($toDos, $currentTodo)) {
                        unlink($toDos);
                    }
                }

                static::removeRecursive($base . $source, 'framework');
                exec('LM_DEBUG_MODE=1 composer dumpautoload', $output, $returnCode);
                foreach ($output as $line) {
                    static::cli()?->writeln('Dumping:   ' . $line);
                }

                if ($returnCode === 0) {
                    static::cli()?->writeln('Update was completed version [' . BaseConfig::$version??'1.5.0' . ']', 'green');
                    if($hasTodo){
                        static::cli()?->writeln('TODO ATTENTION!', 'red');
                        static::cli()?->writeln('Please see /TODO.md to few manual associated with the current version update');
                    }
                }
            }
        }

        return true;
    }
}
