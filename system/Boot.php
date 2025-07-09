<?php 
/**
 * Luminova Framework Bootstrapping.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova;

use \Throwable;
use \Luminova\Http\Header;
use \Luminova\Logger\Logger;
use \Luminova\Routing\Router;
use \Luminova\Command\Terminal;
use \Luminova\Cache\StaticCache;
use \Luminova\Foundation\Error\Guard;
use \Luminova\Foundation\Module\Factory;
use \Luminova\Exceptions\RuntimeException;
use \Luminova\Foundation\Core\Application;
use \Luminova\Foundation\Module\Autoloader;
use function \Luminova\Funcs\{root, import};
use \Luminova\Interface\{KernelInterface, RouterInterface, LazyObjectInterface};

/**
 * Luminova framework autoloader helper class.
 *
 * Luminova relies on Composer for autoloading. This class simplifies the process 
 * of loading both Composer and framework modules without needing to manually include 
 * `plugins/vendor/autoload.php` and other boot files.
 * 
 * To autoload required files, include `/system/Boot.php` and call the appropriate 
 * static method based on your environment (`http()`, `cli()`, or `autoload()`).
 * 
 * @see https://luminova.ng/docs/0.0.0/boot/autoload
 * 
 * Boot Shared Memory
 *
 * Lightweight in-memory storage for application key/value data.
 * Works as a static runtime registry. Comparable in concept to Swift's
 * `UserDefaults` or Android/Java `SharedPreferences`, but not persistent.
 *
 * @see \Luminova\Funcs\shared() Global helper for quick access.
 *
 * @example - Usages:
 * ```php
 * use Luminova\Boot;
 *
 * Boot::set('THEME', true);
 *
 * $context = Boot::get('THEME', false);
 *
 * if (Boot::has('THEME')) {
 *     // Key exists...
 * }
 *
 * Boot::remove('THEME');
 * Boot::clear();
 * ```
 *
 * > **Note**
 * > This storage is runtime-only:
 * > - Data is lost at the end of each request.
 * > - Values are not shared across processes or workers.
 * > - Nothing is written to disk.
 */
final class Boot
{
    /**
     * Storage for shared keys and values.
     *
     * @var array<string,mixed> $storage
     */
    private static array $storage = [];

    /**
     * Indicates whether warmup initialization has already been performed.
     *
     * @var bool $isWarmed
     */
    private static bool $isWarmed = false;

    /**
     * If all features are registered.
     * 
     * @var bool $registered 
     */
    private static bool $registered = false;

    /**
     * Initializes the HTTP environment for web and API applications.
     *
     * Warms up the application, registers error handlers, and loads core modules.
     * 
     * > Similar to the `http()` method but does not return the application instance.
     *
     * @example Usage:
     * ```php
     * use \Luminova\Boot;
     * 
     * require_once __DIR__ . '/system/Boot.php';
     * 
     * Boot::init();
     * ```
     * @see http()
     * @see autoload()
     */
    public static function init(): void
    {
        self::warmup();
        Guard::register();
        self::finish();
    }

    /**
     * Initializes all required autoload modules.
     *
     * Wrapper for `warmup()` and `finish()`. Loads core modules and prepares the application 
     * context without registering error handlers or configuring the CLI environment.
     * 
     * > Use this to ensure Composer and framework modules are available in the current context.
     *
     * @example Usage:
     * ```php
     * use \Luminova\Boot;
     * 
     * require_once __DIR__ . '/system/Boot.php';
     * 
     * Boot::autoload();
     * ```
     * @see init()
     * @see http()
     * @see cli()
     */
    public static function autoload(): void
    {
        self::warmup();
        self::finish();
    }

    /**
     * Prepares the HTTP environment for web and API applications.
     *
     * Typically used in `public/index.php`. Ensures the application is warmed up, 
     * registers error handlers, and loads required core modules before returning 
     * the application instance.
     *
     * @return Application|\App\Application<Application> Return the application instance.
     * @example - Usage (public/index.php)
     * 
     * ```php
     * use \Luminova\Boot;
     * 
     * require_once __DIR__ . '/../system/Boot.php';
     * 
     * Boot::http()->router->context(...)->run();
     * ```
     * @see init()
     */
    public static function http(): Application
    {
        self::init();

        if(!PRODUCTION && !IS_LOCALHOST){
           exit('Invalid environment mode for production. Set "app.environment.mood=production" or "staging".');
        }

        return self::application();
    }

    /**
     * Get the shared application instance.
     *
     * Returns the current CoreApplication singleton.  
     * This provides a single, global access point to the running application.
     *
     * @return Application|\App\Application<Application> Returns the application instance.
     * > **Note:**
     * > Future versions may introduce parameters so the method can return
     * > context-specific or module-specific application instances.
     */
    public static function application(): Application
    {
        return \App\Application::getInstance();
    }

    /**
     * Return the kernel instance for this application.
     *
     * @return KernelInterface|\App\Config\Kernel|null The kernel instance, or null to use the default.
     */
   /* public static function kernel(): ?KernelInterface
    {
        if(!Kernel::shouldShareObject()){
            return Kernel::create();
        }

        if(!self::$kernel instanceof KernelInterface){
            self::$kernel = Kernel::create();
        }

        return self::$kernel;
    }*/

