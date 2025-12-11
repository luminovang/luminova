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

if(!defined('APP_ROOT')){
    include_once __DIR__ . '/../../bootstrap/constants.php';
    include_once __DIR__ . '/../../bootstrap/functions.php';
}

use \DirectoryIterator;
use \Luminova\Luminova;
use \Luminova\Command\Terminal;

class Updater
{
    /**
     * Path to the framework directory.
     * 
     * @var string FRAMEWORK_PATH
     */
    public const FRAMEWORK_PATH = 'system/plugins/luminovang/framework/';

    /**
     * List of files to be replaced during updates.
     * 
     * @param array $toReplace
     */
    private static array $toReplace = [];

    /**
     * Self update into.
     * 
     * @var array selfInfo
     */
    private static array $selfInfo = [false, null, null];

    /**
     * List of depreciated modules to remove.
     * 
     * @var array depreciated
     */
    private static array $depreciated = [];

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
        if(self::onInstallAndUpdate('bootstrap/', self::FRAMEWORK_PATH, 'install/Boot/')){
            self::doMove(self::FRAMEWORK_PATH . 'novakit', 'novakit');
            self::updateConfigurations(self::FRAMEWORK_PATH . 'install/App/', 'app/');
            self::updateConfigurations(self::FRAMEWORK_PATH . 'install/Bin/', 'bin/');
            self::updateConfigurations(self::FRAMEWORK_PATH . 'install/Main/', 'samples/Main/', true);
            self::onInstallAndUpdate('system/', self::FRAMEWORK_PATH, 'src/', true);
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
        self::onInstallAndUpdate('system/', self::FRAMEWORK_PATH, 'src/', true);
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
     * Recursively deletes a directory and its contents.
     * 
     * @param string $dir Directory to delete.
     * @param string|null $main Root directory to protect from deletion.
     * 
     * @return void
     */
    public static function cleanup(string $dir, ?string $main = null): void
    {
        $iterator = new DirectoryIterator($dir);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            $path = $item->getPathname();

            if ($item->isDir()) {
                self::cleanup($path, $main);
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
     * Moves application configuration files to the appropriate directories.
     * 
     * @param string $destination Target directory.
     * @param string $sampleFolder Sample directory.
     * @param string $source Source directory.
     * @param bool $main Whether it's a main configuration.
     * 
     * @return void
     */
    private static function updateConfigurations(
        string $destination,
        string $source,
        bool $main = false,
        ?string $samples = null
    ): void 
    {
        if (!file_exists($source)) {
            return;
        }

        self::makeDirectoryIfNotExist($destination);
        $samples ??= 'samples' . DIRECTORY_SEPARATOR . trim($destination, TRIM_DS);

        if (!$main) {
            self::makeDirectoryIfNotExist($samples);
        }

        $iterator = new DirectoryIterator($source);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            $srcPath = $item->getPathname();
            $name    = $item->getFilename();

            $dstPath    = rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $name;
            $samplePath = rtrim($samples, TRIM_DS) . DIRECTORY_SEPARATOR . $name;

            if ($item->isDir()) {
                self::updateConfigurations($srcPath, $dstPath, $main, $samplePath);
                continue;
            }

            self::doConfigCopy($srcPath, $dstPath, $samplePath, $main);
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
            if (!file_exists($sample)) {
                return rename($from, $sample);
            }

            if(self::fileChanged($from, $sample)){
                self::delete($sample);
                self::$toReplace[] = $sample;
                
                return rename($from, $sample);
            }

            return true;
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
    private static function doMove(string $from, string $to): bool
    {
        $isExists = file_exists($to);

        if($isExists && !self::fileChanged($from, $to)){
            return false;
        }

        if($isExists){
            self::delete($to);
        }

        return rename($from, $to);
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
        if (!is_dir($source)) {
            return;
        }

        self::makeDirectoryIfNotExist($destination);
        $iterator = new DirectoryIterator($source);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            $srcPath = $item->getPathname();
            $dstPath = rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $item->getFilename();

            if ($item->isDir()) {
                self::checkAndMoveFolderRecursive($dstPath, $srcPath);
                continue;
            }

            if (self::isUpdater(self::displayPath($dstPath))) {
                self::$selfInfo = [
                    self::fileChanged($srcPath, $dstPath),
                    $srcPath, 
                    $dstPath
                ];
                continue;
            }

            if (!file_exists($dstPath) || self::fileChanged($srcPath, $dstPath)) {

                $dir = dirname($dstPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                rename($srcPath, $dstPath);
            }
        }
    }

    /**
     * Handles the installation and update of framework components.
     * 
     * @param string $destination Target directory.
     * @param string $source Source directory.
     * @param string $subdir Subdirectory to process.
     * @param bool $complete Whether to perform a full installation.
     * 
     * @return bool True if successful, false otherwise.
     */
    private static function onInstallAndUpdate(
        string $destination,
        string $source,
        string $subdir,
        bool $complete = false
    ): bool 
    {
        $fullSource = rtrim($source, TRIM_DS) . DIRECTORY_SEPARATOR . ltrim($subdir, TRIM_DS);

        if (!is_dir($fullSource)) {
            return false;
        }

        self::makeDirectoryIfNotExist($destination);

        $iterator = new DirectoryIterator($fullSource);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            $srcPath = $item->getPathname();
            $name    = $item->getFilename();
            $dstPath = rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $name;

            if ($item->isDir()) {
                self::checkAndMoveFolderRecursive($dstPath, $srcPath);
                continue;
            }

            // Handle self updater
            if (self::isUpdater(self::displayPath($dstPath))) {
                self::$selfInfo = [
                    self::fileChanged($srcPath, $dstPath),
                    $srcPath, 
                    $dstPath
                ];
                continue;
            }

            $isDest = file_exists($dstPath);

            if (!$isDest || self::fileChanged($srcPath, $dstPath)) {
                if ($isDest) {
                    self::delete($dstPath);
                }

                rename($srcPath, $dstPath);
            }
        }

        if ($complete) {
            self::finalizeInstall($source);
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param string $source
     * @return void
     */
    private static function finalizeInstall(string $source): void
    {
        Terminal::init();

        $base = rtrim(APP_ROOT, TRIM_DS) . DIRECTORY_SEPARATOR;

        $todoSource = $base . rtrim($source, TRIM_DS) . DIRECTORY_SEPARATOR . 'TODO.md';
        $todoTarget = $base . 'TODO.md';

        $hasTodo = false;

        if (is_file($todoSource) && self::fileChanged($todoSource, $todoTarget)) {
            if (copy($todoSource, $todoTarget)) {
                self::delete($todoSource);
                $hasTodo = true;
            }
        }

        // Apply self update last
        [$isSelf, $srcFile, $dstFile] = self::$selfInfo;

        if ($isSelf) {
            @rename($srcFile, $dstFile);
        }

        self::cleanup($base . $source, 'framework');

        exec('composer dump-autoload --optimize --no-dev', $output, $code);

        foreach ($output as $line) {
            Terminal::writeln('Dumping:   ' . $line);
        }

        if ($code !== 0) {
            return;
        }

        Terminal::writeln(
            'Update was completed version [' . (Luminova::VERSION ?? '3.7.3') . ']',
            'white',
            'green'
        );

        Terminal::newLine();

        if ($hasTodo || self::$toReplace !== []) {
            Terminal::beeps(2);
            Terminal::writeln('TODO ATTENTION!', 'yellow');

            if (self::$toReplace !== []) {
                Terminal::writeln('See "/samples/*" to manually replace your configuration files accordingly.');
            }

            if ($hasTodo) {
                Terminal::writeln(
                    'See "/TODO.md" to follow a few manual steps associated with the current version update.'
                );
            }
        }
    }
}