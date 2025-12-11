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
use \Luminova\Utility\Version;
use \Luminova\Command\Terminal;
use \Luminova\Cache\StaticCache;
use \Luminova\Foundation\Error\Error;
use \Luminova\Foundation\Module\Factory;
use \Luminova\Exceptions\RuntimeException;
use \Luminova\Foundation\Core\Application;
use \Luminova\Foundation\Module\Autoloader;
use function \Luminova\Funcs\root;

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
     * Boot configuration loaded from `.luminova.php`.
     *
     * @var array<string,mixed>|null $config
     */
    private static ?array $config = null;

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
        return Luminova::kernel('application', true);
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

        self::tryInclude('autoload.php', 'plugins', false);
        // self::tryInclude('Config/Env.php', 'system');
        self::tryInclude('constants.php', 'bootstrap', false);

        self::config();
        self::override();

        self::tryInclude('functions.php', 'bootstrap');

        if(self::isAutoloadResolver(['luminova', 'auto'])){
            self::tryInclude('Luminova.php', 'system');
            self::tryInclude('Exceptions/ErrorCode.php', 'system');
            self::tryInclude('Foundation/Error/Error.php', 'system');
            self::tryInclude('Foundation/Error/Message.php', 'system');
        }

        self::setAppDefaults();

        define('APP_WARMED_UP', true);
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
     * Determine whether shared module configuration is enabled.
     *
     * This method checks if the application is running with a valid shared
     * Luminova module configuration. It loads the boot configuration safely
     * and verifies that shared settings exist.
     *
     * @return bool True if shared modules are enabled, false otherwise.
     */
    public static function isSharedModule(): bool 
    {
        try {
            self::config(false);
            return !empty(self::$config);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Undocumented function
     *
     * @param string[]|string $mode
     * 
     * @return bool
     */
    public static function isAutoloadResolver(array|string $mode): bool 
    {
        return self::$config && in_array(
            self::$config['resolve.autoloader'] ?? 'auto', 
            (array) $mode, 
            true
        );
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
     * Resolve file based on `.luminova.php` boot configuration directory.
     * 
     * This method attempts to resolve a specified file from the boot directory or the default base directory.
     * If the file is not found and `null` is true.
     *
     * @param string $file The file pathname.
     * @param string $from The boot directory to load from (e.g., 'system', 'bootstrap', 'plugins' or `root`).
     * 
     * @return string|null Return full resolved file path.
     */
    public static function resolve(string $file, string $from = 'system'): ?string
    {
        if(
            self::isSharedModule()
            && !str_ends_with($file, $from . '/constants.php')
            && !str_ends_with($file, $from  . '/Boot.php')
            && isset(self::$config['luminova.paths']['target'])
        ){
            $base = self::toPath(self::$config['luminova.paths']['target']);
            $root = match($from){
                'system'    => $base . 'system/',
                'bootstrap' => $base . 'bootstrap/',
                default     => $base
            };
        } else{
            $root = root(match($from){
                'system'    =>  '/system/',
                'bootstrap' =>  '/bootstrap/',
                'plugins'   =>  '/system/plugins/',
                default     => '/'
            });
        }

        $filepath = $root . ltrim($file, '/');

        if (file_exists($filepath)) {
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
    public static function toPath(string $path, string $suffix = '/'): string
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
    public static function onError(string $message, ?bool $isProduction = null): void
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
        $file = __DIR__ . '/../app/Errors/Defaults/xxx.php';

        if(is_file($file)){
            $description = 'Application Runtime Error';
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
    public static function registerAliases(bool $autoload = true, int &$registered = 0): bool
    {
        static $modules = null;
        $registered = 0;

        if (env('feature.app.class.alias', false)) {
            $modules ??= self::tryInclude('Config/Modules.php', 'app', false, true);
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
        //self::tryInclude('autoload.php', 'plugins', false);
        self::trySharedModules();
        self::features();

        defined('APP_BOOTED') || define('APP_BOOTED', true);

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
     * Register autoloading for shared Luminova modules.
     *
     * This method wires namespace-to-path mappings for reusable core modules
     * located outside the current project (e.g., a shared Luminova installation).
     *
     * @return void
     */
    private static function trySharedModules(): void 
    {
        if (!self::$config) {
            return;
        }

        if(self::isAutoloadResolver('composer')){
            self::isVersionSatisfies(trim(self::$config['luminova.version'] ?? ''));
            return;
        }

        if(empty(self::$config['share.namespaces'] ?? null)){
            $base = self::toPath(self::$config['luminova.paths']['target']);

            self::$config['share.namespaces'] = [
                'Luminova\\Funcs\\'  => $base . 'bootstrap',
                'Luminova\\'         => $base . 'system',
            ];

            if(self::isAutoloadResolver('luminova')){
                $app = __DIR__ . '/../app/';

                self::$config['share.namespaces'] = array_merge(self::$config['share.namespaces'], [
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

                uksort(self::$config['share.namespaces'], fn($a, $b) => strlen($b) <=> strlen($a));
            }
        }

        spl_autoload_register(static function(string $class): void 
        {
            if (isset($classes[$class])) {
                require self::$classes[$class];
                return;
            }

            foreach (self::$config['share.namespaces'] as $prefix => $base) {
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

        self::isVersionSatisfies(trim(self::$config['luminova.version'] ?? ''));
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
    private static function config(bool $assert = true): void
    {
        if(self::$config !== null){
            return;
        }

        $path = __DIR__ . '/../.luminova.php';

        if(!@is_file($path)){
            return;
        }

        $config = include $path;

        if(!$config || !($config['resolve.paths'] ?? false)){
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

        self::$config = $config;
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
                ((STAGING || !PRODUCTION) && env('debug.display.errors', false)) ? '1' : '0'
            );

            ini_set('error_prepend_string', '<span class="php-core-error">');
            ini_set('error_append_string', '</span>');
        }

        /**
         * Set exception tracing arguments reporting.
         */
        ini_set('zend.exception_ignore_args', (!STAGING && PRODUCTION) ? '1' : '0');

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
     * Safely includes a PHP file if it exists, with optional exception handling.
     * 
     * This method attempts to include a specified file from the boot directory or the default base directory.
     * If the file is not found and `$throw` is true, a RuntimeException is thrown. Otherwise, it fails silently.
     *
     * @param string $file The file pathname.
     * @param string $from The boot directory to load from (e.g., 'system', 'bootstrap', 'plugins').
     * @param bool $shared
     * @param bool $return
     * @param bool $throw
     * 
     * @return mixed
     * @throws \RuntimeException If the file is required but missing.
     */
    private static function tryInclude(
        string $file, 
        string $from = 'system', 
        bool $shared = true,
        bool $return = false,
        bool $throw = false
    ): mixed
    {
        if($shared && self::$config){
            $paths = self::$config['luminova.paths'] ?? [];

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
                'app'       => __DIR__ . '/../app/',
                'bootstrap' => __DIR__ . '/../bootstrap/',
                'plugins'   => __DIR__ . '/plugins/',
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
     * Determine if the application is running in production mode.
     *
     * This method checks defined constants to determine if the application is in production.
     * It first checks for a `IS_LOCAL` constant, then falls back to `PRODUCTION`.
     * If neither constant is defined, it defaults to true (production mode).
     *
     * @return bool True if in production mode, false otherwise.
     */
    private static function isProduction(): bool 
    {
        if(defined('IS_LOCAL')){
            return IS_LOCAL !== true;
        }

        if(defined('PRODUCTION')){
            return PRODUCTION !== false;
        }

        return true;
    }

    /**
     * Load application is undergoing maintenance.
     * 
     * @return void
     */
    private static function maintenance(): void 
    {
        $message = 'Error: (503) System undergoing maintenance!';

        if(Luminova::isCommand()){
            Terminal::error($message);
            exit(STATUS_SUCCESS);
        }

        $retry = env('app.maintenance.retry', 3600);
        try{
            Header::sendNoCacheHeaders(503, retry: $retry);
            self::tryInclude(
                file: 'Errors/Defaults/maintenance.php',
                from: 'app',
                shared: false,
                return: false,
                throw: true
            );
        }catch(Throwable){
            Luminova::terminate(503, $message, 'Service Unavailable', $retry);
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
                } elseif ($first === '!' && $second === '=') {
                    $op = '!=';
                    $target = substr($version, 2);
                } elseif ($first === '<' && $second === '>') {
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
        if(defined('APP_BOOTED')){
            return;
        }

        /**
         * Load and initialize dev global functions.
         */
        if (!defined('__INIT_DEV_FUNCTIONS__') && env('feature.app.dev.functions', false)) {
            self::tryInclude('Utils/Global.php', 'app', false);
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
        if (!defined('__INIT_DEV_MODULES__') && self::registerAliases()) {
            define('__INIT_DEV_MODULES__', true);
        }
    }
}
Boot::warmup();