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
use \Luminova\Luminova;
use \Luminova\Http\Header;
use \Luminova\Logger\Entry;
use \Luminova\Routing\Router;
use \Luminova\Command\Terminal;
use \Luminova\Cache\StaticCache;
use \Luminova\Foundation\Error\Error;
use \Luminova\Foundation\Module\Factory;
use \Luminova\Exceptions\RuntimeException;
use \Luminova\Foundation\Core\Application;
use \Luminova\Foundation\Module\Autoloader;
use function \Luminova\Funcs\{root, import};

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
     * Enable or store query debug information.
     *
     * @var string QUERY_DEBUG
     */
    public const QUERY_DEBUG = 'luminova.b.query.debug';

    /**
     * Allow dropping columns during table alteration.
     *
     * @var string ALTER_DROP_COLUMNS
     */
    public const ALTER_DROP_COLUMNS = 'luminova.b.db.alter_drop_columns';

    /**
     * Enable validation before running ALTER TABLE operations.
     *
     * @var string CHECK_ALTER_TABLE
     */
    public const CHECK_ALTER_TABLE = 'luminova.b.db.check_alter_table';

    /**
     * Flag indicating a successful migration execution.
     *
     * @var string MIGRATION_SUCCESS
     */
    public const MIGRATION_SUCCESS = 'luminova.b.migration.success';

    /**
     * Flag indicating a successful table alteration.
     *
     * @var string ALTER_SUCCESS
     */
    public const ALTER_SUCCESS = 'luminova.b.alter.success';

    /**
     * Store database transaction object.
     *
     * @var string DROP_TRANSACTION
     */
    public const DROP_TRANSACTION = 'luminova.O.db.transaction';

    /**
     * Store query execution profiling data.
     *
     * @var string QUERY_PROFILING
     */
    public const QUERY_PROFILING = 'luminova.a.db.query.profiling';

    /**
     * Store routed class metadata used during runtime.
     *
     * @var string CLASS_METADATA
     */
    public const CLASS_METADATA = 'luminova.a.class.metadata';

    /**
     * Store rendered template context ID.
     *
     * @var string TEMPLATE_CONTEXT
     */
    public const TEMPLATE_CONTEXT = 'luminova.s.template.context';

    /**
     * All internal memory-cache storage keys.
     *
     * @var string[] ALL_KEYS
     */
    public const ALL_KEYS = [
        self::QUERY_DEBUG,
        self::ALTER_DROP_COLUMNS,
        self::CHECK_ALTER_TABLE,
        self::MIGRATION_SUCCESS,
        self::ALTER_SUCCESS,
        self::DROP_TRANSACTION,
        self::QUERY_PROFILING,
        self::CLASS_METADATA,
        self::TEMPLATE_CONTEXT,
    ];

    /**
     * Storage for shared keys and values.
     *
     * @var array<string,mixed> $storage
     */
    private static array $storage = [];

    /**
     * If all features are registered.
     * 
     * @var bool $registered 
     */
    private static bool $registered = false;

    /**
     * Prevent initialization.
     */
    private function __construct() {}

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
        Error::register();
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

        if(!PRODUCTION && !IS_LOCAL){
           exit('Invalid environment mode for production. Set "app.environment.mood=production" or "staging".');
        }

        return self::application();
    }

    /**
     * Get the shared application instance.
     *
     * @return Application|\App\Application<Application> Returns the application instance.
     */
    public static function application(): Application
    {
        return Luminova::kernel(true)->getApplication() 
            ?? \App\Application::getInstance();
    }

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

        defined('CLI_ENVIRONMENT') 
            || define('CLI_ENVIRONMENT', env('cli.environment.mood', 'testing'));

        self::defineCliStreams();
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
        if (defined('APP_WARMED_UP')) {
            self::override();
            return;
        }

        require_once __DIR__ . '/../bootstrap/constants.php';
        self::override();

        require_once __DIR__ . '/../bootstrap/functions.php';
        self::tryInclude('/Exceptions/ErrorCode.php');
        self::tryInclude('/Foundation/Error/Error.php');
        self::tryInclude('/Foundation/Error/Message.php');
        require_once __DIR__ . '/Luminova.php';

        self::setAppDefaults();

        define('APP_WARMED_UP', true);
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
     * Values are resolved from environment configuration using `env()`.
     *
     * @return void
     */
    private static function setAppDefaults(): void
    {
        // Set max execution time (seconds)
        Luminova::setExecutionTimeLimit(
            (int) env('script.execution.limit', 30)
        );

        // Set default timezone
        Luminova::setTimezone(
            (string) env('app.timezone', 'UTC')
        );

        // Set internal encoding (if defined)
        Luminova::setEncoding(
            (string) env('app.mb.encoding', '')
        );

        // Control behavior on client disconnect
        Luminova::setIgnoreUserAbort(
            (bool) env('script.ignore.abort', false)
        );
    }

    /**
     * Try including files that may not be available first during installation.
     *
     * @param string $file The file pathname.
     * 
     * @return void
     */
    private static function tryInclude(string $file): void
    {
        $filepath = __DIR__ . $file;

        if (@is_file($filepath)) {
            require_once $filepath;
        }
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
     * Ensures CLI standard streams (STDIN, STDOUT, STDERR) are available.
     *
     * If the constants are not already defined, this method safely opens the
     * corresponding PHP streams using `tryFopen()` and defines them.
     *
     * @return void
     * @throws RuntimeException If any of the streams cannot be opened or defined.
     */
    public static function defineCliStreams(): void
    {
        defined('STDIN')  || define('STDIN',  self::tryFopen('php://stdin',  'r'));
        defined('STDOUT') || define('STDOUT', self::tryFopen('php://stdout', 'w'));
        defined('STDERR') || define('STDERR', self::tryFopen('php://stderr', 'w'));
    }

    /**
     * Store a shared value by key.
     *
     * Saves a value in the shared storage. If both the existing value and
     * the new value are arrays, they are merged using `array_replace`,
     * with the new values overriding existing ones.
     *
     * @param string $key The storage key.
     * @param mixed $value The value to store.
     *
     * @return mixed Returns the stored value.
     *
     * @example - Examples:
     * ```php
     * use Luminova\Boot;
     *
     * Boot::set('theme', 'dark');
     * Boot::set('config', ['debug' => true]);
     * Boot::set('config', ['cache' => false]); // merges with existing array
     * ```
     */
    public static function set(string $key, mixed $value): mixed
    {
        if (is_array($value) && is_array(self::$storage[$key] ?? null)) {
            return self::$storage[$key] = array_replace(
                self::$storage[$key],
                $value
            );
        }

        return self::$storage[$key] = $value;
    }

    /**
     * Add a value to a shared array entry.
     *
     * Ensures the storage key exists as an array, then assigns the value
     * at the given index.
     *
     * @param string $key The storage key.
     * @param string|int $index The array index to add value.
     * @param mixed $value The value to store.
     *
     * @return mixed Returns the stored value.
     *
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     *
     * Boot::add('routes', 'home', '/');
     * Boot::add('routes', 'login', '/login');
     * ```
     */
    public static function add(string $key, string|int $index, mixed $value): mixed
    {
        if (!isset(self::$storage[$key])) {
            self::$storage[$key] = [];
        }

        return self::$storage[$key][$index] = $value;
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
     * Remove a value from the shared storage.
     *
     * By default, this method protects Luminova internal keys and will not
     * remove framework-owned storage entries. To force removal of a core
     * key, explicitly set `$user` to false.
     *
     * @param string $key  The storage key to remove.
     * @param bool $userDefined Whether to enforce protection for core keys.
     *
     * @return void
     *
     * @example - Example:
     * ```php
     * use Luminova\Boot;
     *
     * // Remove a user-defined key
     * Boot::remove('theme');
     *
     * // Force removal of a core key (not recommended)
     * Boot::remove(Boot::CLASS_METADATA, false);
     * ```
     */
    public static function remove(string $key, bool $userDefined = true): void
    {
        if ($userDefined && in_array($key, self::ALL_KEYS, true)) {
            return;
        }

        self::$storage[$key] = null;

        unset(self::$storage[$key]);
    }

    /**
     * Clear all user-defined values from the shared storage.
     *
     * Core Luminova keys are preserved and cannot be removed by this method.
     * This makes the operation safe to use in long-running processes and
     * framework-level code.
     *
     * @return void
     *
     * @example - Example
     * ```php
     * use Luminova\Boot;
     *
     * Boot::clear();
     * ```
     */
    public static function clear(): void
    {
        foreach (array_keys(self::$storage) as $key) {
            if (in_array($key, self::ALL_KEYS, true)) {
                continue;
            }

            self::$storage[$key] = null;
            unset(self::$storage[$key]);
        }
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
        if (!PRODUCTION || !env('debug.alert.performance.tips', false)) {
            return;
        }

        $entry = new Entry('warning');
        $off = 'Disable this warning by setting "debug.alert.performance.tips=false" in your .env file.';

        if (function_exists('opcache_get_status') && !ini_get('opcache.enable')) {
            $entry->add("OPcache is installed but disabled. Enable it to improve performance. {$off}");
        }

        if (env('feature.route.attributes') && !env('feature.route.cache.attributes', false)) {
            $entry->add("Route attribute caching is disabled. Turn it on to avoid repeated reflection scans. {$off}");
        }

        if (!$entry->isEmpty()) {
            $entry->log();
        }
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
        require_once __DIR__ . '/plugins/autoload.php';

        defined('APP_BOOTED') || define('APP_BOOTED', true);
        self::features();

        // If application is undergoing maintenance.
        if(MAINTENANCE){
            self::maintenance();
        }else if(!Luminova::isCommand() && self::cache()){
            // If the view uri ends with `.extension`, 
            // then try serving the cached static version.
            exit(STATUS_SUCCESS);
        }
    }

    /**
     * Load application is undergoing maintenance.
     * 
     * @return void
     */
    private static function maintenance(): void 
    {
        $message = 'Error: (503) System undergoing maintenance!';

        if(!Luminova::isCommand()){
            $retry = env('app.maintenance.retry', 3600);
            
            try{
                Header::sendNoCacheHeaders(503, retry: $retry);
                import(path: 'app:Errors/Defaults/maintenance.php', once: true, throw: true);
            }catch(Throwable){
                Luminova::terminate(503, $message, 'Service Unavailable', $retry);
            }

            exit(STATUS_SUCCESS);
        }

        Terminal::error($message);
        exit(STATUS_SUCCESS);
    }

    /**
     * Serve static cached pages.
     * 
     * If cache is enabled and the request is not in CLI mode, check if the cache is still valid.
     * If valid, render the cache and terminate further router execution.
     *
     * @return bool Return true if cache is rendered, otherwise false.
     */
    private static function cache(): bool
    {
        if (!env('page.caching', false)) {
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
            $isExpired = $cache->setKey(Luminova::getCacheId())
                ->setUri($uri)
                ->expired($matches[1]);

            if ($isExpired === false) {
                $rendered = $cache->read(
                    $matches[1],
                    onBeforeRender: fn() => Luminova::profiling('start'),
                    onRendered: function(): void {
                        self::add(self::CLASS_METADATA, 'isStaticCache', true);
                        self::add(self::CLASS_METADATA, 'isCache', true);
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
         * Load and initialize dev global functions.
         */
        if (env('feature.app.dev.functions', false) && !defined('__INIT_DEV_FUNCTIONS__')) {
            import('app:Utils/Global.php', true, true);
            define('__INIT_DEV_FUNCTIONS__', true);
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
                    $entry = new Entry('warning');
                    
                    foreach ($config['alias'] as $alias => $namespace) {
                        if (!class_alias($namespace, $alias)) {
                            $entry->add("Failed to create alias [{$alias}] for class [{$namespace}]");
                        }
                    }

                    if (!$entry->isEmpty()) {
                        $entry->log();
                    }
                }

                define('__INIT_DEV_MODULES__', true);
                $config = null;
            }
        }

        self::$registered = true;
    }
}
Boot::warmup();