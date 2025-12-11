<?php 
declare(strict_types=1);
/**
 * Luminova Framework Autoloder.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova;

use \App\Kernel;
use \Throwable;
use Luminova\Runtime;
use Luminova\Luminova;
use Luminova\Config\Env;
use Luminova\Http\Header;
use Luminova\Logger\Entry;
use Luminova\Routing\Router;
use Luminova\Cache\ViewCache;
use Luminova\Utility\Version;
use Luminova\Command\Terminal;
use Luminova\Foundation\Error\Error;
use Luminova\Foundation\Module\Factory;
use Luminova\Foundation\Core\Application;
use Luminova\Foundation\Module\Autoloader;
use Luminova\Promise\{Rejected, Fulfilled};
use Luminova\Exceptions\{RuntimeException, InvalidArgumentException};

/**
 * Luminova framework bootstrapping and autoloading helper.
 *
 * This class provides static methods for initializing the Luminova framework
 * and resolving its autoloading environment. It supports Composer and Luminova
 * VCI autoloading without requiring applications to manually include
 * `vendor/autoload.php` or other framework bootstrap files.
 *
 * Use the appropriate boot method for the application environment:
 *
 * - {@see Boot::http()}     Bootstrap the application for HTTP requests.
 * - {@see Boot::cli()}      Bootstrap the application for CLI commands.
 * - {@see Boot::autoload()} Resolve and initialize the application autoloader.
 *
 * @see https://luminova.ng/docs/0.0.0/boot/autoload
 */
final class Boot
{
    /**
     * Luminova VCI configuration loaded.
     *
     * @var array<string,mixed>|null $vciConfig
     * 
     * @see ../.vci.php
     */
    private static ?array $vciConfig = null;

    /**
     * Class autoload mapping.
     *
     * @var array<string,string> classes
     */
    private static array $classes = [];

    /**
     * Prevent initialization.
     */
    private function __construct() {}

    /**
     * Initialize the application for HTTP requests.
     *
     * Warms up the framework, registers error handlers, completes the boot
     * process, and returns the application instance for further configuration
     * and execution.
     *
     * This method is intended for web and API entry points, typically
     * `public/index.php`.
     *
     * @return Application|\App\Application<Application> The application instance.
     *
     * @example - Usage (public/index.php):
     * ```php
     * use Luminova\Boot;
     *
     * require_once __DIR__ . '/../system/Boot.php';
     *
     * Boot::http()->router->context(...)->run();
     * ```
     *
     * @see self::init()
     * @see self::cli()
     * @see ../public/index.php
     */
    public static function http(): Application
    {
        self::init();

        if (!PRODUCTION && !STAGING && !Runtime::isLocalhost()) {
            exit(
                'Invalid environment mode for production. '
                . 'Set "app.environment.mood=production" or "staging".'
            );
        }

        return Kernel::resolve(Kernel::SERVICE_APPLICATION, true);
    }

    /**
     * Initialize the framework without configuring an HTTP or CLI environment.
     *
     * Loads the required framework modules by running the warmup and completion
     * phases of the boot process. Error handlers and CLI-specific configuration
     * are not initialized.
     *
     * Use this method when Composer and framework modules need to be available
     * without starting a complete application environment.
     *
     * @return void
     *
     * @example - Usage:
     * ```php
     * use Luminova\Boot;
     *
     * require_once __DIR__ . '/system/Boot.php';
     *
     * Boot::autoload();
     * ```
     *
     * @see self::init()
     * @see self::http()
     * @see self::cli()
     */
    public static function autoload(): void
    {
        self::warmup();
        self::finish();
    }

    /**
     * Initialize the HTTP boot environment.
     *
     * Warms up the framework, registers the application error handlers, and
     * completes the boot process. Unlike {@see self::http()}, this method does
     * not create or return the application instance.
     *
     * @return void
     *
     * @example - Usage:
     * ```php
     * use Luminova\Boot;
     *
     * require_once __DIR__ . '/system/Boot.php';
     *
     * Boot::init();
     * ```
     *
     * @see self::http()
     * @see self::autoload()
     */
    public static function init(): void
    {
        self::warmup();
        Error::register();
        self::finish();
    }

