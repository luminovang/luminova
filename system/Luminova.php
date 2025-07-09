<?php
/**
 * Luminova Framework foundation.
 * 
 * ██╗     ██╗   ██╗███╗   ███╗██╗███╗   ██╗ ██████╗ ██╗   ██╗ █████╗ 
 * ██║     ██║   ██║████╗ ████║██║████╗  ██║██╔═══██╗██║   ██║██╔══██╗
 * ██║     ██║   ██║██╔████╔██║██║██╔██╗ ██║██║   ██║██║   ██║███████║
 * ██║     ██║   ██║██║╚██╔╝██║██║██║╚██╗██║██║   ██║██║   ██║██╔══██║
 * ███████╗╚██████╔╝██║ ╚═╝ ██║██║██║ ╚████║╚██████╔╝╚██████╔╝██║  ██║
 * ╚══════╝ ╚═════╝ ╚═╝     ╚═╝╚═╝╚═╝  ╚═══╝ ╚═════╝  ╚═════╝ ╚═╝  ╚═╝
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova;

use \Throwable;
use \ReflectionClass;
use \App\Application;
use \Luminova\Http\Header;
use \Luminova\Logger\Logger;
use \Luminova\Errors\ErrorHandler;
use \Luminova\Debugger\Performance;

final class Luminova 
{
    /**
     * Framework version code.
     * 
     * @var string VERSION
     */
    public const VERSION = '3.6.7';

    /**
     * Framework version name.
     * 
     * @var string VERSION_NAME
     */
    public const VERSION_NAME = 'Hermes';

    /**
     * Minimum required php version.
     * 
     * @var string MIN_PHP_VERSION 
     */
    public const MIN_PHP_VERSION = '8.0';

    /**
     * Command line tool version.
     * 
     * @var string NOVAKIT_VERSION
     */
    public const NOVAKIT_VERSION = '3.0.0';

    /**
     * Server base path for router.
     * 
     * @var ?string $base
     */
    private static ?string $base = null;

    /**
     * Request URL segments.
     * 
     * @var ?string $segments
     */
    private static ?string $segments = null;

    /**
     * System paths for filtering.
     * 
     * @var array<int,string> $systemPaths
     */
    public static array $systemPaths = [
        'public',
        'node',
        'bin',
        'system',  
        'bootstrap',
        'resources', 
        'writeable', 
        'libraries', 
        'routes', 
        'builds',
        'app'
    ];

    /**
     * Controller class information.
     * 
     * @var array<string,string> $classMetadata
     */
    private static array $classMetadata = [
        'filename'    => null,
        'uri'         => null,
        'namespace'   => null,
        'method'      => null,
        'attrFiles'   => 0,
        'cache'       => false,
        'staticCache' => false,
    ];

    /**
     * Get the framework copyright information.
     *
     * @param bool $userAgent Whether to return user-agent information instead (default: false).
     * 
     * @return string Return framework copyright message or user agent string.
     * @internal
     */
    public static final function copyright(bool $userAgent = false): string
    {
        if ($userAgent) {
            return sprintf(
                'LuminovaFramework-%s/%s (PHP; %s; %s) - https://luminova.ng',
                self::VERSION_NAME, 
                self::VERSION,
                PHP_VERSION,
                PHP_OS_FAMILY
            );
        }

        return sprintf('PHP Luminova (%s)', self::VERSION);
    }

    /**
     * Get the framework version name or code.
     * 
     * @param bool $integer Return version code or version name (default: name).
     * 
     * @return string|int Return version name or code.
     */
    public static final function version(bool $integer = false): string|int
    {
        return $integer ? (int) \Luminova\Funcs\strict(self::VERSION, 'int') : self::VERSION;
    }

    /**
     * Start or stop application profiling.
     * 
     * @param string $action The name of the action (e.g, start or stop).
     * @param array|null $context Additional information to pass to profiling (default: null).
     * 
     * @return void
     */
    public static final function profiling(string $action, ?array $context = null): void
    {
        if(!PRODUCTION && env('debug.show.performance.profiling', false)){
            ($action === 'start')
                ? Performance::start() 
                : Performance::stop(null, $context);
        }
    }

    /**
     * Registers the global error and shutdown handlers.
     * 
     * Sets up the application's error handling system to catch PHP errors and 
     * handle fatal shutdown events via `shutdown()` for graceful fallback.
     * 
     * @return void
     */
    public static function initialize(): void 
    {
        set_error_handler([self::class, 'handle']);
        register_shutdown_function([self::class, 'shutdown']);
    }

    /**
     * Get error logging level.
     * 
     * @param string|int $errno The error code.
     * 
     * @return string Return error log level by error code.
     */
    public static function getLevel(string|int $errno): string 
    {
        return match ($errno) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => 'critical',
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => 'warning',
            E_PARSE => 'emergency',
            E_NOTICE, E_USER_NOTICE => 'notice',
            E_DEPRECATED, E_USER_DEPRECATED => 'info',
            E_RECOVERABLE_ERROR => 'error',
            E_ALL, 0 => 'exception',
            default => 'php_error'
        };
    }

    /**
     * Display system errors based on the given error.
     *
     * This method includes an appropriate error view based on the environment and request type.
     *
     * @param ErrorHandler|null $stack The error stack containing errors to display.
     * 
     * @return void
     */
    private static function display(?ErrorHandler $stack = null): void 
    {
        [$isTraceable, $view, $path] = self::errRoute($stack);
        $isCommand = $view === 'cli.php';

        // Get tracer for php error if not available
        if($isTraceable || SHOW_DEBUG_BACKTRACE){
            ErrorHandler::setBacktrace(debug_backtrace(), true);
        }
        
        if (!$isCommand) {
            Header::clearOutputBuffers();
        }

        if(file_exists($path . $view)){
            include_once $path . $view;
            return;
        }

        // Give up and output basic issue message
        ErrorHandler::notify($isCommand);
    }

    /**
     * Returns the base public controller directory.
     * 
     * This strips the controller script name from `SCRIPT_NAME` and normalizes
     * the path using forward slashes.
     *
     * @return string Return the base path ending with a forward slash (e.g. `/`, `/admin/`).
     */
    public static function getBase(): string
    {
        if (self::$base !== null) {
            return self::$base;
        }

        $script = $_SERVER['SCRIPT_NAME'] ?? '/';

        if($script === '/'){
            return self::$base = $script;
        }

        $script = str_replace('\\', '/', $script);
        $lastSlash = strrpos($script, '/');
        
        return self::$base = ($lastSlash > 0) ? substr($script, 0, $lastSlash) . '/' : '/';
    }

    /**
     * Convert a relative path to a full absolute URL.
     *
     * Automatically removes system-relative parts like `public/`, and resolves base URL 
     * based on the current environment (development vs production).
     *
     * @param string $path Relative file path to convert.
     * 
     * @return string Return fully qualified URL.
     */
    public static function toAbsoluteUrl(string $path): string
    {
        if (NOVAKIT_ENV === null && !PRODUCTION) {
            $base = rtrim(self::getBase(), 'public/');
            $basePos = strpos($path, $base);

            if ($basePos !== false) {
                $path = trim(substr($path, $basePos + strlen($base)), TRIM_DS);
            }
        } else {
            $path = trim(self::filterPath($path), TRIM_DS);
        }

        if (str_starts_with($path, 'public/')) {
            $path = ltrim($path, 'public/');
        }

        return \Luminova\Funcs\start_url($path);
    }

    /**
     * Get the request url segments as relative.
     * 
     * Resolves the request URI as a relative path, without query string or base path.
     *
     * @return string Return the normalized URI segment path (e.g., `/products/view/10`)
     */
    public static function getUriSegments(): string
    {
        if (self::$segments === null) {
            self::$segments = '/';

            if (!empty($_SERVER['REQUEST_URI'])) {
                $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen(self::getBase()));

                if ($uri !== '' && ($pos = strpos($uri, '?')) !== false) {
                    $uri = substr($uri, 0, $pos);
                }

                self::$segments = '/' . trim($uri, '/');
            }
        }

        return self::$segments;
    }

    /**
     * Get the current view segments as array.
     * 
     * Breaks the request URI into an array of individual segments.
     *
     * @return array<int,string> Return URI segments (e.g., `['products', 'view', '10']`)
     */
    public static function getSegments(): array
    {
        $segments = self::getUriSegments();

        if ($segments === '/') {
            return [''];
        }

        $parts = explode('/', trim($segments, '/'));

        // Remove "public" segment if present
        $index = array_search('public', $parts);

        if ($index !== false) {
            array_splice($parts, $index, 1);
        }

        return $parts;
    }

    /**
     * Generate a unique cache identifier for the current request URL.
     *
     * Normalizes the request method and URI, strips extensions if configured,
     * and converts into a hashed string safe for storage and retrieval.
     * 
     * @return string Return a Unique MD5 cache ID.
     */
    public static function getCacheId(): string 
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? 'index';

        $url = strtr($method . $uri, [
            '/' => '-', 
            '?' => '-', 
            '&' => '-', 
            '=' => '-', 
            '#' => '-'
        ]);

        // Remove file extension for static cache formats
        // To avoid creating 2 versions of same cache
        // While serving static content (e.g, .html).

        if (($types = env('page.caching.statics', false)) !== false) {
            $url = preg_replace('/\.(' . $types . ')$/i', '', $url);
        }

        return md5($url);
    }

    /**
     * Determines if the current request is an API request.
     * 
     * Checks if the first URI segment matches the API prefix 
     * (e.g., `/example.com/api`, `public/api` or custom api prefix based on env(app.api.prefix)),
     * and optionally treats AJAX requests as API calls.
     * 
     * @param bool $includeAjax If true, treats XMLHttpRequest (AJAX) as an API request.
     * 
     * @return bool Return true if the request starts with the API prefix or is AJAX (when enabled).
     */
    public static function isApiPrefix(bool $includeAjax = false): bool
    {
        static $prefix = null;

        if ($prefix === null) {
            $prefix = defined('IS_UP') ? env('app.api.prefix', 'api') : 'api';
        }

        $segments = self::getSegments();

        if ($segments !== [] && $segments[0] === $prefix) {
            return true;
        }

        return $includeAjax 
            && isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0;
    }

    /**
     * Determines if the application is running in CLI (Command-Line Interface) mode.
     *
     * @return bool Return true if running via CLI; false if it's a web request.
     */
    public static function isCommand(): bool
    {
        static $cli = null;

        if ($cli !== null) {
            return $cli;
        }

        // If typical web environment vars are set, it's not CLI
        if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['HTTP_USER_AGENT'])) {
            return $cli = false;
        }

        return $cli = PHP_SAPI === 'cli'
            || defined('STDIN')
            || !empty($_ENV['SHELL'])
            || isset($_SERVER['argv']);
    }

    /**
     * Filters a full file path by removing everything before the first known system directory.
     * 
     * Useful for hiding sensitive server paths when displaying errors or logs. The resulting path
     * will always start from one of the known system directories (like `app`, `system`, etc.).
     *
     * @param string $path Full file path to filter.
     * 
     * @return string Return filtered path starting from project root, or original path if no match found.
     */
    public static function filterPath(string $path): string 
    {
        // normalize for cross-platform support
        $normalized = str_replace('\\', '/', $path); 

        foreach (self::$systemPaths as $dir) {
            $needle = '/' . trim($dir, '/') . '/';

            if (($pos = strpos($normalized, $needle)) !== false) {
                return substr($normalized, $pos + 1);
            }
        }

        return $normalized;
    }

    /**
     * Check whether a class or object has a property, with optional static-only filtering.
     *
     * Uses `ReflectionClass` when `$staticOnly` is true to determine if a property is declared as `static`.
     * For general use, it falls back to `property_exists()` for better performance.
     *
     * @param class-string|object $objectOrClass The class name or object to check.
     * @param string $property The property name to check for.
     * @param bool $staticOnly If true, only returns true for static properties (default: false).
     *
     * @return bool True if the property exists (and is static if required), false otherwise.
     *
     * @example - Usages:
     * ```php
     * Luminova::isPropertyExists(MyClass::class, 'config', true); // true if static
     * Luminova::isPropertyExists(MyClass::class, 'config');       // true if static or non-static
     * ```
     */
    public static function isPropertyExists(
        string|object $objectOrClass, 
        string $property, 
        bool $staticOnly = false
    ): bool 
    {
        if (!$staticOnly) {
            return property_exists($objectOrClass, $property);
        }

        try {
            $ref = new ReflectionClass($objectOrClass);
            return $ref->hasProperty($property) && $ref->getProperty($property)->isStatic();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get the base name(s) from fully qualified class name(s).
     *
     * Accepts a single FQCN (e.g., `App\Controllers\HomeController`) or a comma-separated list.
     * Removes leading slashes and namespace paths, returning just the class names.
     *
     * @param string $class One or more fully qualified class names, separated by commas if multiple.
     * 
     * @return string Return the base class name, or comma-separated base class names (e.g., `HomeController, UserModel`).
     *
     * @example - Usages:
     * 
     * ```php
     * Luminova::getClassBaseNames('\App\Controllers\HomeController'); // HomeController
     * Luminova::getClassBaseNames('App\Models\User, App\Services\Log'); // User, Log
     * ```
     */
    public static function getClassBaseNames(string $class): string
    {
        if (!$class) {
            return '';
        }

        if (str_contains($class, ',')) {
            return implode(', ', array_map(function (string $ns): string {
                $ns = trim($ns, " \t\n\r\0\x0B\\");
                return basename(str_replace('\\', '/', $ns));
            }, explode(',', $class)));
        }

        $class = ltrim($class, '\\');
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Handles standard PHP runtime errors.
     * 
     * This method is registered as the global error handler and is called automatically
     * whenever a recoverable PHP error occurs (e.g. warnings, notices, deprecations).
     * 
     * It checks the current error reporting level to determine if the error should be handled.
     * If so, it logs the error message using a structured format and marks it as handled.
     *
     * @param int    $errno    The level/severity of the error raised.
     * @param string $errstr   The error message.
     * @param string $errFile  The full path to the file where the error occurred.
     * @param int    $errLine  The line number in the file where the error occurred.
     * 
     * @return bool Returns true if the error was handled (i.e. not suppressed), false otherwise.
     */
    public static function handle(int $errno, string $errstr, string $errFile, int $errLine): bool 
    {
        if (error_reporting() === 0) {
            return false;
        }

        $errorCode = ErrorHandler::getErrorCode($errstr, $errno);
        self::log(self::getLevel($errno), sprintf(
            "[%s (%s)] %s File: %s Line: %s.", 
            ErrorHandler::getErrorName($errorCode),
            (string) $errorCode,
            $errstr,
            self::filterPath($errFile),
            (string) $errLine
        ));

        return true;
    }

    /**
     * Final shutdown handler for fatal or last-minute errors.
     * 
     * This method is called when the script terminates and an error has occurred.
     * It handles fatal errors gracefully by:
     * - Determining if the error is fatal.
     * - Displaying a custom error page if needed.
     * - Logging the error details if in production or not fatal.
     * - Forwarding the error data to `Application::onShutdown()`.
     *
     * @return void
     */
    public static function shutdown(): void 
    {
        if (($error = error_get_last()) === null || !isset($error['type'])) {
            return;
        }

        // If handled by developer in application
        // Then terminate handling here.
        if(!Application::onShutdown($error)){
            return;
        }

        $isFatal = self::isFatal($error['type']);
        $isDisplay = ini_get('display_errors');
        $errorCode = ErrorHandler::getErrorCode($error['message'], $error['type']);
        $errName = ErrorHandler::getErrorName($errorCode);

        // If error display is not enabled or error occurred on production
        // Display custom error page.
        if(!$isDisplay || PRODUCTION){
            $stack = $isFatal ? new ErrorHandler(
                $error['message'], 
                $errorCode,
                null,
                $error['file'],
                $error['line'],
                $errName
            ) : null;

            self::display($stack);
        }

        // If message is not empty and is not fatal error or on projection
        // Log the error message.
        if(!$isFatal || PRODUCTION){
            self::log(self::getLevel($error['type']), sprintf(
                "[%s (%s)] %s File: %s Line: %s.", 
                $errName,
                (string) $errorCode,
                $error['message'],
                $error['file'],
                (string) $error['line']
            ));
        }
    }

    /**
     * Check if error is fatal.
     * 
     * @param string|int $errno The error code.
     * 
     * @return bool Return true if is fatal, otherwise false.
     * 
     * - Fatal run-time errors.
     * - Fatal errors that occur during PHP's initial startup.
     * - Fatal compile-time errors.
     * - Compile-time parse errors.
     */
    public static function isFatal(string|int $errno): bool 
    {
        return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
    }

    /**
     * Retrieve all metadata related to the currently routed controller class.
     *
     * This data typically includes controller name, method, namespace, etc.
     * 
     * **The returned array includes:**
     * 
     * - `filename`:    (string|null) The resolved full path to the controller file.
     * - `uri`:         (string|null) The matched route URI.
     * - `namespace`:   (string|null) The fully qualified controller class name.
     * - `method`:      (string|null) The controller class method that was executed.
     * - `attrFiles`:   (int) The number of controllers was discovered via attribute routing.
     * - `cache`:       (bool) Whether this route was cached.
     * - `staticCache`: (bool) Whether this route was serve from static cache.
     *
     * @return array<string,string> Return an associative array of routed class information.
     *
     * @internal Used by the routing system to track resolved route details.
     */
    public static function getClassMetadata(): array
    {
        return self::$classMetadata;
    }

    /**
     * Sets or updates a single metadata entry for the routed controller class.
     *
     * Common keys include:
     * - `filename`, `uri`, `namespace`, `method`, `attrFiles`, `cache`, `staticCache`
     * 
     * @param string $key Metadata key (e.g., 'namespace', 'method').
     * @param mixed  $value Corresponding value to assign.
     *
     * @return void
     *
     * @internal Used by the routing system to assign individual route values.
     */
    public static function addClassMetadata(string $key, mixed $value): void
    {
        self::$classMetadata[$key] = $value;
    }

    /**
     * Merge a new set of metadata into the existing routed controller class info.
     * 
     * This method replaces existing keys with the provided ones.
     * 
     * All expected keys are:
     * - `filename`, `uri`, `namespace`, `method`, `attrFiles`, `cache`, `staticCache`
     *
     * @param array<string,mixed> $metadata An associative array Key-value pairs of controller routing metadata.
     *
     * @return void
     *
     * @internal Used by the routing system to initialize class routing context.
     */
    public static function setClassMetadata(array $metadata): void
    {
        self::$classMetadata = array_replace(
            self::$classMetadata, 
            $metadata
        );
    }

    /**
     * Resolves the appropriate error view based on application state and context.
     *
     * @param ErrorHandler|null $stack The current error handler stack, if available.
     * 
     * @return array{0:bool,1:string,2:string} 
     *         Returns an array containing:
     *         [0] bool   - Whether a tracer-specific view was selected.
     *         [1] string - The view file name to render.
     *         [2] string - The absolute path to the error views directory.
     */
    private static function errRoute(?ErrorHandler $stack): array
    {
        $path = APP_ROOT . 'app/Errors/Defaults/';

        if (defined('IS_UP') && PRODUCTION) {
            if (env('logger.mail.logs')) {
                return [true, 'mailer.php', $path];
            } 
            
            if (env('logger.remote.logs')) {
                return [true, 'remote.php', $path];
            }
        }

        $view = match (true) {
            self::isCommand() => 'cli.php',
            self::isApiPrefix(true) => defined('IS_UP')
                ? env('app.api.prefix', 'api') . '.php'
                : 'api.php',
            default => ($stack instanceof ErrorHandler) ? 'errors.php' : 'info.php',
        };

        return [false, $view, $path];
    }

    /**
     * Gracefully log error messages.
     * 
     * Logs messages using the internal logger if the application is marked as up (IS_UP),
     * or writes to a file-based fallback log if not.
     *
     * @param string $level   The log level (e.g. error, warning, info).
     * @param string $message The log message to write.
     * 
     * @return void
     */
    private static function log(string $level, string $message): void 
    {
        if (defined('IS_UP')) {
            Logger::dispatch($level, $message);
            return;
        }

        $path = __DIR__ . '/../writeable/logs/';
        $filename = "{$path}{$level}.log";

        if (!is_dir($path) && !@mkdir($path, 0777, true) && !is_dir($path)) {
            return;
        }

        $formatted = sprintf('[%s] [%s] [Boot::Fallback] %s', 
            strtoupper($level),
            date('Y-m-d\TH:i:sP'),
            $message
        );

        if (@file_put_contents($filename, $formatted . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            @chmod($filename, 0666);
        }
    }

    /** 
     * Trim file directory both sides.
     *
     * @param string $path The path to trim.
     *
     * @return string Return trimmed path (e.g, `foo/bar/`).
     * @internal
     */
    public static function bothTrim(string $path): string 
    {
        return trim($path, TRIM_DS) . DIRECTORY_SEPARATOR;
    }
}