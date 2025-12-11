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
     * Luminova phpvci configuration loaded.
     *
     * @var array{
     *      status: 0|1|2,
     *      enabled: bool,
     *      version: string,
     *      paths: array{
     *          target: string
     *      }
     * }|null $phpvci
     * 
     * @see ../.phpvci.php
     */
    private static ?array $phpvci = null;

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
                . 'Set "app.environment.mode=production" or "staging".'
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
        if (!in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            self::onError(sprintf(
                'Method "Boot::cli()" can only be called in CLI context. '
                . 'Use "Boot::http()" instead. Current SAPI: %s.',
                PHP_SAPI
            ), 'Logic Error');
        }

        self::warmup();

        error_reporting(E_ALL);

        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        ini_set('html_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', 'php://stderr');

        defined('CLI_ENVIRONMENT')
            || define('CLI_ENVIRONMENT', Env::get('cli.environment.mode', 'testing'));

        Runtime::defineStandardStreams();

        Error::register();
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
        defined('APP_BASE_PATH') || define(
            'APP_BASE_PATH',
            rtrim(
                getenv('APP_BASE_PATH') ?: ($_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)),
                '/\\'
            )
        );

        if (defined('BOOT_WARMUP_STARTED')) {
            self::override();
            return;
        }

        self::resolvePhpVciConfig();
        
        self::import('autoload.php', from: 'plugins', resolvable: false);
        self::import('polyfills.php', from: 'bootstrap');
        self::import('Luminova.php');
        self::import('Runtime.php');
        self::import('Config/Env.php');

        /**
         * Register environment variables from a `.env` file.
         */
        try{
            Env::register();
        } catch (Throwable $e) {
            self::onError(
                $e->getMessage(), 
                'Configuration Error #500', 
                'Failed to parse environment configuration.'
            );
        }

        self::import('constants.php', from: 'bootstrap');
        self::override();

        self::import('Funcs/functions.php', from: 'bootstrap');
        self::import('Funcs/strings.php', from: 'bootstrap');
        self::import('Funcs/arrays.php', from: 'bootstrap');

        self::setAppDefaults();

        define('BOOT_WARMUP_STARTED', true);

        // start recording
        Runtime::profiling('start');
    }

    /**
     * Determines whether Luminova VCI autoloading is enabled.
     *
     * Loads the 'phpvci.php' configuration and checks whether a target path is configured.
     * Returns false if the configuration cannot be loaded or is invalid.
     *
     * @return bool Returns true if VCI autoloading is enabled, otherwise false.
     *
     * @see ../.phpvci.php
     */
   public static function isPhpVciAutoload(): bool
    {
        self::$phpvci ??= self::readPhpVciConfig();

        if (self::$phpvci === []) {
            return false;
        }

        if ((self::$phpvci['status'] ?? 0) === 2) {
            return true;
        }

        return !empty(self::$phpvci['version'])
            && !empty(self::$phpvci['paths']['target']);
    }

    /**
     * Resolves a file path using the PHPVCI boot configuration or the default
     * application directory.
     *
     * PHPVCI resolution is used for system files except `Boot.php`. Bootstrap
     * and plugin files are resolved from the default application directory.
     *
     * @param string $file The relative file path.
     * @param 'system'|'bootstrap'|'plugins' $from The source directory.
     *
     * @return string|null Returns the resolved absolute file path if the file
     *                     exists, or null otherwise.
     */
    public static function resolveFilepath(string $file, string $from = 'system'): ?string
    {
        $file = ltrim($file, '/');

        if ($file === '' || !in_array($from, ['system', 'bootstrap', 'plugins'], true)) {
            return null;
        }

        $usePhpVci = $from !== 'plugins'
            && !($from === 'system' && $file === 'Boot.php')
            && self::isPhpVciAutoload();

        $base = $usePhpVci
            ? self::toPath(self::$phpvci['paths']['target'])
            : '/';

        $root = match ($from) {
            'system'    => $base . 'system/',
            'bootstrap' => $base . 'bootstrap/',
            default     => '/system/plugins/'
        };

        $filepath = $usePhpVci
            ? $root . $file
            : Luminova::root($root, $file);

        return @is_file($filepath) ? $filepath : null;
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
     * @example - Example:
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
     * - CLI: writes error to STDERR and exits
     * - HTTP: returns a generic HTML error response
     *
     * @param string $message Error message to display or log.
     * @param string|null $title
     * @param string|null $description
     * 
     * @return never
     * @throws \RuntimeException When not in production mode.
     */
    private static function onError(
        string $message,
        ?string $title = null,
        ?string $description = null
    ): never
    {
        if (in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            fwrite(STDERR, preg_replace(
                '/<br\s*\/?>/i',
                PHP_EOL,
                strip_tags($message)
            ) ?? $message);

            exit(1);
        }

        while (ob_get_level() > 0 && @ob_end_clean()) {}
        http_response_code(500);

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        }

        $title ??= 'Application Autoload Error';
        $description ??= 'Boot Error #500';
        $escaped = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $file = APP_BASE_PATH . '/app/Errors/Defaults/xxx.php';

        if (is_file($file)) {
            $description = $title;
            $message = preg_replace(
                '/&lt;br\s*\/?&gt;/i',
                ' ',
                $escaped
            ) ?? $escaped;


            include $file;
            exit(1);
        }

        $message = preg_replace(
            '/&lt;br\s*\/?&gt;/i',
            '<br>',
            $escaped
        ) ?? $escaped;

        echo '<!DOCTYPE html><html lang="en"><head>'
            . '<meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' .  $title . '</title>'
            . '<style>'
            . 'body{margin:0;padding:40px;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;background:#f6f7f9;color:#333;}'
            . '.error{max-width:600px;margin:0 auto;background:#fff;border:1px solid #ddd;padding:20px;border-radius:6px;}'
            . 'h1{margin-top:0;font-size:20px;color:#c0392b;}'
            . 'p{margin:10px 0 0;overflow-wrap:anywhere;}'
            . '@media(prefers-color-scheme:dark){body{background:#161616}.error{background:#000;border-color:#2f2d2d;color:#fff}}'
            . '</style></head><body><div class="error">'
            . '<h1>' . $description . '</h1>'
            . '<p>' . $message . '</p>'
            . '</div></body></html>';

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
            $modules ??= self::import(
                'Config/Modules.php', 
                from:       'app', 
                resolvable: false, 
                return:     true
            );
        }

        $aliases = ($modules && is_array($modules)) 
            ? $modules['alias'] ?? []
            : [];

        if ($aliases === [] || !is_array($aliases)) {
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
        self::phpVciVersionCompare();
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
     * Loads PHPVCI boot configuration.
     *
     * @return array{
     *      status: 0,
     *      enabled: bool,
     *      version: string,
     *      paths: array{
     *          target: string
     *      }
     * } Return configurations.
     */
    private static function readPhpVciConfig(): array
    {
        $path = APP_BASE_PATH . '/.phpvci.php';

        if (!@is_file($path)) {
            return [];
        }

        $config = include $path;

        if (
            !is_array($config) 
            || empty($config['enabled'])
        ) {
            return [];
        }
        
        $config['status'] = 0;
        $config['version'] = trim($config['version'] ?? '');

        return $config;
    }

    /**
     * Loads and validates the PHPVCI boot configuration.
     *
     * @return void
     */
    private static function resolvePhpVciConfig(): void
    {
        self::$phpvci ??= self::readPhpVciConfig();

        if (self::$phpvci === []) {
            return;
        }

        if((self::$phpvci['status'] ?? 0) >= 0){
            return;
        }

        self::$phpvci['status'] = 1;

        foreach (['version', 'paths', 'target'] as $key) {
            $isTarget = $key === 'target';
            $isMissing = $isTarget 
                ? empty(self::$phpvci['paths'][$key]) 
                : empty(self::$phpvci[$key]);

            if (!$isMissing) {
                continue;
            }

            self::onError(
                sprintf(
                    'Invalid PHPVCI configuration: missing "%s".', 
                    $isTarget ? "paths.{$key}" : $key
                ),
                sprintf('Missing %s', ucfirst($key))
            );
        }

        self::$phpvci['status'] = 2;
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
        $isLive = (PRODUCTION && !STAGING);

        /**
         * Set error reporting.
         */
        if(PHP_SAPI !== 'cli'){
            error_reporting($isLive
                ? E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED
                : E_ALL
            );
            ini_set(
                'display_errors', 
                ((STAGING || !PRODUCTION) && Env::get('debug.display.errors', false)) ? '1' : '0'
            );

            ini_set('error_prepend_string', '<span class="php-core-error">');
            ini_set('error_append_string', '</span>');
            ini_set('log_errors', '1');
            ini_set('error_log', APP_BASE_PATH . '/writeable/logs/php-errors.log');
        }

        /**
         * Set exception tracing arguments reporting.
         */
        ini_set('zend.exception_ignore_args', $isLive ? '1' : '0');

        // Set max execution time (seconds)
        $limit = Env::get('script.execution.limit');

        if($limit !== null){
            Runtime::setExecutionTime((int) $limit);
        }

        // Set default timezone
        $tz = (string) Env::get('app.timezone');

        if($tz !== ''){
            Runtime::setTimezone($tz);
        }

        // Set internal encoding (if defined)
        $enc = (string) Env::get('app.mb.encoding');

        if($enc !== ''){
            Runtime::setEncoding($enc);
        }

        // Control behavior on client disconnect
        $abort = Env::get('script.ignore.abort');
        
        if($abort !== null){
            Runtime::setIgnoreUserAbort((bool) $abort);
        }
    }

    /**
     * Safely includes a PHP module from the configured boot or application directory.
     *
     * When resolvable loading is enabled, system and bootstrap modules are loaded
     * from the PHPVCI target if its configuration is available.
     *
     * @param string $file The relative module file path.
     * @param 'app'|'system'|'bootstrap'|'plugins' $from The source directory.
     * @param bool $resolvable Whether to resolve system and bootstrap modules through PHPVCI.
     * @param bool $return Whether to return the included file's result.
     * @param bool $throw Whether to throw an exception if the file is missing.
     *
     * @return mixed Returns the included file's result when $return is true,
     *               0 when successfully included without returning its result,
     *               or false if the file cannot be included.
     *
     * @throws \RuntimeException If the file is missing and $throw is true.
     */
    private static function import(
        string $file,
        string $from = 'system',
        bool $resolvable = true,
        bool $return = false,
        bool $throw = false
    ): mixed 
    {
        if (
            $resolvable
            && in_array($from, ['system', 'bootstrap'], true)
            && self::isPhpVciAutoload()
        ) {
            $base = self::toPath(self::$phpvci['paths']['target']);
            $root = $base . $from . '/';
        } else {
            $root = match ($from) {
                'app'       => APP_BASE_PATH . '/app/',
                'bootstrap' => APP_BASE_PATH . '/bootstrap/',
                'system'    => __DIR__ . '/',
                'plugins'   => __DIR__ . '/plugins/',
                default     => null,
            };
        }

        $file = ltrim($file, '/');
        $filepath = $root . $file;

        if ($root !== null && @is_file($filepath)) {
            if ($return) {
                return require $filepath;
            }

            require_once $filepath;

            return 0;
        }

        $message = ($file === 'autoload.php')
            ? sprintf('Luminova requires Composer "%s" for autoloading.', $file)
            : sprintf(
                'Module file "%s" not found.%s',
                $file,
                self::isProduction()
                    ? ''
                    : sprintf(' Checked path: %s', $filepath)
            );

        if ($throw) {
            throw new \RuntimeException($message);
        }

        self::onError(
            $message,
            'Autoload Error',
            'Failed to resolve module path.'
        );

        return false;
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
            exit(STATUS_ERROR);
        }

        error_reporting(0);
        ini_set('display_errors', 0);

        $retry = Env::get('app.maintenance.retry', 3600);

        try{
            Header::sendNoCacheHeaders(503, retry: $retry);
            self::import(
                file:       'Errors/Defaults/maintenance.php',
                from:       'app',
                resolvable: false,
                throw:      true
            );
        }catch(Throwable){
            Luminova::terminate(
                503, 
                $message, 
                'Service Unavailable', 
                $retry,
                hookOnTerminated: false
            );
        } finally{
            exit(STATUS_ERROR);
        }
    }

    /**
     * Check luminova shared module version compatibility.
     * 
     * @return void
     */
    private static function phpVciVersionCompare(): void
    {
        if(!self::isPhpVciAutoload()){
            return;
        }

        $isSatisfies = false;
        $version = self::$phpvci['version'];

        if($version === Luminova::VERSION){
            return;
        }

        if($version !== ''){
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
        }

        if (!$isSatisfies) {
            self::onError(sprintf(
                'Luminova version constraint not satisfied the required phpvci.version: %s, current %s.',
                $version,
                Luminova::VERSION
            ), 'phpvci version mismatch');
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
            self::import('Utils/Global.php', from: 'app', resolvable: false);
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