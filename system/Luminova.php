<?php
/**
 * Luminova Framework foundation.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova;

use \Luminova\Errors\ErrorHandler;
use \Luminova\Debugger\Performance;
use \Luminova\Http\Header;
use \Luminova\Logger\Logger;

final class Luminova 
{
    /**
     * Framework version code.
     * 
     * @var string VERSION
     */
    public const VERSION = '3.5.6';

    /**
     * Framework version name.
     * 
     * @var string VERSION_NAME
     */
    public const VERSION_NAME = 'Xtra';

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
    public const NOVAKIT_VERSION = '2.9.8';

    /**
     * Server base path for router.
     * 
     * @var ?string $base
     */
    private static ?string $base = null;

    /**
     * Request url segments.
     * 
     * @var ?string $segments
     */
    private static ?string $segments = null;

    /**
     * System paths for filtering.
     * 
     * @var array<int,string> $systemPaths
     */
    private static array $systemPaths = [
        'public',
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
     * Get the framework copyright information.
     *
     * @param bool $userAgent Weather to return user-agent information instead (default: false).
     * 
     * @return string Return framework copyright message or user agent string.
     * @internal
     */
    public static final function copyright(bool $userAgent = false): string
    {
        if($userAgent){
            return sprintf(
                'LuminovaFramework/%s (PHP; %s; %s) - https://luminova.ng',
                self::VERSION, 
                PHP_VERSION,
                PHP_OS_FAMILY
            );
        }

        return 'PHP Luminova (' . self::VERSION . ')';
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
        return $integer ? (int) strict(self::VERSION, 'int') : self::VERSION;
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
     * Initializes and register global error handler.
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
        [$tracer, $view, $path] = self::errRoute($stack);

        // Get tracer for php error if not available
        if(($tracer || SHOW_DEBUG_BACKTRACE)){
            ErrorHandler::setBacktrace(debug_backtrace(), true);
        }
        
        if ($view !== 'cli.php') {
            Header::clearOutputBuffers();
        }

        if(file_exists($path . $view)){
            include_once $path . $view;
            return;
        }

        // Give up and output basic issue message
        ErrorHandler::notify($view === 'cli.php');
    }

    /**
     * Retrieve the server public controller directory.
     * Remove the index controller file name from the scrip name.
     *
     * @return string Return public directory path.
     */
    public static function getBase(): string
    {
        if (self::$base !== null) {
            return self::$base;
        }

        self::$base = ($_SERVER['SCRIPT_NAME'] ?? '/');

        if(self::$base === '/'){
            return self::$base;
        }

        self::$base = str_replace(['/', '\\'], '/', self::$base);
        if (($last = strrpos(self::$base, '/')) !== false && $last > 0) {
            self::$base = substr(self::$base, 0, $last) . '/';
            return self::$base;
        }

        self::$base = '/';
        return self::$base;
    }

    /**
     * Convert relative path to absolute url.
     *
     * @param string $path Path to convert to absolute url.
     * 
     * @return string Return full url without system path.
     */
    public static function toAbsoluteUrl(string $path): string
    {
        if(NOVAKIT_ENV === null && !PRODUCTION){
            $base = rtrim(self::getBase(), 'public/');
            
            if (($basePos = strpos($path, $base)) !== false) {
                $path = trim(substr($path, $basePos + strlen($base)), TRIM_DS);
            }
        }else{
            $path = trim(self::filterPath($path), TRIM_DS);
        }

        if(str_starts_with($path, 'public/')){
            $path = ltrim($path, 'public/');
        }
 
        if(PRODUCTION){
            return APP_URL . '/' . $path;
        }

        $hostname = $_SERVER['HTTP_HOST'] 
            ?? $_SERVER['HOST'] 
            ?? $_SERVER['SERVER_NAME'] 
            ?? $_SERVER['SERVER_ADDR'] 
            ?? '';

        return URL_SCHEME . '://' . $hostname . '/' . $path;
    }

    /**
     * Get the request url segments as relative.
     * Removes the public controller path from uri if available.
     * 
     * @return string Return relative url segment paths.
     */
    public static function getUriSegments(): string
    {
        if(self::$segments === null){
            self::$segments = '/';

            if (isset($_SERVER['REQUEST_URI'])) {
                $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen(self::getBase()));

                // Remove url parameters if available.
                if ($uri !== '' && false !== ($pos = strpos($uri, '?'))) {
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
     * @return array<int,string> Return the array list of url segments.
     */
    public static function getSegments(): array
    {
        $segments = self::getUriSegments();

        if($segments === '/'){
            return [''];
        }

        $segments = explode('/', trim($segments, '/'));
   
        if (($public = array_search('public', $segments)) !== false) {
            array_splice($segments, $public, 1);
        }

        return $segments;
    }

    /**
     * Generate cache id for storing and serving static pages.
     * 
     * @return string Return cache id.
     */
    public static function getCacheId(): string 
    {
        $url = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $url .= $_SERVER['REQUEST_URI'] ?? 'index';
        $url = strtr($url, [
            '/' => '-', 
            '?' => '-', 
            '&' => '-', 
            '=' => '-', 
            '#' => '-'
        ]);

        // Remove static cache extension to avoid creating 2 versions of same cache
        // while serving static content (e.g, .html).
        if(($types = env('page.caching.statics', false)) !== false){
            $url = preg_replace('/\.(' . $types . ')$/i', '', $url);
        }
        
        return md5($url);
    }

    /**
     * Check if the request URL indicates an API prefix endpoint.
     * 
     * This method checks if the request URL starts with API prefix (e.g, `/api` or `public/api`).
     * 
     * @param bool $includeAjax Whether to also consider AJAX request as an API request.
     * 
     * @return bool Returns true if the request matches the API prefix or (if enabled) is an AJAX request, , false otherwise.
     */
    public static function isApiPrefix(bool $includeAjax = false): bool
    {
        static $prefix = null;
        $segments = self::getSegments();

        if ($prefix === null) {
            $prefix ??= defined('IS_UP') ? env('app.api.prefix', 'api') : 'api';
        }

        if ($segments !== [] && $segments[0] === $prefix) {
            return true;
        }

        return $includeAjax 
            && isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0;
    }

    /**
     * Find whether the application is running in CLI mode.
     *
     * @return bool Return true if the request is made in CLI mode, false otherwise.
     */
    public static function isCommand(): bool
    {
        static $cli = null;

        if ($cli !== null) {
            return $cli;
        }

        if (!empty($_SERVER['REMOTE_ADDR']) || !empty($_SERVER['HTTP_USER_AGENT'])) {
            return $cli = false;
        }

        return $cli = (PHP_SAPI === 'cli' || isset($_SERVER['argv']) || defined('STDIN') || !empty($_ENV['SHELL']));
    }

    /**
     * Filter the display path, to remove private directory paths before previewing to users.
     *
     * @param string $path The path to be filtered.
     * 
     * @return string Return the filtered path.
     */
    public static function filterPath(string $path): string 
    {
        $matching = '';
        foreach (self::$systemPaths as $directory) {
            $separator = $directory . DIRECTORY_SEPARATOR; 
            if (str_contains($path, $separator)) {
                $matching = $separator;
                break;
            }
        }

        return ($matching === '') 
            ? $path 
            : substr($path, strpos($path, $matching));
    }

    /**
     * Resolve the base class name(s) from a fully qualified class name or a list of class names.
     *
     * @param class-string<\T> $class Fully qualified class name, optionally prefixed with a backslash,
     *                            or a comma-separated list of class names.
     * 
     * @return string Return the base class name, or a comma-separated list of base names.
     */
    public static function getClassBaseNames(string $class): string
    {
        if (!$class) {
            return '';
        }

        if (str_contains($class, ',')) {
            $baseNames = array_map(function ($ns) {
                $ns = trim($ns, " \t\n\r\0\x0B\\");
                $pos = strrpos($ns, '\\');
                return ($pos !== false) ? substr($ns, $pos + 1) : $ns;
            }, explode(',', $class));
        
            return implode(', ', $baseNames);
        }

        $class = ltrim($class, '\\');

        if(($pos = strrpos($class, '\\')) !== false){
            return substr($class, $pos + 1);
        }

        return $class;
    }

    /**
     * Handle errors.
     * 
     * @param int $errno The error code.
     * @param string $errstr The error message.
     * @param string $errFile The error file.
     * @param int $errLine The error line number.
     * 
     * @return bool Return true if error was handled by framework, false otherwise.
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
     * Handle shutdown errors.
     * 
     * @return void
     */
    public static function shutdown(): void 
    {
        if (($error = error_get_last()) === null || !isset($error['type'])) {
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
     * Determines the appropriate error view path based on the application state and error context.
     *
     * @param ErrorHandler|null $stack The error handler stack used to manage error types and contexts.
     * 
     * @return array{0:bool,1:string,2:string} Returns an array with the view filename and its file path.
     */
    private static function errRoute(?ErrorHandler $stack): array
    {
        $view = null;
        if(defined('IS_UP') && PRODUCTION){
            $view = env('logger.mail.logs', null)
                ? 'mailer.php' 
                : (env('logger.remote.logs', null) ? 'remote.php' : null);
        }

        $tracer = ($view !== null);

        if($view === null){
            $view = match (true) {
                self::isCommand() => 'cli.php',
                self::isApiPrefix(true) => (defined('IS_UP') ? env('app.api.prefix', 'api') . '.php' : 'api.php'),
                default => ($stack instanceof ErrorHandler ? 'errors.php' : 'info.php')
            };
        }

        return [
            $tracer, $view, 
            sprintf(
                '%s%s%s%s%s%s%s',
                APP_ROOT, 'app',
                DIRECTORY_SEPARATOR, 'Errors',
                DIRECTORY_SEPARATOR, 'Defaults',
                DIRECTORY_SEPARATOR
            )
        ];
    }

    /**
     * Gracefully log error.
     * 
     * @param string $level The error log level.
     * @param string $message The error message to log.
     * 
     * @return void 
     */
    private static function log(string $level, string $message): void 
    {
        // If the error allowed framework to boot,
        // Then we use logger to log the error
        if(defined('IS_UP')){
            Logger::dispatch($level, $message);
            return;
        }

        // If not create a customer logger.
        $message = sprintf("[%s] [%s] {$message}",  $level, date('Y-m-d\TH:i:sP'));
        $log =  APP_ROOT. 'writeable' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR. "{$level}.log";

        if (@file_put_contents($log, $message . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            @chmod($log, 0666);
        }
    }

    /** 
     * Trim file directory both sides.
     *
     * @param string $path The path to trim.
     *
     * @return string Return trimmed path.
     * @internal
     */
    public static function bothTrim(string $path): string 
    {
        return trim($path, TRIM_DS) . DIRECTORY_SEPARATOR;
    }
}