    /**
     * Initialize the CLI environment.
     *
     * Validates that the application is running under PHP CLI, loads the required
     * framework modules, enables CLI-friendly error reporting, defines the CLI
     * environment, initializes the standard CLI streams, and completes the boot
     * process.
     *
     * This method is intended for custom CLI entry points and scripts.
     *
     * @return void
     *
     * @example - Usage (/bin/script.php):
     * ```php
     * #!/usr/bin/env php
     * <?php
     *
     * use Luminova\Boot;
     *
     * require __DIR__ . '/system/Boot.php';
     *
     * Boot::cli();
     *
     * // Your CLI implementation.
     * ```
     *
     * @see ../novakit
     * @see self::autoload()
     */
    public static function cli(): void
    {
        if (PHP_SAPI !== 'cli') {
            echo sprintf(
                'Boot::cli() requires php-cli. %s is not supported. Use Boot::http() instead.',
                PHP_SAPI
            );
            exit(1);
        }

        self::warmup();

        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        ini_set('log_errors', '1');
        ini_set('html_errors', '0');

        error_reporting(E_ALL);

        defined('CLI_ENVIRONMENT')
            || define('CLI_ENVIRONMENT', Env::get('cli.environment.mood', 'testing'));

        self::defineCliStreams();
        self::finish();
    }

    /**
     * Get the shared application instance.
     *
     * @return Application|\App\Application<Application> Returns the application instance.
     * 
     * @deprecated
     */
    public static function application(): Application
    {
        return Kernel::resolve(Kernel::SERVICE_APPLICATION, true);
    }

    /**
     * Loads core modules and prepares the application environment.
     *
     * Ensures constants, functions, error handlers, and the core framework 
     * are loaded in the correct order. Also applies HTTP method spoofing early 
     * to ensure routing uses the correct request method.
     *
     * @return void
     * @ignore
     * @codeCoverageIgnore
     */
    public static function warmup(): void
    {
        defined('APP_BASE_PATH') 
            || define('APP_BASE_PATH', $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__));

        if (defined('BOOT_WARMUP_STARTED')) {
            self::override();
            return;
        }

        self::tryIncludeModule('autoload.php', 'package', false);
        self::resolveVciConfig();

        self::tryIncludeModule('polyfills.php', 'bootstrap');
        self::tryIncludeModule('Luminova.php', 'system');
        self::tryIncludeModule('Runtime.php', 'system');
        self::tryIncludeModule('Config/Env.php', 'system');

        /**
         * Register environment variables from a `.env` file.
         */
        try{
            Env::register();
        } catch (Throwable $e) {
            self::onError(sprintf(
                "Runtime error: Failed to parse environment configuration.%s %s",
                Runtime::isLocalhost() ? '' : ((PHP_SAPI === 'cli') ? "\n\n" : '<br/><br/>'),
                $e->getMessage()
            ));
        }

        self::tryIncludeModule('constants.php', 'bootstrap');
        self::override();

        self::tryIncludeModule('Funcs/functions.php', 'bootstrap');
        self::tryIncludeModule('Funcs/strings.php', 'bootstrap');
        self::tryIncludeModule('Funcs/arrays.php', 'bootstrap');

        if(self::isAutoloadResolver(['luminova', 'auto'])){
            self::tryIncludeModule('Exceptions/ErrorCode.php', 'system');
            self::tryIncludeModule('Foundation/Error/Error.php', 'system');
            self::tryIncludeModule('Foundation/Error/Message.php', 'system');
        }

        self::setAppDefaults();

        define('BOOT_WARMUP_STARTED', true);

