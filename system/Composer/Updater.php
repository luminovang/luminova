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

include_once __DIR__ . '/../../bootstrap/constants.php';
include_once __DIR__ . '/../../bootstrap/functions.php';

use \Luminova\Command\Terminal;
use \Luminova\Application\Foundation;

class Updater
{
    /**
     * @var string $frameworkPath framework directory
    */
    private static string $frameworkPath = 'system/plugins/luminovang/framework/';
    /**
     * @var Terminal $terminal 
    */
    private static ?Terminal $terminal = null;

    /**
     * New sample changes.
     * 
     * @param array $toReplace
    */
    private static array $toReplace = [];

    /**
     * Update framework 
     * 
     * @return void 
    */
    public static function update(): void 
    {
        if (getenv('LM_DEBUG_MODE') === false) { 
            if(self::onInstallAndUpdate('bootstrap/', self::$frameworkPath, 'install/Boot/')){
                self::updateConfigurations(self::$frameworkPath . 'install/Config/', 'app/Controllers/Config/');
                self::updateConfigurations(self::$frameworkPath . 'install/Main/', 'samples/Main/', true);
                self::onInstallAndUpdate('system/', self::$frameworkPath, 'src/', true);
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
            self::onInstallAndUpdate('system/', self::$frameworkPath, 'src/', true);
        }
    }

    /**
     * Is the current destination updater
     * If yet skip it.
     * 
     * @param string $dest
     * 
     * @return bool 
    */
    private static function isUpdater(string $dest): bool 
    {
        return str_contains($dest, 'system/Composer/Updater.php');
    }

    /**
     * Get prepared cli instance 
     * 
     * @return Terminal 
    */
    private static function cli(): Terminal
    {
        return self::$terminal ??= new Terminal();
    }

    /**
     * Check and move files and directory to destination
     * 
     * @param string $destination. File destination path.
     * @param string $source File source.
     * 
     * @return void  
    */
    private static function checkAndMoveFolderRecursive(string $destination, string $source): void
    {
        if(!file_exists($source)){
            return;
        }

        self::makeDirectoryIfNotExist($destination);
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = rtrim($source, '/') . "/$file";
                $dstFile = rtrim($destination, '/') . "/$file";

                if(self::isUpdater(self::displayPath($dstFile))){
                    continue;
                }
                
                if (!is_dir($srcFile)) {
                    if (!file_exists($dstFile) || self::fileChanged($srcFile, $dstFile)) {
                        rename($srcFile, $dstFile);
                    }
                } else {
                    self::checkAndMoveFolderRecursive($dstFile, $srcFile); 
                }
            }
        }
    }

    /**
     * Delete directory recursively 
     * 
     * @param string $dir Directory to delete.
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
                self::removeRecursive($path, $main);
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
     * @param string $path File path.
     * 
     * @return string  
    */
    private static function displayPath(string $path): string 
    {
        return dirname($path) . DIRECTORY_SEPARATOR . basename($path);
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

     * @param string $source File source.
     * @param string $destination. File destination path.
     * 
     * @return bool  
    */
    private static function fileChanged(string $source, string $destination): bool
    {
        return md5_file($source) !== md5_file($destination);
    }

    /**
     * Compare two files to see if any changes in the hash

     * @param string $source File source.
     * @param string $destination. File destination path.
     * 
     * @return bool  
    */
    private static function updateConfigurations(string $source, string $destination, bool $main = false): void
    {
        if(!file_exists($source)){
            return;
        }
        $sampleFolder = 'samples' . DIRECTORY_SEPARATOR . $destination;

        self::makeDirectoryIfNotExist($destination);
        if(!$main){
            self::makeDirectoryIfNotExist($sampleFolder);
        }
        $files = scandir($source);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
                $dstFile = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
                $sampleFile = rtrim($sampleFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;

                if (!is_dir($srcFile)) {
                    self::doConfigCopy($srcFile, $dstFile, $sampleFile, $main);
                } else if (is_dir($srcFile)) {
                    self::updateDevConfigs($dstFile, $sampleFile, $srcFile, $main);
                }
            }
        }
    }

    /**
     * Check and move files and directory to destination
     * 
     * @param string $destination. File destination path.
     * @param string $sampleFolder File sample destination path.
     * @param string $source File source.
     * 
     * @return void  
    */
    private static function updateDevConfigs(string $destination, string $sampleFolder, string $source, bool $main = false): void
    {
        if(!file_exists($source)){
            return;
        }

        self::makeDirectoryIfNotExist($destination);
        if(!$main){
            self::makeDirectoryIfNotExist($sampleFolder);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
                $dstFile = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
                $sampleFile = rtrim($sampleFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;

                if (!is_dir($srcFile)) {
                    self::doConfigCopy($srcFile, $dstFile, $sampleFile, $main);
                } else {
                    self::updateDevConfigs($dstFile, $sampleFile, $srcFile, $main); 
                }
            }
        }
    }

    /**
     * Do move file to it destination.
    */
    private static function doConfigCopy(string $from, string $to, string $sample, bool $main = false): bool
    {
        if (file_exists($to)) {
            if($main){
                unlink($to);
            }else{
                if (file_exists($sample)) {
                    if(self::fileChanged($from, $sample)){
                        unlink($sample);
                        self::$toReplace[] = $sample;
                        return rename($from, $sample);
                    }

                    return true;
                }

                return rename($from, $sample);
            }
        }

        return rename($from, $to);
    }

    /**
     * Update framework codes after installation and update 
     * 
     * @param string $destination. File destination path.
     * @param string $source File source.
     * @param string $codes sub folder to start looking.
     * @param bool $complete complete.
     * 
     * @return bool  
    */
    private static function onInstallAndUpdate(string $destination, string $source, string $codes, bool $complete = false): bool
    {
        $fullSource = $source . $codes;
        if(file_exists($fullSource)){
            self::makeDirectoryIfNotExist($destination);
            $files = scandir($fullSource);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $srcFile = rtrim($fullSource, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
                    $dstFile = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
  
                    if(self::isUpdater(self::displayPath($dstFile))){
                        continue;
                    }

                    if (!is_dir($srcFile)) {
                        if (!file_exists($dstFile) || self::fileChanged($srcFile, $dstFile)) {
                            if(file_exists($dstFile)){
                                unlink($dstFile);
                            }
                            
                            rename($srcFile, $dstFile);
                        }
                    } else if (is_dir($srcFile)) {
                        self::checkAndMoveFolderRecursive($dstFile, $srcFile);
                    }
                }
            }

            if($complete){
                $base = APP_ROOT . DIRECTORY_SEPARATOR;
                $toDos = $base . $source . 'TODO.md';
                $currentTodo = $base . 'TODO.md';
                $hasTodo = false;

                if(file_exists($toDos) && self::fileChanged($toDos, $currentTodo)){
                    $hasTodo = true;
                    if (copy($toDos, $currentTodo)) {
                        unlink($toDos);
                    }
                }

                self::removeRecursive($base . $source, 'framework');
                exec('LM_DEBUG_MODE=1 composer dumpautoload', $output, $returnCode);
                foreach ($output as $line) {
                    self::cli()->writeln('Dumping:   ' . $line);
                }

                if ($returnCode === 0) {
                    self::cli()->success('Update was completed version [' . (Foundation::VERSION??'1.5.0') . ']');
                    if($hasTodo || self::$toReplace !== []){
                        self::cli()->beeps(2);
                        self::cli()->writeln('TODO ATTENTION!', 'yellow');

                        if(self::$toReplace !== []){
                            self::cli()->writeln('Please see /samples/ to manual replace your configuration files accordingly.');
                        }

                        if($hasTodo){
                            self::cli()->writeln('Please see /TODO.md to follow few manual associated with the current version update.');
                        }
                    }
                }
            }
        }

        return true;
    }
}
