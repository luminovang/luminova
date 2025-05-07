<?php 
/**
 * Luminova Framework Composer Updater Helper.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova\Composer;

include_once __DIR__ . '/../../bootstrap/constants.php';
include_once __DIR__ . '/../../bootstrap/functions.php';

use \Luminova\Luminova;
use \Luminova\Command\Terminal;

class Updater
{
    /**
     * Path to the framework directory.
     * 
     * @var string $frameworkPath
     */
    private static string $frameworkPath = 'system/plugins/luminovang/framework/';

    /**
     * CLI terminal instance.
     * 
     * @var Terminal $terminal 
     */
    private static ?Terminal $terminal = null;

    /**
     * List of files to be replaced during updates.
     * 
     * @param array $toReplace
     */
    private static array $toReplace = [];

    /**
     * Check if slef has new update.
     * 
     * @var bool selfHasUpdate
     */
    private static bool $selfHasUpdate = false;

    /**
     * Slef update into.
     * 
     * @var array selfInfo
     */
    private static array $selfInfo = [];

    /**
     * Updates the framework by copying necessary files and configurations.
     * 
     * This method checks if the framework requires an update and then:
     * 
     * - Copies the `novakit` to root directory.
     * - Updates configuration files in the `app/` directory.
     * - Updates sample configurations in `samples/Main/`.
     * - Performs additional updates on the `system/` directory.
     * 
     * @return void
     */
    public static function update(): void 
    {
        if(self::onInstallAndUpdate('bootstrap/', self::$frameworkPath, 'install/Boot/')){
            self::doCopy(self::$frameworkPath . 'novakit', 'novakit');
            self::updateConfigurations(self::$frameworkPath . 'install/App/', 'app/');
            self::updateConfigurations(self::$frameworkPath . 'install/Bin/', 'bin/');
            self::updateConfigurations(self::$frameworkPath . 'install/Main/', 'samples/Main/', true);
            self::onInstallAndUpdate('system/', self::$frameworkPath, 'src/', true);
        }
    }

    /**
     * Installs the framework and configures the project.
     * 
     * This method ensures that all required files and directories 
     * are properly installed and set up.
     * 
     * @return void
     */
    public static function install(): void 
    {
        self::onInstallAndUpdate('system/', self::$frameworkPath, 'src/', true);
    }

    /**
     * Checks if the destination path is the updater itself to avoid self-update.
     * 
     * @param string $dest Destination path to check.
     * 
     * @return bool True if the destination is the updater file, otherwise false.
     */
    private static function isUpdater(string $dest): bool 
    {
        return str_ends_with(self::normalizePath($dest), 'system/Composer/Updater.php');
    }

    /**
     * Normalize path.
     * 
     * @param string $path Path to normalize.
     * 
     * @return string Return string.
     */
    private static function normalizePath(string $path): string 
    {
        return str_replace(['\\', '//'], '/', rtrim($path, '/'));
    }

    /**
     * Returns a prepared CLI instance, initializing it if necessary.
     * 
     * @return Terminal Return the CLI instance.
     */
    private static function cli(): Terminal
    {
        return self::$terminal ??= new Terminal();
    }

    /**
     * Recursively moves files and directories to the specified destination.
     * 
     * @param string $destination Target directory.
     * @param string $source Source directory.
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
                $srcFile = rtrim($source, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
                $dstFile = rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $file;

                if(self::isUpdater(self::displayPath($dstFile))){
                    self::$selfHasUpdate = self::fileChanged($srcFile, $dstFile);
                    self::$selfInfo = [$srcFile, $dstFile];
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
     * Recursively deletes a directory and its contents.
     * 
     * @param string $dir Directory to delete.
     * @param string|null $main Root directory to protect from deletion.
     * 
     * @return void
     */
    private static function removeRecursive(string $dir, ?string $main = null): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = rtrim($dir, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                self::removeRecursive($path, $main);
            } else {
                self::delete($path);
            }
        }

        if ($main === null || (basename($dir) !== $main && is_dir($dir))) {
            rmdir($dir); 
        }
    }
    
    /**
     * Deletes a file if it exists.
     * 
     * @param string $file The path to the file to be deleted.
     *
     * @return bool Returns true if the file is successfully deleted, or false if the file does not exist.
     */
    private static function delete(string $file): bool 
    {
        if(!file_exists($file)){
            return false;
        }

        return unlink($file);
    }

    /**
     * Returns a formatted version of the given file path.
     * 
     * @param string $path Full file path.
     * 
     * @return string Relative or formatted file path.
     */
    private static function displayPath(string $path): string 
    {
        return dirname($path) . DIRECTORY_SEPARATOR . basename($path);
    }

    /**
     * Creates a directory if it does not already exist.
     * 
     * @param string $path Directory path.
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
     * Compares two files to determine if their contents differ.
     * 
     * @param string $source Source file.
     * @param string $destination Destination file.
     * 
     * @return bool Return true if the files differ or if the destination does not exist.
     */
    private static function fileChanged(string $source, string $destination): bool
    {
        $isSource = file_exists($source);
        $isDestination = file_exists($destination);

        if(!$isSource && !$isDestination){
            return false;
        }

        if (!$isDestination) {
            return $isSource;
        }

        return ($isSource && $isDestination) 
            ? md5_file($source) !== md5_file($destination) 
            : false;
    }

    /**
     * Updates configuration files by copying and organizing them appropriately.
     * 
     * @param string $source Source directory.
     * @param string $destination Target directory.
     * @param bool $main Whether the directory is the main config directory.
     * 
     * @return void
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
                $srcFile = rtrim($source, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
                $dstFile = rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
                $sampleFile = rtrim($sampleFolder, TRIM_DS) . DIRECTORY_SEPARATOR . $file;

                if (!is_dir($srcFile)) {
                    self::doConfigCopy($srcFile, $dstFile, $sampleFile, $main);
                } else if (is_dir($srcFile)) {
                    self::updateDevConfigs($dstFile, $sampleFile, $srcFile, $main);
                }
            }
        }
    }

    /**
     * Moves application configuration files to the appropriate directories.
     * 
     * @param string $destination Target directory.
     * @param string $sampleFolder Sample directory.
     * @param string $source Source directory.
     * @param bool $main Whether it's a main configuration.
     * 
     * @return void
     */
    private static function updateDevConfigs(
        string $destination, 
        string $sampleFolder, 
        string $source, 
        bool $main = false
    ): void
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
                $srcFile = rtrim($source, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
                $dstFile = rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
                $sampleFile = rtrim($sampleFolder, TRIM_DS) . DIRECTORY_SEPARATOR . $file;

                if (!is_dir($srcFile)) {
                    self::doConfigCopy($srcFile, $dstFile, $sampleFile, $main);
                } else {
                    self::updateDevConfigs($dstFile, $sampleFile, $srcFile, $main); 
                }
            }
        }
    }

    /**
     * Moves a configuration file to its destination.
     * 
     * @param string $from Source file.
     * @param string $to Destination file.
     * @param string $sample Sample file path.
     * @param bool $main Whether it's a main configuration.
     * 
     * @return bool True if moved successfully, false otherwise.
     */
    private static function doConfigCopy(string $from, string $to, string $sample, bool $main = false): bool
    {
        if (!$main && file_exists($to)) {
            if (file_exists($sample)) {
                if(self::fileChanged($from, $sample)){
                    self::delete($sample);
                    self::$toReplace[] = $sample;
                    return rename($from, $sample);
                }

                return true;
            }

            return rename($from, $sample);
        }

        if($main){
            self::delete($to);
        }

        return rename($from, $to);
    }

    /**
     * Moves a file to its destination.
     * 
     * @param string $from Source file.
     * @param string $to Destination file.
     * 
     * @return bool True if moved successfully, false otherwise.
     */
    private static function doCopy(string $from, string $to): bool
    {
        if (file_exists($to)) {
            if(self::fileChanged($from, $to)){
                if(file_exists($to)){
                    self::delete($to);
                }

                return rename($from, $to);
            }

            return false;
        }

        return rename($from, $to);
    }

    /**
     * Handles the installation and update of framework components.
     * 
     * @param string $destination Target directory.
     * @param string $source Source directory.
     * @param string $codes Subdirectory to process.
     * @param bool $complete Whether to perform a full installation.
     * 
     * @return bool True if successful, false otherwise.
     */
    private static function onInstallAndUpdate(
        string $destination, 
        string $source, 
        string $codes, 
        bool $complete = false
    ): bool
    {
        $fullSource = $source . $codes;
        if(file_exists($fullSource)){
            self::makeDirectoryIfNotExist($destination);
            $files = scandir($fullSource);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $srcFile = rtrim($fullSource, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
                    $dstFile = rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $file;
  
                    if(self::isUpdater(self::displayPath($dstFile))){
                        self::$selfHasUpdate = self::fileChanged($srcFile, $dstFile);
                        self::$selfInfo = [$srcFile, $dstFile];
                        continue;
                    }

                    if (!is_dir($srcFile)) {
                        if (!file_exists($dstFile) || self::fileChanged($srcFile, $dstFile)) {
                            if(file_exists($dstFile)){
                                self::delete($dstFile);
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
                $toDos = $base . rtrim($source, TRIM_DS) . 'TODO.md';
                $currentTodo = rtrim($base, TRIM_DS) . 'TODO.md';
                $hasTodo = false;

                if(file_exists($toDos) && self::fileChanged($toDos, $currentTodo)){
                    $hasTodo = true;
                    if (copy($toDos, $currentTodo)) {
                        self::delete($toDos);
                    }
                }

                self::removeRecursive($base . $source, 'framework');
                exec('composer dumpautoload', $output, $returnCode);
                foreach ($output as $line) {
                    self::cli()->writeln('Dumping:   ' . $line);
                }

                if ($returnCode === 0) {
                    self::cli()->writeln('Update was completed version [' . (Luminova::VERSION??'1.5.0') . ']', 'white', 'green');
                    self::cli()->newLine();

                    if($hasTodo || self::$toReplace !== []){
                        self::cli()->beeps(2);
                        self::cli()->writeln('TODO ATTENTION!', 'yellow');

                        if(self::$toReplace !== []){
                            self::cli()->writeln('See "/samples/*" to manually replace your configuration files accordingly.');
                        }

                        if($hasTodo){
                            self::cli()->writeln('See "/TODO.md" to follow a few manual steps associated with the current version update.');
                        }
                    }
                }

                if(self::$selfHasUpdate){
                    rename(...self::$selfInfo);
                }
            }
        }

        return true;
    }
}