    /**
     * Prepares the CLI environment.
     *
     * Intended for use in custom CLI scripts. Autoloads required files, 
     * enables error reporting, validates the SAPI type, defines CLI constants, 
     * and completes the bootstrapping process.
     *
     * @return void
     * @example - Usage (/bin/script.php)
     * ```php
     * #!/usr/bin/env php
     * <?php
     * use \Luminova\Boot;
     * 
     * require __DIR__ . '/system/Boot.php';
     * 
     * Boot::cli();
     * 
     * // Your cli implementation
     * ```
     */
    public static function cli(): void
    {
        self::warmup();
        ini_set('display_errors', '1');
        error_reporting(E_ALL);

        if (str_starts_with(PHP_SAPI, 'cgi')) {
            echo 'Novakit CLI tool requires php-cli. php-cgi is not supported.';
            exit(1);
        }

        defined('CLI_ENVIRONMENT') || define('CLI_ENVIRONMENT', env('cli.environment.mood', 'testing'));

        self::shouldDefineCommandStreams();
        self::finish();
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
     */
    public static function warmup(): void
    {
        if (self::$isWarmed) {
            self::override();
            return;
        }

        require_once __DIR__ . '/../bootstrap/constants.php';
        self::override();

        require_once __DIR__ . '/../bootstrap/functions.php';
        require_once __DIR__ . '/Foundation/Error/Guard.php';
        require_once __DIR__ . '/Foundation/Error/Message.php';
        require_once __DIR__ . '/Luminova.php';

        self::$isWarmed = true;
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
    public static function tryFopen(string $filename, string $mode): mixed
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
     * Ensures CLI standard streams (STDIN, STDOUT, STDERR) are defined.
     *
     * Uses `tryFopen()` to safely define them if not already set.
     *
     * @return void
     * @throws RuntimeException If any stream cannot be defined.
     */
    public static function shouldDefineCommandStreams(): void 
    {
        defined('STDIN') || define('STDIN', self::tryFopen('php://stdin', 'r'));
        defined('STDOUT') || define('STDOUT', self::tryFopen('php://stdout', 'w'));
        defined('STDERR') || define('STDERR', self::tryFopen('php://stderr', 'w'));
    }

    /**
     * Set a shared value.
     *
     * Stores the given value under the specified key.
     *
     * @param string $key The key to store the value under.
     * @param mixed  $value The value to store.
     *
     * @return mixed Returns the stored value.
     *
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     * 
     * Boot::set('theme', 'dark');
     * ```
     */
    public static function set(string $key, mixed $value): mixed
    {
        return self::$storage[$key] = $value;
    }

    /**
     * Get a shared value.
     *
     * Retrieves a value stored under the specified key.
     * Returns `null` if the key does not exist.
     *
     * @param string $key The key to retrieve.
     *
     * @return mixed Returns the stored value or `null` if not found.
     *
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     * 
     * $theme = Boot::get('theme') ?? 'light';
     * ```
     */
    public static function get(string $key): mixed
    {
        return self::$storage[$key] ?? null;
    }

    /**
     * Check if a key exists in the shared storage.
     *
     * @param string $key The key to check.
     *
     * @return bool Return true if the key exists, false otherwise.
     *
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     * 
     * if (Boot::has('theme')) { ... }
     * ```
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$storage);
    }

    /**
     * Remove a key from the shared storage.
     *
     * @param string $key The key to remove.
     *
     * @return void
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     * 
     * Boot::remove('theme');
     * ```
     */
    public static function remove(string $key): void
    {
        unset(self::$storage[$key]);
    }

    /**
     * Clear all keys from the shared storage.
     *
     * @return void 
     * 
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     * 
     * Boot::clear();
     * ```
     */
    public static function clear(): void
    {
        self::$storage = [];
    }

    /**
     * Count the number of keys currently stored.
     *
     * @return int Return the number of stored keys.
     *
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     * 
     * $count = Boot::count();
     * ```
     */
    public static function count(): int
    {
        return count(self::$storage);
    }

    /**
     * Display performance-related warnings during production.
     *
     * This method checks for common configuration issues that slow the
     * framework down and logs a warning when something important is not
     * enabled. 
     *
     * Warnings are only shown when:
     * - The application is running in production, and
     * - The "debug.alert.performance.tips" feature is turned on.
     *
     * This feature does **not** disable any PHP setting. It only reports
     * problems so developers can fix them. Nothing is modified.
     *
     * Current checks:
     * - OPcache is installed but not enabled.
     * - Route attribute scanning is active but attribute caching is off.
     *
     * @return void
     */
    public static function tips(): void
    {
        $entry = '';

        if (PRODUCTION && env('debug.alert.performance.tips', false)) {
            $off = 'Disable this warning by setting "debug.alert.performance.tips=false" in your .env file.';

            if (function_exists('opcache_get_status') && !ini_get('opcache.enable')) {
                $entry .= Logger::entry(
                    'warning',
                    "OPcache is installed but disabled. Enable it to improve performance. {$off}"
                );
            }

            if (env('feature.route.attributes') && !env('feature.route.cache.attributes', false)) {
                $entry .= Logger::entry(
                    'warning',
                    "Route attribute caching is disabled. Turn it on to avoid repeated reflection scans. {$off}"
                );
            }
        }

        if($entry !== ''){
            Logger::warning($entry);
        }
    }

    /**
     * Load application is undergoing maintenance.
     * 
     * @return bool Return true.
     */
    private static function maintenance(bool $isCommand): bool 
    {
        $err = 'Error: (503) System undergoing maintenance!';

        if($isCommand){
            Terminal::error($err);
            return true;
        }

        Header::headerNoCache(503, null, env('app.maintenance.retry', '3600'));
        
        try{
            import(path: 'app:Errors/Defaults/maintenance.php', once: true, throw: true);
        }catch(Throwable){
            echo $err;
        }

        return true;
    }

    /**
     * Serve static cached pages.
     * 
     * If cache is enabled and the request is not in CLI mode, check if the cache is still valid.
     * If valid, render the cache and terminate further router execution.
     *
     * @return bool Return true if cache is rendered, otherwise false.
     */
    private static function cache(bool $isCommand): bool
    {
        if ($isCommand || !env('page.caching', false)) {
            return false;
        }

        // Supported extension types to match.
        $types = env('page.caching.statics', null);

        if(!$types){
           return false;
        }

        $uri =  Luminova::getUriSegments();

        if (preg_match('/\.(' . $types . ')$/iu', $uri, $matches)) {
            $cache = new StaticCache(
                directory: root('/writeable/caches/templates/')
            );

            $rendered = false;
            $expired = $cache->setKey(Luminova::getCacheId())
                ->setUri($uri)
                ->expired($matches[1]);

            if ($expired === false) {
                $rendered = $cache->read(
                    $matches[1],
                    onBeforeRender: fn() => Luminova::profiling('start'),
                    onRendered: function() {
                        Luminova::setClassMetadata([
                            'staticCache' => true, 
                            'cache' => true
                        ]);
                        Luminova::profiling('stop');
                    }
                );
            }

            $cache = null;

            if($rendered === true){
                return true;
            }

            // Remove the matched file extension to render the request normally
            Router::$staticCacheUri = substr($uri, 0, -strlen($matches[0]));
        }
        
        return false;
    }

    /**
     * Finalizes the bootstrapping process.
     *
     * Loads plugin autoloaders and custom framework feature. Sets `IS_UP` constant if not defined.
     *
     * @return void
     */
    private static function finish(): void
    {
        require_once __DIR__ . '/plugins/autoload.php';

        self::features();
        defined('IS_UP') || define('IS_UP', true);
        $isCommand = Luminova::isCommand();

        // If application is undergoing maintenance.
        if(MAINTENANCE && self::maintenance($isCommand)){
            exit(STATUS_SUCCESS);
        }

        // If the view uri ends with `.extension`, then try serving the cached static version.
        if(self::cache($isCommand)){
            exit(STATUS_SUCCESS);
        }
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
        if (PRODUCTION || php_sapi_name() === 'cli') {
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
     *    - Prevents re-initialization using the `__INIT_DEV_MODULES__` flag.
     * 4. Loads and initializes developer global functions from `app/Utils/Global.php` if
     *    `feature.app.dev.functions` is enabled.
     *    - Prevents re-initialization using the `__INIT_DEV_FUNCTIONS__` flag.
     *
     * @return void
     */
    private static function features(): void 
    {
        if(self::$registered){
            return;
        }

        /**
         * Autoload register PSR-4 classes.
         */
        if (env('feature.app.autoload.psr4', false)) {
            Autoloader::register();
        }

        /**
         * Register application services.
         */
        if (env('feature.app.services', false)) {
            Factory::register();
        }

        /**
         * Initialize and register class modules and aliases.
         */
        if (env('feature.app.class.alias', false) && !defined('__INIT_DEV_MODULES__')) {
            $config = import('app:Config/Modules.php', true, true);

            if($config && $config !== []){
                if (is_array($config['alias'] ?? null)) {
                    $entry = '';
                    foreach ($config['alias'] as $alias => $namespace) {
                        if (!class_alias($namespace, $alias)) {
                            $entry = Logger::entry(
                                'warning', 
                                "Failed to create alias [{$alias}] for class [{$namespace}]"
                            );
                        }
                    }

                    if($entry !== ''){
                        Logger::warning($entry);
                    }
                }

                define('__INIT_DEV_MODULES__', true);
                $config = null;
            }
        }

        /**
         * Load and initialize dev global functions.
         */
        if (env('feature.app.dev.functions', false) && !defined('__INIT_DEV_FUNCTIONS__')) {
            import('app:Utils/Global.php', true, true);
            define('__INIT_DEV_FUNCTIONS__', true);
        }

        self::$registered = true;
    }

    // Prevent instantiation, cloning, and unserialization
    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {}
}
Boot::warmup();