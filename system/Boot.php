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

use \Throwable;
use \App\Kernel;
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
use function strlen;
use function substr;

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

        if (
            !Runtime::isProduction()
            && !Runtime::isStaging()
            && !Runtime::isLocalhost()
        ) {
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
        if (!self::isCli()) {
            self::onError(sprintf(
                'Method "Boot::cli()" can only be called in CLI context. '
                . 'Use "Boot::http()" instead. Current SAPI: %s.',
                PHP_SAPI
            ), 'Logic Error');
        }

        self::warmup();

        error_reporting(E_ALL);

        putenv('TMP=' . TMPDIR);
        putenv('TEMP=' . TMPDIR);

        self::trySetIniValue('display_errors', '1');
        self::trySetIniValue('display_startup_errors', '1');
        self::trySetIniValue('html_errors', '0');
        self::trySetIniValue('log_errors', '1');
        self::trySetIniValue('error_log', 'php://stderr');

        defined('CLI_ENVIRONMENT')
            || define('CLI_ENVIRONMENT', Env::get('cli.environment.mode', 'testing'));

        Runtime::defineStandardStreams();

        Error::register();
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
     * @codeCoverageIgnore
     */
    public static function warmup(): void
    {
        defined('TRIM_DS') || define('TRIM_DS', '/\\');

        /**
         * Luminova application base directory.
         * 
         * @var string APP_BASE_PATH
         * @see ../
         */
        defined('APP_BASE_PATH') || define(
            'APP_BASE_PATH',
            self::toPath(
                path: getenv('APP_BASE_PATH') ?: ($_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)),
                suffix: ''
            )
        );

        /**
         * Luminova temporary directory environment variables.
         * 
         * @var string TMPDIR
         * @see ../writeable/tmp
         */
        defined('TMPDIR') || define('TMPDIR', APP_BASE_PATH . '/writeable/tmp');
        putenv('TMPDIR=' . TMPDIR);

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
            defined('IS_ENV_REGISTERED') || define('IS_ENV_REGISTERED', true);
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

        if(MAINTENANCE){
            return;
        }

        // start recording
        Runtime::profiling('start');
    }

    /**
     * Attempts to set a PHP configuration directive at runtime.
     *
     * Returns the previous directive value on success, or false if the
     * directive cannot be updated or ini_set() is unavailable.
     *
     * @param string $name The PHP configuration directive name.
     * @param mixed $value The value to assign.
     *
     * @return void
     */
    private static function trySetIniValue(string $name, mixed $value): void
    {
        static $available = null;

        if ($available ??= function_exists('ini_set')) {
            @ini_set($name, (string) $value);
            return;
        }
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
        static $on = null;

        self::$phpvci ??= self::readPhpVciConfig();
        $status = self::$phpvci['status'] ?? 0;

        if ($status === 1 || self::$phpvci === []) {
            return false;
        }

        if ($status === 2 || $on === true) {
            return true;
        }

        return $on = (
            !empty(self::$phpvci['version'])
            && !empty(self::$phpvci['paths']['target'])
            && is_dir(self::$phpvci['paths']['target'])
        );
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
            ? self::$phpvci['paths']['target']
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
     * Resolves an error template file path.
     *
     * Searches the framework's default error templates directory first,
     * followed by the application's error defaults directory.
     *
     * When PHPVCI shared autoloading is enabled, framework templates are
     * resolved from the shared framework installation.
     *
     * @param 'xxx.php'|'errors.php'|'view.error.php'|'maintenance.php' $filename The error template filename.
     * @param bool $appOnly Whether to restrict lookup to application error templates.
     *
     * @return string|null Returns the absolute template file path, or null if not found.
     * 
     * @see /app/Errors/Defaults/ Resolve from application error template.
     * @see /system/Foundation/Error/Templates/ Resolve from framework error template.
     * @see /{phpvci:paths.target}/Foundation/Error/Templates/ Resolve from framework phpvci error template.
     */
    public static function resolveErrorTemplate(
        string $filename = 'xxx.php',
        bool $appOnly = false
    ): ?string
    {
        $filename = ltrim($filename, '/\\');
        $files = [];

        if(!$appOnly){
            $base = self::isPhpVciAutoload() 
                ? self::$phpvci['paths']['target']
                : __DIR__ . '/';
 
            $files[] = "{$base}Foundation/Error/Templates/{$filename}";
        }

        if(defined('APP_BASE_PATH')){
            $files[] = APP_BASE_PATH . "/app/Errors/Defaults/{$filename}";
        }

        foreach($files as $file){
            if (is_file($file)) {
                return $file;
            }
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
        if($path === ''){
            return '';
        }

        $path = preg_replace(
            '~[/\\\\]+~', 
            '/', 
            $path
        );

        return rtrim($path, '/') 
            . $suffix;
    }

    /**
     * Check if PHP_SAPI is cli.
     *
     * @return bool
     */
    private static function isCli(): bool 
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg'], true);
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
        if (self::isCli()) {
            fwrite(
                STDERR, 
                preg_replace('/<br\s*\/?>/i', PHP_EOL, strip_tags($message)) 
                    ?? $message
            );
            exit(1);
        }

        self::trySetIniValue('display_errors', '0');
        self::trySetIniValue('display_startup_errors', '0');

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

        $file = self::resolveErrorTemplate('xxx.php');

        if($file !== null){
            $description = $title;
            $message = preg_replace('/&lt;br\s*\/?&gt;/i', ' ', $escaped) 
                ?? $escaped;

            include $file;
            exit(1);
        }

        $message = preg_replace('/&lt;br\s*\/?&gt;/i', '<br>', $escaped) 
            ?? $escaped;

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
                $entry->tryLog();
            } catch(Throwable) {}
        }

        return $registered > 0;
    }

    /**
     * Finalizes the bootstrapping process.
     *
     * Loads composer module and custom framework feature. 
     * Sets `APP_BOOT_COMPLETED` constant if not defined.
     *
     * @return void
     */
    private static function finish(): void
    {
        self::phpVciVersionCompare();
        self::features();

        defined('APP_BOOT_COMPLETED') || define('APP_BOOT_COMPLETED', true);

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
     * Resolves dynamic target paths and adds the configuration status.
     *
     * @return array{
     *     status: 0,
     *     enabled: bool,
     *     version: string,
     *     paths: array{
     *         target: string
     *     }
     * } Returns the PHPVCI configuration, or an empty array if unavailable or disabled.
     */
    public static function readPhpVciConfig(): array
    {
        $path = APP_BASE_PATH . '/.phpvci.php';

        if (!is_file($path)) {
            return [];
        }

        $config = include $path;

        if (!is_array($config) || empty($config['enabled'])) {
            return [];
        }

        $version = $config['version'] ?? '';
        $target = $config['paths']['target'] ?? '';

        if (is_callable($target)) {
            $target = $target();
        }

        $config['status'] = 'ideal';
        $config['version'] = is_string($version) ? trim($version) : '';
        $config['paths']['target'] = is_string($target) ? self::toPath(trim($target)) : '';

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

        if (
            self::$phpvci === [] 
            || (self::$phpvci['status'] ?? 0) >= 0
        ) {
            return;
        }

        self::$phpvci['status'] = 1;

        $required = [
            'version'      => 'Version',
            'paths.target' => 'Target',
        ];

        foreach ($required as $key => $label) {
            $isTarget = $key === 'paths.target';
            $value = $isTarget
                ? (self::$phpvci['paths']['target'] ?? null)
                : (self::$phpvci[$key] ?? null);

            if (!is_string($value) || $value === '') {
                self::onError(
                    sprintf('Invalid PHPVCI configuration: missing "%s".', $key),
                    sprintf('Missing %s', $label)
                );
                continue;
            }

            if($isTarget && !is_dir($value)){
                self::onError(
                    sprintf(
                        'PHPVCI package: target "%s" not found or is not a valid directory.',
                        $value
                    ),
                    'Invalid Target'
                );
            }
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
        $isLive = (Runtime::isProduction() && !Runtime::isStaging());

        /**
         * Set error reporting.
         */
        if(!self::isCli()){
            error_reporting($isLive
                ? E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED
                : E_ALL
            );
            self::trySetIniValue(
                'display_errors', 
                ((Runtime::isStaging() || !Runtime::isProduction()) 
                    && Env::get('debug.display.errors', false)) ? '1' : '0'
            );

            self::trySetIniValue('error_prepend_string', '<span class="php-core-error">');
            self::trySetIniValue('error_append_string', '</span>');
            self::trySetIniValue('log_errors', '1');
            self::trySetIniValue('error_log', APP_BASE_PATH . '/writeable/logs/php-errors.log');
        }

        /**
         * Set exception tracing arguments reporting.
         */
        self::trySetIniValue('zend.exception_ignore_args', $isLive ? '1' : '0');

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
            $base = self::$phpvci['paths']['target'];
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
                Runtime::isProduction()
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
     * Load application is undergoing maintenance.
     * 
     * @return never
     */
    private static function maintenance(): never 
    {
        error_reporting(E_ALL);
        self::trySetIniValue('display_errors', '0');
        self::trySetIniValue('display_startup_errors', '0');
        self::trySetIniValue('log_errors', '1');

        try{
            $msg = 'Error: (503) System undergoing maintenance!';

            if(Runtime::isCommand()){
                Terminal::error($msg);
                exit(STATUS_ERROR);
            }

            $retry = Env::get('app.maintenance.retry', 3600);

            $file = self::resolveErrorTemplate(
                'maintenance.php', 
                appOnly: true
            );

            if($file === null){
                Luminova::terminate(
                    503, 
                    $msg, 
                    'Service Unavailable', 
                    $retry,
                    hookOnTerminated: false
                );
            }

            Header::sendNoCacheHeaders(503, retry: $retry);
            include $file;
        }catch(Throwable $e){
            Error::tryLog(
                'error',
                sprintf(
                    "[Maintenance] %s in %s:%d\n",
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
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
        if(MAINTENANCE || !self::isPhpVciAutoload()){
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
        if (PRODUCTION || Runtime::isCommand()) {
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
        if(defined('APP_BOOT_COMPLETED')){
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

        if(MAINTENANCE){
            return;
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