<?php
/**
 * Luminova Framework BBBB
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova\Composer;

use \Closure;
use \Exception;
use \ZipArchive;
use \DirectoryIterator;
use \Luminova\Command\Terminal;
use function \Luminova\Funcs\{root, make_dir, display_path};

final class Builder
{  
    /**
     * Project files and directories that are included in every build.
     *
     * Entries must match paths as returned (i.e. with a
     * leading `/`, never a leading `.`).
     *
     * @var string[]
     */
    private static array $includes = [
        '.env',
        '.bootconfig.php',
        'composer.json',
        'bootstrap',
        'public',
        'resources',
        'routes',
        'bin',
        'node',
        'novakit',
        'writeable',
        'libraries',
        'app',

        // Always Last
        'system',
    ];

    private static array $projects = [
        '.env',
        '.dev.env',
        '.bootconfig.php',
        'composer.json',
        'bootstrap',
        'resources',
        'routes',
        'bin',
        'node',
        'novakit',
        'writeable',
        'libraries',
        'app',
        'system/Composer',
        'system/Debugger',
        'system/Boot.php',
        'system/Luminova.php',
        'system/Luminova.php',
        'system/plugins/composer',
        'system/plugins/autoload.php',
        'public',
    ];
    
    /**
     * Paths that are always excluded from a build regardless of
     * `$includes`.
     * 
     * These are from project root not inner dirs 
     *
     * @var string[]
     */
    private static array $ignorable = [
        '/system/plugins/',
        '/writeable/logs',
        '/writeable/temp',
        '/writeable/tmp',
        '/writeable/caches',
        '/writeable/session/',
        '/writeable/.env-cache.php',
        '/system/plugins/luminovang/framework',
        '/phpstan.includes.php',
        '/phpstan.neon',
        '/rector.php',
        '/builds',
        '/docs',
        '/test',
        '/tests',
        '/samples',
        '/command.phar',
        '/composer.lock',
        '/git.ssh',
        '/LICENSE',
    ];

    /**
     * Loose filenames (basename only, matched case-insensitively) that are
     * skipped when encountered anywhere in the source tree.
     *
     * @var string[]
     */
    private static array $skippable = [
        '.ide_helper_views.php',
        '.php-cs-fixer.php',
        'composer.lock',
        'LICENSE',
        'LICENSE.txt',
        'LICENSE.md',
        'README',
        'README.md',
        'README.txt',
        'CVS',
        '.gitignore',
        '.gitattributes',
        '.bzrignore',
        '.bzrtags',
        '.hgignore',
        '.hgtags',
        '.DS_Store',
        '.fleet',
        '.idea',
        '.vscode',
        '.hg',
        '.git',
        '.svn',
        '.zip',
        '.tar',
        '.bzr',
        '.sql',
    ];

    /**
     * Project root 
     * 
     * @var string $root
     */
    private static string $root = '';

    /**
     * Merge runtime CLI options.
     *
     * Overrides default builder options such as verbosity,
     * progress display, and quiet mode.
     *
     * @param array<string,mixed> $options Key-value options to override.
     * @return void
     */
    private static array $options = [
        'progress'  => true,
        'quiet'     => false,
        'verbose'   => 3
    ];

    /**
     * Undocumented function
     *
     * @param array $options
     * @return void
     */
    public static function options(array $options): void 
    {
        self::$options = array_replace(self::$options, $options);
    }

    /**
     * Execute a task, optionally display a completion message and invoke a 
     * completion callback with the task's result.
     *
     * The task callable is invoked exactly once. A "Processing..." indicator is 
     * shown on the same line while work is ongoing, then cleared on completion.
     *
     * @param callable $onStart The task to execute. Its return value is forwarded to `$onComplete`.
     * @param callable|null $onComplete Optional callback invoked after the task 
     *          with the task's return value as its sole argument.
     * @param string|null   $completionMessage  Optional green-coloured success message printed after the task.
     *
     * @return void
     */
    private static function progress(callable $onStart, ?callable $onComplete = null): void 
    {
        if (!self::$options['quiet'] && self::$options['progress']) {
            echo str_pad('  → Processing...', 80) . "\r";
            flush();
        }

        $result = $onStart();

        if (!self::$options['quiet'] && self::$options['progress']) {
            echo str_pad('', 80) . "\r";
        }

        if ($onComplete !== null) {
            $onComplete($result);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $message
     * @param string|null $type
     * @param int $verbosity
     * 
     * @return void
     */
    private static function output(
        string $message,
        ?string $type = null,
        int $verbosity = 0
    ): void 
    {
        if (self::$options['quiet'] && !in_array($type, ['error', 'fail'])) {
            return;
        }

        if (self::$options['verbose'] < $verbosity) {
            return;
        }

        match ($type) {
            'error'   => Terminal::error("✖ {$message}"),
            'success' => Terminal::success("✔ {$message}"),
            'info'    => Terminal::writeln("➜ {$message}", 'cyan'),
            'warn'    => Terminal::writeln("⚠ {$message}", 'yellow'),
            'fail'    => Terminal::writeln("⚠ {$message}", 'red'),
            default   => Terminal::writeln("  {$message}")
        };
    }

    /**
     * Import project file from a source directory to the current project root.
     *
     * @param string $source Relative name of the source directory (e.g. `"."` or `"project"`).
     * @param string|null $destination Relative name of the build root (default: current root).
     * 
     * @return int Status code indicating success or failure of the operation.
     */
    public static function import(string $source, ?string $destination = null): int 
    {
        $source = self::normalize(rtrim($source, TRIM_DS) . DIRECTORY_SEPARATOR);
        $destination ??= root();
        Terminal::init();

        if (!is_dir($source) || $source === $destination) {
            self::output("Invalid source: {$source}", 'error');
            return STATUS_ERROR;
        }

        $ignorable = self::$skippable;

        self::$root = $source;
    
        try{
            self::$skippable = self::importSkippable();
            foreach(self::$projects as $base){
                $entry = self::$root . $base;
                $location = $destination . $base;

                if(is_file($entry)){
                    copy($entry, $location);
                    continue;
                }
                
                if(!is_dir($entry)){
                    continue;
                }

                if (!self::ensureDir($location)) {
                    self::output("Failed to create base dir: '{$location}'", type: 'fail');
                    continue;
                }

                $isSystem = $base === 'system';
                self::output("Import {$base}", 'info', 1);
                self::progress(
                    fn(): array => self::onStart($entry, $location, $isSystem, true),
                    fn(array $result) => self::onComplete($result, $destination, true),
                );
                Terminal::newLine();
            }
        } finally {
            self::$skippable = $ignorable;
        }

        return STATUS_SUCCESS;
    }

    /**
     * Get list of skippable files for import operation.
     *
     * These are typically files that are necessary for build export but
     * should be skipped during import to avoid overwriting existing project files.
     *
     * @return string[] List of skippable (case-insensitive).
     */
    private static function importSkippable(): array
    {
        return [
            'composer.lock',
            'CVS',
            '.gitignore',
            '.gitattributes',
            '.bzrignore',
            '.bzrtags',
            '.hgignore',
            '.hgtags',
            '.DS_Store',
            '.fleet',
            '.idea',
            '.vscode',
            '.hg',
            '.git',
            '.svn',
            '.zip',
            '.tar',
            '.bzr',
            '.sql',
        ];
    }

    /**
     * Copy the production build of the project into `$destinationDir`.
     *
     * Only entries listed in `$includes` are copied; entries listed in
     * `$ignorable` or `$skippable` are skipped.  After the
     * copy, `composer install --no-dev` and `composer dump-autoload` are
     * executed inside the destination directory and the environment is set to
     * `production`.
     *
     * @param  string $destination Relative name of the build root (default: `"build"`).
     * @return void
     */
    public static function export(string $destination = 'build'): int
    {
        Terminal::init();
        self::upgrade();

        // Prevent the destination directory from being included in its own build.
        self::$ignorable[] = $destination;
        $destination = root("{$destination}/v-" . APP_VERSION);
        self::$root = root();

        if (!make_dir($destination, 0755, true)) {
            self::output("Create failed: {$destination}", 'error');
            return STATUS_ERROR;
        }

        self::output('Start build', 'info');

        foreach(self::$includes as $base){
            $entry = self::$root . $base;
            $location = $destination . $base;

            if(is_file($entry)){
                copy($entry, $location);
                continue;
            }
            
            if(!is_dir($entry)){
                continue;
            }

            if (!self::ensureDir($location)) {
                self::output("Failed to create base dir: '{$location}'", type: 'fail');
                continue;
            }

            $isSystem = $base === 'system';
            self::output("Building: {$base}", 'info', 1);
            self::progress(
                fn(): array => self::onStart($entry, $location, $isSystem),
                fn(array $result) => self::onComplete($result, $destination)
            );
            Terminal::newLine();
        }

        return STATUS_SUCCESS;
    }

    /**
     * Package the project's production files into a ZIP archive.
     *
     * Only entries listed in `$includes` are archived; entries listed in
     * `$ignorable` or `$skippable` are skipped.
     *
     * @param  string $filename Name of the ZIP file to create (e.g. `"project.zip"`).
     * @param  string $destination Relative name of the directory that holds the archive (default: `"builds"`).
     * 
     * @return int
     */
    public static function archive(string $filename, string $destination = 'builds'): int
    {
        Terminal::init();
        self::upgrade();

        self::$ignorable[] = $filename;
        self::$root = root();
        $destination = root("{$destination}/v-" . APP_VERSION);

        if (!make_dir($destination, 0755, true)) {
            self::output("Error: failed to create directory: '{$destination}'.", 'error');
            return STATUS_ERROR;
        }

        $filename = $destination . ltrim($filename, TRIM_DS);
        $zip = new ZipArchive();

        try {
            if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                self::output('Error: could not create the archive file.', 'error');
                return STATUS_ERROR;
            }

            self::output('Creating archive...', 'info');
            [$status, $added, $skipped, $failed] = self::each(self::$root, '',  
                function(bool $isDir, string $source, string $dest) use ($zip) {
                    static $tick = 0;

                    if (($tick++ % 50) === 0) {
                        Terminal::spinner(sleep: 1000);
                    }

                    if ($isDir) {
                        return $zip->addEmptyDir(rtrim($dest, TRIM_DS));
                    }

                    if($zip->addFile($source, ltrim($dest, TRIM_DS))){
                        self::output("Added: {$source}", null, 3);
                        return true;
                    }

                    self::output("Failed: {$source}", 'fail', 3);
                    return false;
                }
            );

            $zip->close();

            self::output(sprintf(
                "%d done • %d skipped • %d failed",
                $added,
                $skipped,
                $failed
            ), $failed ? 'warn' : 'success');

            self::output('Archive ready', 'success');
            self::output("File: " . display_path($filename));

            return STATUS_SUCCESS;
        } catch (Exception $e) {
            self::output($e->getMessage(), 'error');
        }

        return STATUS_ERROR;
    }

    /**
     * Run composer optimization before build.
     *
     * Prompts user to remove dev dependencies and optimize autoload.
     * Falls back to cleanup when composer fails.
     *
     * @return void
     */
    private static function upgrade(): void 
    {
        $optimize = Terminal::prompt(
            'Do you want to remove dev modules and optimize composer autoload first?',
            ['green' => 'y', 'red' => 'n']
        );

        if($optimize === 'NO'){
            return;
        }

        @chdir(APP_ROOT);
        self::output('Removing dev modules...', 'info');
        $cmd = sprintf(
            '%s composer install --no-dev --optimize-autoloader',
            strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                ? 'set LM_DEBUG_MODE=1 &&'
                : 'LM_DEBUG_MODE=1'
        );

        exec($cmd . ' 2>&1', $output, $exit);
        if ($exit === 0) {
            foreach ($output as $line) {
                self::output($line, null, 2);
            }
            return;
        }

        $base = self::normalize(
            APP_ROOT 
            . DIRECTORY_SEPARATOR 
            . Updater::FRAMEWORK_PATH
        );

        Updater::cleanup($base, 'framework');
        self::output('Failed to remove dav modules', 'fail');
    }

    /**
     * Recursively iterate over source directory.
     *
     * Applies handler to each file and directory while tracking
     * added, skipped, and failed counts.
     *
     * @param string  $source      Source path.
     * @param string  $destination Destination path.
     * @param Closure $handler     Callback for processing entries.
     *
     * @return array{bool,int,int,int} [status, added, skipped, failed]
     */
    private static function each(string $source, string $destination, Closure $handler): array
    {
        $added = $skipped = $failed = 0;

        if (!is_dir($source) || !is_readable($source)) {
            return [false, 0, 0, 1];
        }

        $iterator = new DirectoryIterator($source);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            $sourcePath = $item->getPathname();

            // RELATIVE PATH FROM ROOT (single source of truth)
            $relativePath = self::normalize(str_replace(self::$root, '', $sourcePath));
            $relativePath = '/' . ltrim($relativePath, TRIM_DS);

            $destPath = self::normalize($destination
                ? rtrim($destination, TRIM_DS) . DIRECTORY_SEPARATOR . $item->getFilename()
                : ltrim($relativePath, TRIM_DS) // for zip
            ); 

            $basename = $item->getFilename();

            if (self::ignore($relativePath) || self::skip($basename)) {
                $skipped++;
                continue;
            }

            if ($item->isDir()) {
                if (!self::allow($relativePath)) {
                    $skipped++;
                    continue;
                }

                if (!$handler(true, $sourcePath, $destPath, $basename)) {
                    $failed++;
                    continue;
                }

                [$a, $s, $f] = self::each($sourcePath, $destPath, $handler);

                $added   += $a;
                $skipped += $s;
                $failed  += $f;

                continue;
            }

            if ($handler(false, $sourcePath, $destPath, $basename)) {
                $added++;
                continue;
            }

            $failed++;
        }

        return [true, $added, $skipped, $failed];
    }

    /**
     * Process directory copy/import operation.
     *
     * Traverses source directory and copies allowed files
     * to destination while tracking statistics.
     *
     * @param string $entry     Source directory.
     * @param string $location  Destination directory.
     * @param bool   $isSystem  Whether processing system directory.
     * @param bool   $isImport  Whether running import mode.
     *
     * @return array{string,int,int,int,bool}
     *         [destination, added, skipped, failed, isSystem]
     */
    private static function onStart($entry, $location, $isSystem, bool $isImport = false) : array 
    {
        [$status, $added, $skipped, $failed] = self::each(
            $entry, 
            $location, 
            function(bool $isDir, string $source, string $dest) use($isImport) {
                Terminal::spinner(sleep: 10000);
                
                if ($isDir) {
                    return self::ensureDir($dest);
                }

                if (!self::ensureDir(dirname($dest))) {
                    return false;
                }

                if(copy($source, $dest)){
                    self::output(sprintf(
                        '%s: %s', $isImport ? 'Import' : 'Copy', 
                        $source
                    ), null, 3);
                    return true;
                }

                self::output("Failed: {$source}", 'fail', 3);
                return false;
            }
        );

        if ($status === false) {
            self::output($isImport ? 'failed to import' : 'failed to copy', type: 'fail');
        }

        return [$location, $added, $skipped, $failed, $isSystem];
    }

    /**
     * Ensure a directory exists, creating it if necessary.
     *
     * @param string $dir Path of the directory to ensure.
     * @return bool True if the directory exists or was created successfully, false on failure.
     */
    private static function ensureDir(string $dir): bool
    {
        return is_dir($dir) || mkdir($dir, 0755, true);
    }

    private static function normalize(string $path): string
    {
        return str_replace(['\\', '/'], '/', $path);
    }

    /**
     * Handle completion of a copy/import operation.
     *
     * Outputs summary and runs post-build optimization
     * when processing system directory.
     *
     * @param array  $result       Result from onStart().
     * @param string $destination  Build destination path.
     * @param bool   $isImport     Whether running import mode.
     *
     * @return void
     */
    private static function onComplete(array $result, $destination, bool $isImport = false) : void 
    {
        [$dir, $added, $skipped, $failed, $isSystem] = $result;
        self::output(sprintf(
            "%d %s • %d skipped • %d failed",
            $added,
            $isImport ? 'imported' : 'copied',
            $skipped,
            $failed
        ), $failed ? 'warn' : 'info');

        if (!$isImport && $isSystem) {
            $cwd = @chdir($destination);

            if ($cwd) {
                self::output('Optimize autoload', 'info');
                $cmd = sprintf(
                    'cd %s && %s composer dump-autoload --optimize --no-dev',
                    escapeshellarg($destination),
                    strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                        ? 'set LM_DEBUG_MODE=1 &&'
                        : 'LM_DEBUG_MODE=1'
                );

                exec($cmd . ' 2>&1', $output, $exit);
                if ($exit === 0) {
                    foreach ($output as $line) {
                        self::output($line, null, 2);
                    }
                } else {
                    self::output('Autoload optimization failed', 'error');
                }
            } else {
                self::output('Failed to change directory for optimization', 'fail');
                return;
            }

            self::output('Build completed', 'success');
            self::output("Output: {$destination}");
        }
    }

    /**
     * Return true when `$path` matches an entry in `$includes` or is a
     * child of one of those entries.
     *
     * @param string $path Path to test (raw, may have a leading `.`).
     * 
     * @return bool
     */
    private static function allow(string $path): bool
    {
        $path = trim($path, '/');
        $segment = explode('/', $path)[0] ?? '';

        return in_array($segment, self::$includes, true);
    }

    /**
     * Return true when `$path` matches an entry in `$ignorable` and
     * should therefore be excluded from the build.
     *
     * @param string $path Path to test (raw, may have a leading `.`).
     * 
     * @return bool
     */
    private static function ignore(string $path): bool
    {
        $path = '/' . trim($path, TRIM_DS);

        foreach (self::$ignorable as $ignore) {
            $ignore = '/' . trim($ignore, '/');

            if (
                $path === $ignore
                || str_starts_with($path, $ignore)
                || fnmatch($ignore, $path)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true when `$path` (a basename) matches an entry in
     * `$skippable` (matched case-insensitively).
     *
     * @param string $path Basename of the file to test.
     * 
     * @return bool
     */
    private static function skip(string $name): bool
    {
        $name = strtolower($name);

        foreach (self::$skippable as $skip) {
            $skip = strtolower($skip);

            if ($name === $skip || fnmatch($skip, $name)) {
                return true;
            }
        }

        return false;
    }
}