        // start recording
        Runtime::profiling('start');
    }

    /**
     * Opens a file using `fopen()` with exception handling.
     *
     * If the file can't be opened or an error occurs, a RuntimeException is thrown.
     *
     * @param string $filename Path to the file.
     * @param string $mode File access mode (e.g., 'r', 'w').
     *
     * @return resource|null Return a valid stream resource.
     * @throws RuntimeException If the file can't be opened.
     */
    private static function tryFopen(string $filename, string $mode): mixed
    {
        $error = null;
        $handle = null;

        try {
            $handle = fopen($filename, $mode);
        } catch (Throwable $e) {
            $error = $e;
        }

        if ($handle === false || !is_resource($handle)) {
            throw new RuntimeException(sprintf(
                'Failed to open file "%s" with mode "%s"%s',
                $filename,
                $mode,
                $error ? ': ' . $error->getMessage() : ''
            ), previous: $error);
        }

        return $handle;
    }

    /**
     * Ensure the standard CLI streams are defined.
     *
     * Defines `STDIN`, `STDOUT`, and `STDERR` when they are not already available.
     * Each stream is opened using `tryFopen()`.
     *
     * @return void
     *
     * @throws RuntimeException If a required stream cannot be opened.
     */
    public static function defineCliStreams(): void
    {
        /**
         * Standard input stream.
         *
         * @var resource
         */
        defined('STDIN') 
            || define('STDIN', self::tryFopen('php://stdin', 'r'));

        /**
         * Standard output stream.
         *
         * @var resource
         */
        defined('STDOUT') 
            || define('STDOUT', self::tryFopen('php://stdout', 'w'));

        /**
         * Standard error stream.
         *
         * @var resource
         */
        defined('STDERR') 
            || define('STDERR', self::tryFopen('php://stderr', 'w'));
    }

    /**
     * Determine whether Luminova VCI autoloading is enabled.
     *
     * Loads the VCI configuration and verifies that a valid configuration
     * is available. Returns `false` if the configuration cannot be loaded.
     *
     * @return bool `true` if VCI autoloading is enabled, otherwise `false`.
     * 
     * @see ../.vci.php
     */
    public static function isVciAutoload(): bool
    {
        try {
            self::resolveVciConfig(false);

            return !empty(self::$vciConfig);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Determine whether the application autoloader matches the specified resolver.
     *
     * Resolvers:
     * - `composer` - Composer autoloader, with or without Luminova VCI.
     * - `luminova` - Luminova VCI autoloader.
     * - `auto`     - Automatically resolve the autoloader through Luminova VCI.
     *
     * If VCI configuration is unavailable, `composer` is assumed.
     *
     * @param string[]|string $resolver Autoloader resolver name(s), such as
     *                                   `composer`, `luminova`, or `auto`.
     *
     * @return bool `true` if the configured autoloader matches any specified
     *              resolver, otherwise `false`.
     */
    public static function isAutoloadResolver(array|string $resolver): bool
    {
        $autoloader = isset(self::$vciConfig) 
            ? (self::$vciConfig['resolve.autoloader'] ?? 'auto')
            : 'composer';

        return in_array(
            $autoloader,
            (array) $resolver,
            true
        );
    }

    /**
     * Import a PHP file using a physical path or virtual path scheme.
     *
     * Supports framework virtual path aliases and direct paths. Imported files are loaded once
     * by default using require_once.
     *
     * Available schemes:
     *
     * - `app`        → `root/app/*`
     * - `package`    → `root/system/plugins/*`
     * - `system`     → `root/system/*`
     * - `view`       → `root/resources/Views/*`
     * - `public`     → `root/public/*`
     * - `writeable`  → `root/writeable/*`
     * - `libraries`  → `root/libraries/*`
     * - `resources`  → `root/resources/*`
     * - `routes`     → `root/routes/*`
     * - `bootstrap`  → `root/bootstrap/*`
     * - `bin`        → `root/bin/*`
     * - `node`       → `root/node/*`
     *
     * The imported file may receive variables through `$scope`. Array keys are
     * extracted as local variables inside the imported file.
     *
     * @param string $path File path or scheme path to import.
     * @param bool $throw Throw an exception when the file does not exist (default: `true`).
     * @param bool $once Load the file only once using `*_once` (default: `true`).
     * @param bool $useRequire Use `require` instead of `include` (default: `false`).
     * @param array<string,mixed> $scope Variables to expose inside the imported file.
     * @param bool $promise Return a promise result instead of the raw value (default: `false`).
     *
     * @return \Luminova\Interface\PromiseInterface|mixed|null Imported file return value, promise result,
     *                                    or null when file is missing.
     *
     * @throws RuntimeException If file does not exist and `$throw` is enabled.
     * @throws InvalidArgumentException If `$scope` is a list array.
     * 
     * @see \Luminova\Funcs\import() A global helper function.
     *
     * @example - Examples:
     * ```php
     * Boot::import('app:Config/settings.php');
     * Boot::import(__DIR__ . '/app/Config/settings.php');
     * 
     * Boot::import('package:brick/math/src/BigNumber.php');
     * Boot::import('routes:api.php', once: false);
     * Boot::import('system:Bootstrap/init.php', require: false);
     * ```
     * 
     * @example - Promise Example:
     * ```php
     * Boot::import('app:Config/settings.php', promise: true)
     *      ->then(function(mixed $settings){
     *          echo $settings['name'];
     *      })->catch(function(Throwable $e){
     *          echo $e->getMessage()
     *      });
     * ```
     */
    public static function import(
        string $path,
        bool $throw = true,
        bool $once = true,
        bool $useRequire = false,
        array $scope = [],
        bool $promise = false
    ): mixed
    {
        if (preg_match('/^([a-z]+):(.+)$/i', $path, $match)) {
            $scheme = strtolower($match[1]);

            $path = match ($scheme) {
                'system',
                'package',
                'bootstrap' => self::resolve($match[2], $scheme),
                default     => in_array($scheme, Luminova::SYSTEM_PATHS, true)
                    ? Luminova::root($scheme, $match[2])
                    : $path,
            };
        }

        if (!is_file($path)) {
            if (!$throw) {
                return null;
            }

            $e = new RuntimeException(
                "Unable to import file: {$path} does not exist."
            );

            if(!$promise){
                throw $e;
            }

            return new Rejected($e);
        }

        return self::resolveImport(
            $path,
            $scope,
            $once,
            $useRequire,
            $promise
        );
    }

    /**
     * Resolve file based on `.vci.php` boot configuration directory.
     * 
     * This method attempts to resolve a specified file from the boot directory 
     * or the default base directory.
     * 
     * If the file is not found and `null` is true.
     *
     * @param string $file The file pathname.
     * @param string $from The boot directory to load from 
     *                     (e.g., 'system', 'bootstrap', 'package' or `root`).
     * 
     * @return string|null Return full resolved file path.
     */
    private static function resolve(string $file, string $from = 'system'): ?string
    {
        if(
            self::isVciAutoload()
            && !str_ends_with($file, $from  . '/Boot.php')
            && isset(self::$vciConfig['luminova.paths']['target'])
        ){
            $base = self::toPath(self::$vciConfig['luminova.paths']['target']);
            $root = match($from){
                'system'    => $base . 'system/',
                'bootstrap' => $base . 'bootstrap/',
                default     => $base
            };
        } else{
            $root = Luminova::root(match($from){
                'system'    =>  '/system/',
                'bootstrap' =>  '/bootstrap/',
                'package'   =>  '/system/plugins/',
                default     => '/'
            });
        }

        $filepath = $root . ltrim($file, '/');

        if (is_file($filepath)) {
            return $filepath;
        }

        return null;
    }

    /**
     * Normalize a path and append a trailing suffix.
     *
     * Converts backslashes to forward slashes, trims any trailing slashes,
     * and appends the given suffix (e.g., '/' for directories or '.php' for files).
     *
     * Notes:
     * - Does not validate path existence.
     * - Always forces a single trailing suffix.
     *
     * @param string $path Input path (namespace or filesystem path).
     * @param string $suffix Suffix to append (default: '/').
     *
     * @return string Normalized path with suffix applied.
     * @example
     * 
     * ```php
     * Boot::toPath('App\\Core')            // App/Core/
     * Boot::toPath('App\\Core', '.php')    // App/Core.php
     * Boot::toPath('/var/www/', '/')       // /var/www/
     * ```
     */
    private static function toPath(string $path, string $suffix = '/'): string
    {
        return rtrim(str_replace('\\', '/', $path), '/') . $suffix;
    }

    /**
     * Handle boot error response based on environment context.
     *
     * In development mode, this method throws an exception for easier debugging.
     * In production mode, it renders a safe output depending on runtime context:
     * - CLI: writes error to STDERR and exits
     * - HTTP: returns a generic HTML error response
     *
     * @param string $message Error message to display or log.
     * @param bool|null $isProduction Manually override production mode detection.
     * 
     * @return never
     * @throws \RuntimeException When not in production mode.
     */
    private static function onError(string $message, ?bool $isProduction = null): never
    {
        $isProduction ??= self::isProduction();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!$isProduction && class_exists(Error::class)) {
            throw new \RuntimeException($message);
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDERR, strip_tags(
                str_replace(['<br/>', '<br>'], PHP_EOL, $message)
            ));

            exit(1);
        }

        http_response_code(500);

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        }

        $title = 'Application Error';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $file = APP_BASE_PATH . '/app/Errors/Defaults/xxx.php';

        if(is_file($file)){
            $description = 'Boot Runtime Error';
            include_once $file;
            exit(1);
        }

        echo '<style>body{margin:0;padding:40px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f6f7f9;color:#333;}.error{max-width:600px;margin:0 auto;background:#fff;border:1px solid #ddd;padding:20px;border-radius:6px;}h1{margin-top:0;font-size:20px;color:#c0392b;}p{margin:10px 0 0;}</style>';

        echo '<div class="error">
            <h1>' . $title . '</h1>
            <p>' . $message . '</p>
        </div>';

        exit(1);
    }

    /**
     * Register class aliases from configuration.
     *
     * Loads alias definitions from `/app/Config/Modules.php` and registers
     * them using PHP `class_alias()`.
     *
     * @param bool $autoload Whether to allow autoloading of target classes.
     * @param int &$registered The number of registered class aliases passed by reference.
     * 
     * @return bool True if at least one alias was successfully registered.
     */
    private static function registerAliases(bool $autoload = true, int &$registered = 0): bool
    {
        static $modules = null;
        $registered = 0;

        if (Env::get('feature.app.class.alias', false)) {
            $modules ??= self::tryIncludeModule(
                'Config/Modules.php', 
                from: 'app', 
                shared: false, 
                return: true
            );
        }

        if(!$modules){
            return false;
        }

        $aliases = $modules['alias'] ?? null;

        if (!$aliases || !is_array($aliases)) {
            return false;
        }

        $entry = new Entry('warning');

        foreach ($aliases as $alias => $namespace) {
            if (class_alias($namespace, $alias, $autoload)) {
                $registered++;
                continue;
            }

            $entry->add(sprintf(
                'Failed to register alias [%s] for class [%s]',
                $alias,
                $namespace
            ));
        }

        if (!$entry->isEmpty()) {
            try{
                $entry->log();
            } catch(Throwable) {}
        }

        return $registered > 0;
    }

    /**
     * Finalizes the bootstrapping process.
     *
     * Loads composer module and custom framework feature. 
     * Sets `APP_BOOTED` constant if not defined.
     *
     * @return void
     */
    private static function finish(): void
    {
        //self::tryIncludeModule('autoload.php', 'package', false);
        self::tryVciAutoload();
        self::features();

        defined('APP_BOOTED') || define('APP_BOOTED', true);

        // If application is undergoing maintenance.
        if(MAINTENANCE){
            self::maintenance();
        }else if(!Runtime::isCommand() && self::cache()){
            // If the view uri ends with `.extension`, 
            // then try serving the cached static version.
            exit(STATUS_SUCCESS);
        }
    }

    /**
     * Register autoloading for shared Luminova modules.
     *
     * This method wires namespace-to-path mappings for reusable core modules
     * located outside the current project (e.g., a shared Luminova installation).
     *
     * @return void
     */
    private static function tryVciAutoload(): void 
    {
        if (!self::$vciConfig) {
            return;
        }

        if(self::isAutoloadResolver('composer')){
            self::isVersionSatisfies(trim(self::$vciConfig['luminova.version'] ?? ''));
            return;
        }

        if(empty(self::$vciConfig['share.namespaces'] ?? null)){
            $base = self::toPath(self::$vciConfig['luminova.paths']['target']);

            self::$vciConfig['share.namespaces'] = [
                'Luminova\\Funcs\\'  => $base . 'bootstrap/Funcs/',
                'Luminova\\'         => $base . 'system/',
            ];

            if(self::isAutoloadResolver('luminova')){
                $app = APP_BASE_PATH . '/app/';

                self::$vciConfig['share.namespaces'] = array_merge(self::$vciConfig['share.namespaces'], [
                    'App\\Modules\\Controllers\\Http\\' => $app . 'Modules/Controllers/Http/',
                    'App\\Modules\\Controllers\\Cli\\'  => $app . 'Modules/Controllers/Cli/',
                    'App\\Modules\\Controllers\\'       => $app . 'Modules/Controllers/',
                    'App\\Database\\Migrations\\'       => $app . 'Database/Migrations',
                    'App\\Errors\\Controllers\\'        => $app . 'Errors/Controllers/',
                    'App\\Config\\Templates\\'   => $app . 'Config/Templates/',
                    'App\\Database\\Seeders\\'   => $app . 'Database/Seeders/',
                    'App\\Controllers\\Http\\'   => $app . 'Controllers/Http/',
                    'App\\Controllers\\Cli\\'    => $app . 'Controllers/Cli/',
                    'App\\Tasks\\Workers\\'      => $app . 'Tasks/Workers/',
                    'App\\Tasks\\Jobs\\'         => $app . 'Tasks/Jobs/',
                    'App\\Controllers\\'         => $app . 'Controllers/',
                    'App\\Database\\'            => $app . 'Database/',
                    'App\\Modules\\'             => $app . 'Modules/',
                    'App\\Models\\'              => $app . 'Models/',
                    'App\\Console\\'             => $app . 'Console/',
                    'App\\Config\\'              => $app . 'Config/',
                    'App\\Utils\\'               => $app . 'Utils/',
                    'App\\Tasks\\'               => $app . 'Tasks/',
                    'App\\'                      => $app
                ]);

                uksort(self::$vciConfig['share.namespaces'], fn($a, $b) => strlen($b) <=> strlen($a));
            }
        }

        spl_autoload_register(static function(string $class): void 
        {
            if (isset($classes[$class])) {
                require self::$classes[$class];
                return;
            }

            foreach (self::$vciConfig['share.namespaces'] as $prefix => $base) {
                if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                    continue;
                }

                $relative = substr($class, strlen($prefix));
                $filename = $base 
                    . self::toPath(ltrim($relative, '\\/'), '.php');

                if (str_ends_with($filename, '/system/Boot.php')) {
                    throw new \RuntimeException(
                        sprintf('%s must be loaded from the current project context.', $class)
                    );
                }

                if (is_file($filename)) {
                    self::$classes[$class] = $filename;
                    require $filename;
                    return;
                }
            }
        });

        self::isVersionSatisfies(trim(self::$vciConfig['luminova.version'] ?? ''));
    }

    /**
     * Loads core modules and completes the bootstrapping process.
     *
     * This method is called after `warmup()` to load the core framework and 
     * perform any final initialization steps. It ensures the application is fully 
     * prepared to handle requests or execute CLI commands.
     *
     * @return void
     */
    private static function resolveVciConfig(bool $assert = true): void
    {
        if(self::$vciConfig !== null){
            return;
        }

        $path = APP_BASE_PATH . '/.vci.php';

        if(!@is_file($path)){
            return;
        }

        $config = include $path;

        if(!is_array($config) || !(bool) ($config['resolve.paths'] ?? false)){
            return;
        }

        if (!isset($config['luminova.paths'])) {
            if(!$assert){
                return;
            }

            self::onError('Invalid Luminova configuration: missing paths.');
        }

        if (!isset($config['luminova.version'])) {
            if(!$assert){
                return;
            }

            self::onError('Invalid Luminova configuration: missing version.');
        }

        self::$vciConfig = $config;
    }

    /**
     * Configure default PHP runtime settings for the application.
     *
     * This method initializes core environment behavior such as:
     * - Script execution time limit
     * - Default timezone
     * - Internal multibyte encoding
     * - Client abort handling
     *
     * Values are resolved from environment configuration using `env`.
     *
     * @return void
     */
    private static function setAppDefaults(): void
    {
        /**
         * Set error reporting.
         */
        if(PHP_SAPI !== 'cli'){
            error_reporting((PRODUCTION && !STAGING) ? 
                E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED :
                E_ALL
            );
            ini_set(
                'display_errors', 
                ((STAGING || !PRODUCTION) && Env::get('debug.display.errors', false)) ? '1' : '0'
            );

            ini_set('error_prepend_string', '<span class="php-core-error">');
            ini_set('error_append_string', '</span>');
        }

        /**
         * Set exception tracing arguments reporting.
         */
        ini_set('zend.exception_ignore_args', (!STAGING && PRODUCTION) ? '1' : '0');

        // Set max execution time (seconds)
        $limit = Env::get('script.execution.limit');

        if($limit !== null){
            Runtime::setExecutionTime((int) $limit);
        }

        // Set default timezone
        $tz = (string) Env::get('app.timezone');

        if($tz){
            Runtime::setTimezone($tz);
        }

        // Set internal encoding (if defined)
        $enc = (string) Env::get('app.mb.encoding');

        if($enc){
            Runtime::setEncoding($enc);
        }

        // Control behavior on client disconnect
        $abort = Env::get('script.ignore.abort');
        
        if($abort !== null){
            Runtime::setIgnoreUserAbort((bool) $abort);
        }
    }

    /**
     * Safely includes a PHP file if it exists, with optional exception handling.
     * 
     * This method attempts to include a specified file from the boot directory 
     * or the default base directory.
     * 
     * If the file is not found and `$throw` is true, a RuntimeException is thrown.
     * Otherwise, it fails silently.
     *
     * @param string $file The file pathname.
     * @param string $from The boot directory to load from (e.g., 'system', 'bootstrap', 'package').
     * @param bool $shared
     * @param bool $return
     * @param bool $throw
     * 
     * @return mixed
     * @throws \RuntimeException If the file is required but missing.
     */
    private static function tryIncludeModule(
        string $file, 
        string $from = 'system', 
        bool $shared = true,
        bool $return = false,
        bool $throw = false
    ): mixed
    {
        if($shared && self::$vciConfig){
            $paths = self::$vciConfig['luminova.paths'] ?? [];

            if (!isset($paths['target'])) {
                self::onError(
                    sprintf('Missing target path in luminova.paths configuration.', $from)
                );
            }
        
            $base = self::toPath($paths['target']);
            $root = match($from){
                'system'    => $base . 'system/',
                'bootstrap' => $base . 'bootstrap/',
                default     => $base
            };
        } else{
            $root = match($from){
                'system'    => __DIR__ . '/',
                'app'       => APP_BASE_PATH . '/app/',
                'bootstrap' => APP_BASE_PATH . '/bootstrap/',
                'package'   => __DIR__ . '/plugins/',
                default     => __DIR__ . '/'
            };
        }

        $filepath = $root . $file;

        if (is_file($filepath)) {
            if($return){
                return (require $filepath);
            }

            require_once $filepath;
            return 0;
        }

        $isProduction = self::isProduction();
        $message = $isProduction
            ? sprintf('Boot file "%s" not found.', $file)
            : sprintf('Boot file "%s" not found. Checked path: %s', $file, $filepath);

        if($throw){
            throw new \RuntimeException($message);
        }

        self::onError($message, $isProduction);
        return false;
    }

    /**
     * Resolve import file.
     *
     * @param string $path
     * @param array $scope
     * @param boolean $once
     * @param boolean $require
     * @param boolean $promise
     * 
     * @return mixed
     */
    private static function resolveImport(
        string $path,
        array $scope,
        bool $once,
        bool $require,
        bool $promise
    ): mixed
    {
        if ($scope !== []) {
            if (array_is_list($scope)) {
                $e = new InvalidArgumentException(
                    'import(scope: ...) scope must be an associative array.'
                );

                return $promise
                    ? new Rejected($e)
                    : throw $e;
            }

            extract($scope, \EXTR_SKIP);
        }

        try{
            $result = $require
                ? ($once ? require_once $path : require $path)
                : ($once ? include_once $path : include $path);

            return $promise
                ? new Fulfilled($result)
                : $result;
        } catch(Throwable $e){
            if(!$promise){
                throw $e;
            }

            return new Rejected($e);
        }
    }

    /**
     * Determine if the application is running in production mode.
     *
     * This method checks defined constants to determine if the application is in production.
     * It first checks for a `Runtime::isLocalhost()` constant, then falls back to `PRODUCTION`.
     * If neither constant is defined, it defaults to true (production mode).
     *
     * @return bool True if in production mode, false otherwise.
     */
    private static function isProduction(): bool 
    {
        if(defined('PRODUCTION')){
            return PRODUCTION === true;
        }

        return !Runtime::isLocalhost();
    }

    /**
     * Load application is undergoing maintenance.
     * 
     * @return never
     */
    private static function maintenance(): never 
    {
        $message = 'Error: (503) System undergoing maintenance!';

        if(Runtime::isCommand()){
            Terminal::error($message);
            exit(STATUS_SUCCESS);
        }

        error_reporting(0);
        ini_set('display_errors', 0);

        $retry = Env::get('app.maintenance.retry', 3600);
        try{
            Header::sendNoCacheHeaders(503, retry: $retry);
            self::tryIncludeModule(
                file: 'Errors/Defaults/maintenance.php',
                from: 'app',
                shared: false,
                return: false,
                throw: true
            );
        }catch(Throwable){
            Luminova::terminate(
                503, 
                $message, 
                'Service Unavailable', 
                $retry,
                hookOnTerminated: false
            );
        }

        exit(STATUS_SUCCESS);
    }

    /**
     * Check luminova shared module version compatibility.
     *
     * @param string $version
     * 
     * @return void
     */
    private static function isVersionSatisfies(string $version): void
    {
        if($version === Luminova::VERSION){
            return;
        }

        $isSatisfies = false;
        $first = $version[0] ?? '';

        if (str_contains($version, ' ')
            || $first === '^'
            || $first === '~'
        ) {
            $isSatisfies = Version::satisfies(Luminova::VERSION, $version);
        } else{
            $op = '=';
            $target = $version;
            $second = $version[1] ?? '';

            if (in_array($first, ['>', '<', '!', '='], true)) {
                if ($second === '=') {
                    $op = $first . '=';
                    $target = substr($version, 2);
                } elseif (
                    ($first === '!' && $second === '=')
                    || ($first === '<' && $second === '>')
                ) {
                    $op = '!=';
                    $target = substr($version, 2);
                } else {
                    $op = $first;
                    $target = substr($version, 1);
                }
            }

            $isSatisfies = version_compare(
                Luminova::VERSION,
                trim($target),
                $op
            );
        }

        if (!$isSatisfies) {
            self::onError(sprintf(
                'Luminova version constraint not satisfied: required %s, current %s.',
                $version,
                Luminova::VERSION
            ));
        }
    }

    /**
     * Attempt to serve a static cached page for the current request.
     *
     * If static page caching is enabled and the request URI matches a configured
     * cache file extension, this method checks whether a valid cached version
     * exists. When a valid cache file is found, it is rendered and request
     * processing is terminated.
     *
     * If no valid cache is available, the matched file extension is removed from
     * the request URI so the route can be processed normally.
     *
     * @return bool Returns true if a cached response was rendered; otherwise false.
     */
    private static function cache(): bool
    {
        if (!Env::get('page.caching', false)) {
            return false;
        }

        // Supported extension types to match.
        $types = Runtime::getStaticCacheTypes();

        if ($types === '') {
            return false;
        }

        $uri = Router::getUriPath();

        if (preg_match('/\.(' . $types . ')$/iu', $uri, $matches)) {
            $cache = new ViewCache(
                directory: Luminova::root('/writeable/caches/templates/')
            );

            $rendered = false;
            $isExpired = $cache->setKey(Kernel::getCacheId())
                ->setUri($uri)
                ->expired($matches[1]);

            if ($isExpired === false) {
                $rendered = $cache->read(
                    $matches[1],
                    onRendered: function(): void {
                        Runtime::add(Runtime::CLASS_METADATA, 'isStaticCache', true);
                        Runtime::add(Runtime::CLASS_METADATA, 'isCache', true);
                        Runtime::profiling('stop');
                    }
                );
            }

            $cache = null;

            if($rendered === true){
                return true;
            }

            // Remove the matched file extension to render the request normally
            Router::$uriPath = substr($uri, 0, -strlen($matches[0]));
        }
        
        return false;
    }

    /**
     * Overrides the HTTP request method using `_method` or `_METHOD`.
     *
     * Allows browsers or clients to spoof HTTP methods (e.g., PUT, DELETE) via POST.
     *
     * @return void
     */
    private static function override(): void 
    {
        if (PRODUCTION || PHP_SAPI === 'cli') {
            return;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $override = $_POST['_METHOD'] 
                ?? $_POST['_method'] 
                ?? $_GET['_method'] 
                ?? null;

            if (!$override) {
                return;
            }

            $override = strtoupper(trim($override));

            if (in_array($override, ['PUT', 'DELETE', 'PATCH', 'OPTIONS'], true)) {
                $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = $override;
            }
        }
    }

    /**
     * Initialize and register optional application features.
     *
     * This method checks the `.env` or environment configuration for enabled features and performs
     * the following actions:
     * 1. Registers the PSR-4 autoloader if `feature.app.autoload.psr4` is enabled.
     * 2. Registers application service classes if `feature.app.services` is enabled.
     * 3. Initializes class aliases from `app/Config/Modules.php` if `feature.app.class.alias` is enabled.
     *    - Logs a warning if an alias cannot be created.
     *    - Prevents re-initialization using the `USER_MODULE_AUTOLOAD` flag.
     * 4. Loads and initializes developer global functions from `app/Utils/Global.php` if
     *    `feature.app.dev.functions` is enabled.
     *    - Prevents re-initialization using the `USER_DEFINED_GLOBAL_HELPER` flag.
     *
     * @return void
     */
    private static function features(): void 
    {
        if(defined('APP_BOOTED')){
            return;
        }

        /**
         * Load and initialize dev global functions.
         */
        if (
            !defined('USER_DEFINED_GLOBAL_HELPER') 
            && Env::get('feature.app.dev.functions', false)
        ) {
            self::tryIncludeModule('Utils/Global.php', 'app', false);
            define('USER_DEFINED_GLOBAL_HELPER', true);
        }

        /**
         * Autoload register PSR-4 classes.
         */
        if (Env::get('feature.app.autoload.psr4', false)) {
            Autoloader::register();
        }

        /**
         * Register application services.
         */
        if (Env::get('feature.app.services', false)) {
            Factory::register();
        }

        /**
         * Initialize and register class modules and aliases.
         */
        if (!defined('USER_MODULE_AUTOLOAD') && self::registerAliases()) {
            define('USER_MODULE_AUTOLOAD', true);
        }
    }
}
Boot::warmup();