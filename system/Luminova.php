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
use \App\Kernel;
use \ReflectionClass;
use \Luminova\Http\Header;
use \Luminova\Logger\Logger;
use \Luminova\Http\HttpStatus;
use \Luminova\Command\Terminal;
use \Luminova\Logger\NovaLogger;
use function \Luminova\Funcs\root;
use \Luminova\Debugger\Performance;
use \Luminova\Interface\ServiceKernelInterface;
use \Luminova\Exceptions\{ErrorCode, FileException, InvalidArgumentException};

final class Luminova 
{
    /**
     * Framework version code. 
     * 
     * @var string VERSION
     */
    public const VERSION = '3.7.7';

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
    public const MIN_PHP_VERSION = '8.1';

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
     * Prevent initialization
     */
    private function __construct(){}

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
        if (!$userAgent) {
            return sprintf('PHP Luminova (%s)', self::VERSION);
        }

        return sprintf(
            'LuminovaFramework-%s/%s (PHP; %s; %s) - https://luminova.ng',
            self::VERSION_NAME, 
            self::VERSION,
            PHP_VERSION,
            PHP_OS_FAMILY
        );
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
        return $integer 
            ? (int) str_replace('.', '', self::VERSION)
            : self::VERSION;
    }

    /**
     * Start or stop recording application performance profiling.
     *
     * Profiling is only active when debugging is enabled and the application
     * is not running in production.
     *
     * @param string $action The profiling action (typically: `start` or `stop`).
     * @param array|null $command Optional CLI command context for profiling stop.
     * 
     * **Command structure:**
     *
     * ```
     * [
     *     'command'      => string,        // Original CLI input (without PHP binary)
     *     'name'       => string,        // Resolved command name.
     *     'group'      => string,        // Command group namespace.
     *     'arguments'  => string[],      // Positional arguments (e.g. ['limit=2'])
     *     'options'    => array<string,mixed>, // Named options (e.g. ['no-header' => null])
     *     'input'      => string,        // Full executable command string
     *     'params'     => string[],      // Parsed parameter values
     * ]
     * ```
     *
     * @return void
     * @throws InvalidArgumentException If an unsupported action is provided in non-production.
     * 
     * @see Performance::start() To start recording performance profiling
     * @see Performance::stop() To stop recording.
     */
    public static final function profiling(string $action, ?array $command = null): void
    {
        if (PRODUCTION || !STAGING && !env('debug.show.performance.profiling', false)) {
            return;
        }

        switch ($action) {
            case 'start':
                Performance::start();
                return;

            case 'stop':
                Performance::stop(command: $command);
                return;

            default:
                throw new InvalidArgumentException(
                    sprintf('Invalid profiling action "%s". Expected "start" or "stop".', $action)
                );
        }
    }

    /**
     * Get the application service kernel instance.
     *
     * Returns the kernel that manages core services and application modules
     * during the current request lifecycle.
     *
     * By default, a shared kernel instance is requested. Set `$shared` to
     * false to request a new instance. If shared instances are disabled by
     * `shouldShareObject`, a new kernel instance will also be created.
     *
     * @param bool $shared Whether to return a shared kernel instance (default: true).
     *
     * @return ServiceKernelInterface The application service kernel instance.
     *
     * @example Get the shared kernel instance.
     * ```php
     * $kernel = Luminova::kernel();
     *
     * $http = $kernel->getHttpClient();
     * ```
     *
     * @example Create a new kernel instance.
     * ```php
     * $kernel = Luminova::kernel(false);
     * ```
     *
     * @see https://luminova.ng/docs/0.0.0/foundation/kernel
     */
    public static function kernel(bool $shared = true): ServiceKernelInterface
    {
        return Kernel::create(
            $shared || Kernel::shouldShareObject()
        );
    }

    /**
     * Terminates the request by sending a status and formatted message.
     *
     * Responds according to the `Accept` header:
     * - `application/json` → JSON response
     * - `application/xml` / `text/xml` → XML response
     * - `text/html` → HTML page
     * - fallback → plain text
     *
     * @param int $status HTTP status code.
     * @param string $message Termination message.
     * @param string|null $title Optional error title.
     * @param int $retry Optional cache retry duration in seconds (default: 3600).
     *
     * @return void
     */
    public static function terminate(
        int $status, 
        string $message, 
        ?string $title = null,
        int $retry = 3600
    ): void
    {
        $title ??= HttpStatus::phrase($status, 'Termination Error');
        $exitCode = ($status === STATUS_SUCCESS || HttpStatus::isAccepted($status)) 
            ? STATUS_SUCCESS : STATUS_ERROR;

        // Close all open logging stream
        if(self::kernel()->getLogger() instanceof NovaLogger){
            NovaLogger::closeStreams();
        }

        if(self::isCommand()){
            Header::clearOutputBuffers('all');
            Terminal::terminate(sprintf(
                "(%d) [%s] %s\nRetry After: %d", 
                $status, $title, $message, $retry
            ), $exitCode);

            exit($exitCode);
        }

        $output = '';
        $accept = $_SERVER['HTTP_LMV_SENT_CONTENT_TYPE'] 
            ?? $_SERVER['HTTP_ACCEPT'] 
            ?? '*/*';
        $type = 'text/plain; charset=utf-8';

        if (
            $accept === '*/*' 
            || str_contains($accept, 'application/json') 
            || (!$accept && self::isApiRequest())
        ) {
            $type = 'application/json; charset=utf-8';
            $output = json_encode(
                ['status' => $status, 'error' => $title, 'message' => $message], 
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
        } elseif ($accept && str_contains($accept, 'text/html')) {
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $message = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
            $type = 'text/html; charset=utf-8';

            $output = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>{$title}</title></head><body>";
            $output .= "<h1>{$status} {$title}</h1><p>{$message}</p>";
            $output .= "</body></html>";
        } elseif ($accept && str_contains($accept, 'xml')) {
            $type = 'application/xml; charset=utf-8';
            $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $output .= "<response>\n";
            $output .= "  <status>{$status}</status>\n";
            if($title){
                $output .= "  <error>" . htmlspecialchars($title, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</error>\n";
            }
            $output .= "  <message>" . htmlspecialchars($message, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</message>\n";
            $output .= "</response>";
        } else {
            $output = sprintf('(%d) [%s] %s', $status, $title, $message);
        }

        Header::sendNoCacheHeaders($status, $type, $retry);
        Header::clearOutputBuffers('all');

        echo $output;
        exit($exitCode);
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

        $path = ($lastSlash > 0) 
            ? substr($script, 0, $lastSlash) . '/' 
            : '/';
        
        return self::$base = $path;
    }

    /**
     * Convert a relative application path to a fully qualified URL.
     *
     * Normalizes system paths (like `public/`), then builds a full URL
     * based on the environment (development or production).
     *
     * @param string $path Application-relative file or route path.
     *
     * @return string Returns the fully qualified absolute URL.
     * 
     * @example - Absolute URL Example:
     * ```php
     * // Development environment
     * echo Luminova::toAbsoluteUrl('public/images/logo.png');
     * // http://localhost/my-project-path/public/images/logo.png
     *
     * // Production environment
     * echo Luminova::toAbsoluteUrl('public/images/logo.png');
     * // https://example.com/images/logo.png
     * ```
     *
     * @example - Route Example:
     * ```php
     * echo Luminova::toAbsoluteUrl('about');
     * // Dev:  http://localhost/my-project-path/public/about
     * // Prod: https://example.com/about
     * ```
     */
    public static function toAbsoluteUrl(string $path): string
    {
        if (NOVAKIT_ENV === null && !PRODUCTION) {
            $base = rtrim(self::getBase(), 'public/');
            $pos = strpos($path, $base);

            if ($pos !== false) {
                $path = substr($path, $pos + strlen($base));
            }
        } else {
            $path = self::toDisplayPath($path);
        }

        $path = trim($path, TRIM_DS);

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        return self::toBaseUrl($path);
    }

    /**
     * Build a URL relative to the application base path.
     *
     * Generates an absolute or relative URL using the application
     * base path or front controller directory.
     *
     * Useful for generating links to routes, assets, and internal pages.
     *
     * - In development, the front controller path is included.
     * - In production, URLs are resolved from the application root.
     * - Host and port are preserved when available.
     *
     * @param string|null $route Optional route path to append.
     * @param bool $relative Whether to return a relative URL.
     *
     * @return string Returns the constructed application URL.
     *
     * @example - Example:
     * 
     * Assuming your application path is like: `/Some/Path/To/htdocs/my-project-path/public/`.
     * 
     * ```php
     * echo Luminova::toBaseUrl('about');
     * ```
     * 
     * It returns depending on your development environment:
     * 
     * **On Development:**
     * - http://localhost:8080/about
     * - http://localhost/my-project-path/public/about
     * - http://localhost/public/about
     * 
     * **In Production:**
     * - http://example.com:8080/about
     * - http://example.com/about
     * 
     * @example - Relative URL Example:
     * 
     * ```php
     * echo Luminova::toBaseUrl('about', true); 
     * // /my-project-path/public/about
     * // /about
     * ```
     */
    public static function toBaseUrl(?string $route = null, bool $relative = false): string
    {
        $route = '/' . ltrim((string) $route, '/');

        if(PRODUCTION){
            return $relative ? $route : APP_URL . $route;
        }

        $script = trim(CONTROLLER_SCRIPT_PATH, TRIM_DS);

        if ($relative) {
            return ($script === '') 
                ? $route 
                : "/{$script}{$route}";
        }

        $hostname = $_SERVER['HTTP_HOST'] 
            ?? $_SERVER['HOST'] 
            ?? $_SERVER['SERVER_NAME'] 
            ?? 'localhost';

        $base = URL_SCHEME . '://' . $hostname;

        if ($script !== '') {
            $base .= '/' . $script;
        }

        return $base . $route;
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
        if (self::$segments !== null) {
            return self::$segments;
        }

        self::$segments = '/';

        if (!empty($_SERVER['REQUEST_URI'])) {
            $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen(self::getBase()));

            if ($uri !== '' && ($pos = strpos($uri, '?')) !== false) {
                $uri = substr($uri, 0, $pos);
            }

            self::$segments = '/' . trim($uri, '/');
        }

        return self::$segments;
    }

    /**
     * Get the URI segments as an array.
     * 
     * Splits the request URI into individual segments. 
     * Automatically removes the "public" prefix if it appears at the start of the URI.
     *
     * Examples:
     * - `/public/foo/bar` → `['foo', 'bar']`
     * - `/public` → `['']`
     * - `/products/view/10` → `['products', 'view', '10']`
     * - `/` → `['']`
     *
     * @return array<int,string> Return an array of URI segments.
     */
    public static function getSegments(): array
    {
        $segments = self::getUriSegments();

        if ($segments === '/') {
            return [''];
        }

        $segments = trim($segments, '/');

        if($segments === 'public'){
            return [''];
        }

        if (str_starts_with($segments, 'public/')) {
            $segments = substr($segments, 7);
        }

        if ($segments === '') {
            return [''];
        }

        return explode('/', $segments);
    }

    /**
     * Generate a unique URL based cache key for the current request.
     *
     * This method creates a normalized identifier for caching based on:
     * - The HTTP request method (`GET`, `POST`, etc.).
     * - The request URI (path and optionally query parameters).
     * - Stripping file extensions for static cache formats if configured.
     * - Replacing special URL characters with dashes for safe storage.
     *
     * The resulting string is hashed with MD5 to produce a fixed-length cache ID.
     *
     * @param string|null $salt Optional cache salt to include in key hashing (default: null).
     * @param bool|null $uriQuery Whether to include query parameters in the cache ID (default: false).
     *                  If set to null, it uses default from `env(page.cache.query.params)`
     *                  If explicitly set, it overrides the default env.
     *
     * @return string Return a unique MD5 hash representing the cache ID for this request.
     *
     * @example - Example:
     * ```php
     * $cacheId = Luminova::getCacheId(); // e.g., "d41d8cd98f00b204e9800998ecf8427e"
     * 
     * $cacheIdWithoutQuery = Luminova::getCacheId(uriQuery: false); // ignores query string
     * ```
     */
    public static function getCacheId(?string $salt = null, ?bool $uriQuery = null): string 
    {
        $salt ??= '';
        $uriQuery ??= (bool) env('page.cache.query.params', false);
        $uri = ($_SERVER['REQUEST_URI'] ?? 'index');
        $id = ($_SERVER['REQUEST_METHOD'] ?? 'CLI');
        $id .= ($uriQuery ? $uri : (parse_url($uri, PHP_URL_PATH) ?: ''));

        $id = strtr($id, [
            '/' => '-', 
            '?' => '-', 
            '&' => '-', 
            '=' => '-', 
            '#' => '-'
        ]);

        // Remove file extension for static cache formats
        // To avoid creating 2 versions of same cache
        // While serving static content (e.g, .html).
        if (($types = env('page.caching.statics', null)) !== null) {
            $id = preg_replace('/\.(' . $types . ')$/i', '', $id);
        }

        return md5($salt . $id);
    }

    /**
     * Determine whether the current request matches application API URI prefix.
     *
     * A request is considered an API-prefixed request when the first URI segment
     * matches the configured API prefix (for example: `/api` or a custom prefix
     * set in `app.api.prefix`).
     *
     * @return bool Return true if the first URI segment matches the API prefix.
     * 
     * @see self::isApiRequest()
     * @see self::isUriPrefix()
     */
    public static function isApiPrefix(): bool
    {
        $prefix = defined('APP_BOOTED') 
            ? env('app.api.prefix', 'api') 
            : 'api';

        return self::isUriPrefix($prefix);
    }

    /**
     * Determine whether the current request should be treated as an API request.
     *
     * A request is considered an API request when:
     * - the first URI segment matches the configured API prefix, or
     * - AJAX requests are allowed to be treated as API requests.
     *
     * When `$ajaxAsApi` is null, the value is resolved from
     * `app.validate.ajax.asapi` if the application has booted.
     *
     * @param bool|null $ajaxAsApi Whether to treat AJAX requests as API requests.
     *                             If null, uses `env(app.validate.ajax.asapi)`.
     *
     * @return bool Return true if the request matches the API prefix or qualifies as an AJAX request.
     * 
     * @see self::isApiPrefix()
     * @see self::isUriPrefix()
     */
    public static function isApiRequest(?bool $ajaxAsApi = null): bool
    {
        if (self::isApiPrefix()) {
            return true;
        }

        if($ajaxAsApi === null ){
            if(!defined('APP_BOOTED')){
                return false;
            }

            $ajaxAsApi = env('app.validate.ajax.asapi', false);
        }

        return $ajaxAsApi
            && isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0;
    }

    /**
     * Determine whether the first URI segment matches any given prefix.
     *
     * This checks only the first segment of the current request URI, making it
     * useful for route grouping such as `/admin`, `/api`, or `/webhook`.
     *
     * @param array|string $prefix One or more URI prefixes to match.
     *
     * @return bool Return true if the first URI segment matches any given prefix.
     * 
     * @see self::isApiPrefix()
     * @see self::isApiRequest()
     *
     * @example - Match a single prefix.
     * ```php
     * if (Luminova::isUriPrefix('admin')) {
     *     // Matches: /admin or /admin/users
     * }
     * ```
     *
     * @example - Match multiple prefixes.
     * ```php
     * if (Luminova::isUriPrefix(['api', 'webhook'])) {
     *     // Matches: /api/* or /webhook/*
     * }
     * ```
     */
    public static function isUriPrefix(array|string $prefix): bool
    {
        $segment = trim(self::getSegments()[0] ?? '', '/');

        foreach ((array) $prefix as $uri) {
            if ($segment === trim($uri, '/')) {
                return true;
            }
        }

        return false;
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
            || isset($_SERVER['argv'])
            || defined('STDIN')
            || !empty(getenv('SHELL'));
    }

    /**
     * Check if the given input can be called as a function or method.
     * 
     * This method detects standard callables, closures, function names, 
     * and array-style class/method pairs. If `$strict` is true, 
     * it will also verify that the class in an array callable exists.
     *
     * @param mixed $input The value to check (string, array, closure, object, etc.).
     * @param bool $strict If true, array callables are valid only if the class exists.
     *
     * @return bool Return true if the input is callable, false otherwise.
     */
    public static function isCallable(mixed $input, bool $strict = false): bool
    {
        if (is_callable($input)) {
            return true;
        }

        if (is_array($input) && count($input) === 2) {
            [$class, $method] = $input;
            return $strict ? class_exists($class) && method_exists($class, $method) : true;
        }

        return false;
    }

    /**
     * Mask a file path by trimming everything before the first known system directory.
     *
     * This method removes leading path segments before the first matched system
     * directory (for example, `app` or `system`). It helps hide sensitive server
     * paths in error messages, logs, and debug output.
     *
     * If no known system directory is found, the normalized original path is returned.
     *
     * @param string $path The full file path to mask.
     *
     * @return string Return the masked path starting from the matched system directory,
     *                or the normalized original path if no match is found.
     *
     * @example - Mask an absolute file path.
     * ```php
     * Luminova::toDisplayPath('/var/www/project/app/Controllers/Home.php');
     * // Returns: app/Controllers/Home.php
     * ```
     */
    public static function toDisplayPath(string $path): string
    {
        // Normalize path for cross-platform support
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
     * Check if file has read or write permission is granted.
     * 
     * @param string $permission File access permission.
     * @param string|null $file File name or file path to check permissions (default: writeable dir).
     * @param bool $throw Indicate whether to throws an exception if permission is not granted.
     * 
     * @return bool Returns true if permission is granted otherwise false.
     * @throws FileException If permission is not granted and quiet is not passed true.
     */
    public static function permission(string $permission = 'rw', ?string $file = null, bool $throw = false): bool
    {
        $file ??= root('writeable');
        
        if ($permission === 'rw' && (!is_readable($file) || !is_writable($file))) {
            $error = "Read and Write permission denied for '%s, please grant 'read' and 'write' permission.";
            $code = ErrorCode::READ_WRITE_PERMISSION_DENIED;
        } elseif ($permission === 'r' && !is_readable($file)) {
            $error = "Read permission denied for '%s', please grant 'read' permission.";
            $code = ErrorCode::READ_PERMISSION_DENIED;
        } elseif ($permission === 'w' && !is_writable($file)) {
            $error = "Write permission denied for '%s', please grant 'write' permission.";
            $code = ErrorCode::WRITE_PERMISSION_DENIED;
        } else {
            return true;
        }

        if(!$throw){
            return false;
        }

        if (PRODUCTION) {
            Logger::dispatch('critical', sprintf($error, $file));
            return false;
        }

        throw new FileException(sprintf($error, $file), $code);
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
     * @return bool Returns true if the property exists (and is static if required), false otherwise.
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
        if (property_exists($objectOrClass, $property)) {
            return true;
        }

        if($staticOnly){
            try {
                $ref = new ReflectionClass($objectOrClass);

                if(!$ref->hasProperty($property)){
                    return false;
                }

                $prop = $ref->getProperty($property);

                return $prop->isStatic() && ($prop->isPublic() || $prop->isProtected());
            } catch (Throwable) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get the base class name(s) from fully qualified class name(s).
     *
     * Accepts:
     * - A single fully qualified class name (FQCN), e.g. `App\Controllers\HomeController`
     * - A comma-separated string of FQCNs, e.g. `App\Models\User, App\Services\Log`
     * - An array of FQCNs
     *
     * Returns the base class name(s) while preserving the input format:
     * - Single string input → returns a single string
     * - Comma-separated string → returns a comma-separated string
     * - Array → returns an array of base names
     *
     * @param string[]|string $class One or more fully qualified class names.
     *
     * @return string[]|string Base class name(s), format matches the input.
     *
     * @example - Examples:
     * ```php
     * Luminova::getClassBaseName('\App\Controllers\HomeController'); 
     * // Returns: 'HomeController'
     *
     * Luminova::getClassBaseName('App\Models\User, App\Services\Log'); 
     * // Returns: 'User, Log'
     *
     * Luminova::getClassBaseName(['App\Models\User', 'App\Services\Log']); 
     * // Returns: ['User', 'Log']
     * ```
     */
    public static function getClassBaseName(array|string $class): array|string
    {
        if (!$class) {
            return is_array($class) ? [] : '';
        }

        $isArray = is_array($class);
        $classes = $isArray ? $class : explode(',', $class);

        $bases = array_map(function (string $ns): string {
            return basename(str_replace('\\', '/', trim($ns, " \t\n\r\0\x0B\\")));
        }, $classes);

        return $isArray ? $bases : implode(', ', $bases);
    }
}