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

use \Luminova\Composer\BaseComposer;
use \Luminova\Command\Terminal;
use \ZipArchive;
use \Exception;

class Builder extends BaseComposer
{  
    /**
     * @var Terminal $terminal 
    */
    private static ?Terminal $terminal = null;

    /**
     * Project files 
     * @var array
    */
    private static $projectFiles = [
        "/app",
        "/bootstrap",
        "/system",
        "/public",
        "/resources",
        "/writeable",
        "/libraries",
        "/routes",
        ".env",
        ".gitkeep",
        "composer.json"
    ];
    
    private static $systemIgnoreFiles = [
        "/system/log",
        "/writeable/caches",
        "/phpstan.includes.php",
        "/phpstan.neon",
        "/novakit",
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

    /**
     * Get prepared cli instance 
     * 
     * @return Terminal 
    */
    private static function terminal(): Terminal
    {
        return static::$terminal ??= new Terminal();
    }


    public static function export(string $destinationDir = "build"): void
    {
        static::$systemIgnoreFiles[] = $destinationDir;
        $destinationDir = APP_ROOT . DIRECTORY_SEPARATOR . $destinationDir . DIRECTORY_SEPARATOR . 'v-' . APP_VERSION;

        if (!is_dir($destinationDir)) {
            make_dir($destinationDir, 0755, true);
        }

        parent::progress(10, function ($step) use ($destinationDir) {
            [$added, $skipped, $failed] = static::makeCopy('.', $destinationDir);

            return $destinationDir;
        }, 
        function($dir, $results){
            if (chdir($dir)) {
                $project_link = "http://" . strtolower(gethostname());
                $project_link .= "/" . basename(dirname(__DIR__, 2));
                $project_link .= "/" . $dir . "/public";

                exec('LM_DEBUG_MODE=1 composer install --no-dev', $output, $returnCode);
                static::terminal()->writeln("Cleaning and updating project dependency...");
                foreach ($output as $line) {
                    echo $line . "\n";
                }
            
                if ($returnCode === 0) {
                    exec('LM_DEBUG_MODE=1 composer dump-autoload --optimize --no-dev');
                    static::terminal()->writeln("Dumping project development files...");
                    foreach ($output as $line) {
                        echo $line . "\n";
                    }
         
                    static::terminal()->writeln("Updating environment variables...");
                    setenv('app.environment.mood', 'production', true);

                    static::terminal()->writeln("Project build completed successfully.");
                    static::terminal()->writeln("To view your project, click the below link:");
                    static::terminal()->writeln("\033[34m" . $project_link . "\033[0m\n");

                } else {
                    static::terminal()->error("Fail to build project failed"); 
                }
            } else {
                $error = "\033[31mFailed to change to the build production directory.\033[0m\n";
                $error .= "To build the project, please follow these steps:\n";
                $error .= "1. Navigate to the directory: $dir\n";
                $error .= "2. Run the following commands:\n";
                $error .= "   - \033[32mLM_DEBUG_MODE=1 composer install --no-dev\033[0m\n";
                $error .= "   - \033[32mLM_DEBUG_MODE=1 composer dump-autoload --optimize --no-dev\033[0m";

                static::terminal()->writeln($error);
                exit(1);
    
            } 
        },
        "Project production files copied to: $destinationDir");   
        //setenv('app.environment.mood', 'production', true);               
    }

    public static function archive(string $zipFileName, string $buildDir = "builds"): void
    {
        static::$systemIgnoreFiles[] = $zipFileName;
        $zip = new ZipArchive();
        $buildDir = APP_ROOT . DIRECTORY_SEPARATOR . $buildDir . DIRECTORY_SEPARATOR . 'v-' . APP_VERSION;
        $buildFile = $buildDir . DIRECTORY_SEPARATOR . $zipFileName;
   
        try {
            if(!is_dir($buildDir)){
                make_dir($buildDir, 0755, true); 
            }
         
            if ($zip->open($buildFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                static::terminal()->error('Error creating project archive file');

                exit(1);
            }

            static::terminal()->writeln("Creating a zip archive for the project...");
            [$added, $skipped] = static::addToZip($zip, APP_ROOT . DIRECTORY_SEPARATOR . '.', '');
            static::terminal()->writeln("Archiving project...");
            $zip->close();

            static::terminal()->writeln("Project archive exported successfully");
            static::terminal()->writeln("Build path: \033[34m" . filter_paths($buildFile) . "\033[0m\n");
            static::terminal()->writeln($added . ' Files was added', 'green');
            static::terminal()->writeln($skipped . ' Files was skipped', 'yellow');
            exit(0);
        } catch (Exception $e) {
            static::terminal()->error("Error: " . $e->getMessage());
        }

        exit(1);
    }

    private static function addToZip(ZipArchive $zip, string $folder, string $zipFolder): array
    {
        $added = 0;
        $skipped = 0;
        $files = scandir($folder);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $folder . DIRECTORY_SEPARATOR . $file;
            $relativePath = $zipFolder . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                if (static::allow($relativePath) && !static::ignore($relativePath)) {
                    $zip->addEmptyDir($relativePath);
                    static::addToZip($zip, $filePath, $relativePath);
                    $added++;
                } else {
                    $skipped++;
                }
            } else {
                if (!static::ignore($relativePath) && !static::skip(basename($relativePath))) {
                    $zip->addFile($filePath, $relativePath);
                    $added++;
                } else {
                    $skipped++;
                }
            }
        }

        return [$added, $skipped];
    }

    private static function makeCopy(string $source, string $destination): array
    {
        $added = 0;
        $skipped = 0;
        $failed = 0;
        $files = scandir($source);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $destination . DIRECTORY_SEPARATOR . $file;

            if (is_dir($sourcePath)) {
                if (static::allow($sourcePath) && !static::ignore($sourcePath)) {
                    if (!is_dir($destinationPath)) {
                        if(mkdir($destinationPath, 0755, true)){
                            $added++;
                        }else{
                            $failed++; 
                        }
                    }else{
                        $added++;
                    }

                    [$added, $skipped, $failed] = static::makeCopy($sourcePath, $destinationPath);
                }else{
                    $skipped++;
                }
            } else {
                if (!static::ignore($sourcePath) && !static::skip(basename($sourcePath))) {
                    if(copy($sourcePath, $destinationPath)){
                        $added++;
                    }else{
                        $failed++;
                    }
                }else{
                    $skipped++;
                }
            }
        }

        return [$added, $skipped, $failed];
    }
    

    private static function allow(string $path): bool
    {
        foreach (static::$projectFiles as $projectFile) {
            if (fnmatch($projectFile, parent::parseLocation($path)) || parent::isParentOrEqual($projectFile, parent::parseLocation($path)) ) {
               return true;
            }
        }
        return false;
    }

    private static function ignore(string $path): bool
    {

        foreach (static::$systemIgnoreFiles as $ignoreFile) {
            if (fnmatch($ignoreFile, parent::parseLocation($path))) {
                return true;
            }
        }
        return false;
    }

    private static function skip(string $path): bool
    {
        foreach (static::$projectIgnoreFiles as $skipFile) {
            if (fnmatch(strtolower($skipFile), strtolower(parent::parseLocation($path)))) {
                return true;
            }
        }
        return false;
    }
}