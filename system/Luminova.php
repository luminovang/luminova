<?php
declare(strict_types=1);
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
use \App\Application;
use \ReflectionClass;
use Luminova\Http\Header;
use Luminova\Logger\Logger;
use Luminova\Routing\Router;
use \Psr\Log\LoggerInterface;
use Luminova\Http\HttpStatus;
use Luminova\Command\Terminal;
use Luminova\Sessions\Session;
use Luminova\Http\Client\Novio;
use Luminova\Logger\NovaLogger;
use function Luminova\Funcs\root;
use Luminova\Interface\ClientInterface;
use Luminova\Interface\MailerInterface;
use Luminova\Interface\RouterInterface;
use Luminova\Interface\SessionInterface;
use Luminova\Interface\ServiceKernelInterface;
use Luminova\Components\Email\Clients\NovaMailer;
use Luminova\Cache\{FileCache, RedisCache, MemoryCache};
use Luminova\Exceptions\{ErrorCode, FileException, ClassException, RuntimeException, InvalidArgumentException};

final class Luminova 
{
    /**
     * Framework version code. 
     * 
     * @var string VERSION
     */
    public const VERSION = '3.8.6';

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
     * Hold termination state.
     *
     * @var bool $isTerminated
     */
    private static bool $isTerminated = false;

    /**
     * PHP disabled functions.
     *
     * @var string[]|null $disabledFunctions
     */
    private static ?array $disabledFunctions = null;

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
	 * Generate a hash using the requested algorithm with optional fallback support.
	 *
	 * The method attempts to use the requested hashing algorithm. If the algorithm
	 * is unavailable in the current PHP environment, a fallback algorithm is used.
	 *
	 * This is useful when applications run across different environments where
	 * optional hash algorithms may not be compiled or enabled.
	 *
	 * @param string $algo Preferred hashing algorithm.
	 * @param string $data Data to hash.
	 * @param bool $binary Whether to return raw binary output instead of hex.
	 * @param array $options Reserved options for future hash algorithm settings.
	 * @param string|null $fallbackAlgo Algorithm to use when the preferred one is unavailable.
	 *
	 * @return string Return generated hash output.
	 * @throws InvalidArgumentException If neither the requested algorithm nor
	 *                                  the fallback algorithm is supported.
	 * 
	 * @see \hash()
	 * @link https://php.net/manual/en/function.hash.php
	 */
	public static function hash(
		string $algo,
		string $data,
		bool $binary = false,
		array $options = [],
		?string $fallbackAlgo = 'sha256'
	): string
	{
		static $algorithms = null;

		$algorithms ??= array_flip(hash_algos());

		$algo = strtolower($algo);

		if (!isset($algorithms[$algo])) {
			$algo = strtolower((string) $fallbackAlgo);

			if (!isset($algorithms[$algo])) {
				throw new InvalidArgumentException(
					sprintf(
						'Unsupported hash algorithm: "%s".',
						$algo
					)
				);
			}
		}

		return hash($algo, $data, $binary, $options);
	}
    
    /**
     * Resolve a service from the kernel or create a new instance.
     *
     * This method provides a unified way to access core services like HTTP client,
     * logger, mailer, session, router, and application instance. It first checks
     * if the service is registered in the kernel, then falls back to default
     * implementations if available.
     * 
     * **Service Name Aliases:**
     * - `http.client` - HTTP client service.
     * - `logger`    - Logger service.
     * - `mailer`    - Mailer service.
     * - `session`   - Session client service.
     * - `router`    - Routing system service.
     * - `app` or `application` - Application instance.
     * - `memcached` - Memcached servers.
     * - `redis` - Redis servers.
     * - `cache` - Cache system.
     *
     * @param string|null $service The service name or interface to resolve (e.g. 'http.client', 'logger').
     * @param bool $shared Whether to return a shared instance (default: true).
     * @param mixed ...$arguments Optional arguments passed to the service constructor.
     *
     * @return ServiceKernelInterface|mixed The resolved service instance 
     *      or application service kernel instance if service is null.
     *
     * @throws RuntimeException If an unregistered service is requested.
     * @throws ClassException If the resolved class for a service does not exist.
     * 
     * @see Kernel::create() To create or retrieve the kernel instance.
     * @see Kernel::shouldShareObject() To determine if services should be shared by default.
     * @see Kernel::has() To check if a service is registered in the kernel.
     * @see Kernel::get() To retrieve a service from the kernel.
     * 
     * @example - Example Usage:
     * ```php
     * $http = Luminova::kernel('http.client');
     * ```
     * 
     * ```php
     * $logger = Luminova::kernel('logger');
     * ```
     * 
     * @link https://luminova.ng/docs/0.0.0/foundation/kernel
     */
    public static function kernel(?string $service = null, bool $shared = true, mixed ...$arguments): mixed
    {
        static $services = [];

        if($shared && $service && isset($services[$service])){
            return $services[$service];
        }

        $resolve = Kernel::create(
            $shared && Kernel::shouldShareObject($service)
        );

        if($service === null){
            return $resolve;
        }

        if($resolve->has($service)){
            return $resolve->get($service, ...$arguments);
        }

        $class = match($service){
            'http.client', ClientInterface::class, \Psr\Http\Client\ClientInterface::class 
                => $resolve->getHttpClient(...$arguments) ?? Novio::class,
            'logger', LoggerInterface::class 
                => $resolve->getLogger(...$arguments) ?? NovaLogger::class,
            'mailer', MailerInterface::class 
                => $resolve->getMailer(...$arguments) ?? NovaMailer::class,
            'session', SessionInterface::class 
                => $resolve->getSessionClient(...$arguments) ?? Session::class,
            'router', 'routing', RouterInterface::class 
                => $resolve->getRoutingSystem(...$arguments) ?? Router::class,
            'app', 'application', Application::class 
                => $resolve->getApplication() ?? Application::getInstance(),
            'memcached', \Memcached::class,
                => $resolve->getMemcached(...$arguments) ?? \Memcached::class,
            'redis', \Redis::class,
                => $resolve->getRedis(...$arguments) ?? \Redis::class,
            'cache', \Luminova\Base\Cache::class => $resolve->getCacheProvider(...$arguments) 
                ?? match($arguments[0] ?? env('system.cache.driver', 'filecache')) { 
                    'filecache' => new FileCache(
                        $arguments[1] ?? null, $arguments[2] ?? null
                    ),
                    'memcached'  => new MemoryCache(
                        $arguments[1] ?? null, $arguments[2] ?? null
                    ),
                    'redis'  => new RedisCache(
                        $arguments[1] ?? null, $arguments[2] ?? null
                    ),
                    default => null
                },
            default => throw new RuntimeException(
                sprintf('Service "%s" is not registered in the kernel.', $service)
            )
        };

        if($class && is_object($class)){
            if(!$shared){
                return $class;
            }

            return $services[$service] = $class;
        }

        if($class === null || !class_exists($class)) {
            throw new ClassException(
                sprintf('Class "%s" for service "%s" does not exist.', (string) $class, $service)
            );
        }

        if(!$shared){
            return new $class(...$arguments);
        }

        return $services[$service] = new $class(...$arguments);
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
        if(self::$isTerminated){
            return;
        }

        self::$isTerminated = true;

        $title ??= HttpStatus::phrase($status, 'Terminated');
        $exitCode = STATUS_ERROR;
        
        if($message !== '' && !HttpStatus::isNoContent($status)){
            $exitCode = self::sendTermination(
                $status,
                $message,
                $title,
                $retry
            );
        } else{
            Header::sendNoContentHeaders($retry);
            Header::clearOutputBuffers('all');
        }
       
        try {
            ob_start();
            self::kernel('app', shared: true)->trigger('onTerminated', [
                'context'  => self::isCommand() ? 'CLI' : 'HTTP',
                'status'   => $status,
                'message'  => $message,
                'title'    => $title
            ]);
            ob_end_flush();
        } catch (Throwable){
        } finally{
            self::$isTerminated = true;
            NovaLogger::close();

            exit($exitCode);
        }
    }

    /**
     * Call a PHP function if it exists and is not disabled.
     *
     * Returns `false` when the function is unavailable due to being undefined
     * or disabled by PHP configuration.
     *
     * @param string $function Function name to call.
     * @param mixed ...$arguments Arguments passed to the function.
     *
     * @return mixed Function result, or false if the function is unavailable.
     *
     * @example - Example:
     * ```php
     * $result = Luminova::tryFunction('set_time_limit', 300);
     *
     * if ($result === false) {
     *     echo 'Function is unavailable.';
     * }
     * ```
     */
    public static function tryFunction(string $function, mixed ...$arguments): mixed
    {
        if (!function_exists($function) || self::isFunctionDisabled($function)) {
            return false;
        }

        return $function(...$arguments);
    }

    /**
     * Check whether a PHP function is disabled by the server configuration.
     *
     * Reads the configured disabled function list and checks if the specified
     * function is unavailable due to PHP's `disable_functions` setting.
     *
     * @param string $function The PHP function name to check.
     *
     * @return bool True if the function is disabled, otherwise false.
     */
    public static function isFunctionDisabled(string $function): bool
    {
        static $disabled = null;
        $disabled ??= self::getDisabledFunctions(true);

        return isset($disabled[strtolower($function)]);
    }

    /**
     * Returns the list of disabled PHP functions (via `disable_functions` directive).
     *
     * This method retrieves the list of functions that are disabled in the PHP configuration. 
     * It can return the list as a simple array of function names or as an associative array
     * with function names as keys and `true` as values.
     * 
     * @param bool $flip If true, returns an associative array with function names as keys and true as values.
     *
     * @return string[]|array<string,true>> Returns an array of disabled function names 
     *          or an associative array if `$flip` is true.
     */
    public static function getDisabledFunctions(bool $flip = false): array
    {
        if (self::$disabledFunctions !== null) {
            return ($flip && isset(self::$disabledFunctions[0])) 
                ? array_flip(self::$disabledFunctions) 
                : self::$disabledFunctions;
        }

        $list = ini_get('disable_functions') ?: '';

        if ($list === '') {
            return self::$disabledFunctions = [];
        }

        $disabled = [];

        foreach (explode(',', $list) as $function) {
            $function = trim($function);
            if($function === ''){
                continue;
            }

            if ($flip) {
                $disabled[$function] = true;
                continue;
            }

            $disabled[] = $function;
        }

        return self::$disabledFunctions = $disabled;
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

        $script = trim(APP_CONTROLLER_INDEX, TRIM_DS);

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
     * Generate a cache identifier for the current request.
     *
     * Creates a normalized cache key using the request method and URI. The URI may
     * optionally include query parameters and can be normalized by removing known
     * static file extensions to prevent duplicate cache entries for the same
     * resource.
     *
     * The generated identifier can be returned as either a raw key or an XXH3 hash.
     *
     * @param string|null $prefix Optional prefix to prepend to the generated key.
     * @param bool|null $withUriQuery Whether to include query parameters in the key.
     *                                When null, the value is determined by
     *                                `env('page.cache.query.params', false)`.
     * @param bool $hashValue Whether to return the generated key as an XXH3 hash.
     *
     * @return string The generated cache identifier or its XXH3 hash.
     *
     * @example - Example
     * ```php
     * $cacheId = Luminova::getCacheId();
     *
     * $cacheIdWithoutQuery = Luminova::getCacheId(
     *     withUriQuery: false
     * );
     *
     * $rawCacheId = Luminova::getCacheId(
     *     hashValue: false
     * );
     * 
     * $userCacheId = Luminova::getCacheId(
     *     prefix: 'user-1',
     *     hashValue: true
     * );
     * ```
     */
    public static function getCacheId(
        ?string $prefix = null,
        ?bool $withUriQuery = null,
        bool $hashValue = true
    ): string
    {
        static $types = null;

        $withUriQuery ??= (bool) env('page.cache.query.params', false);

        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $id = $method . (
            $withUriQuery
                ? $uri
                : (parse_url($uri, PHP_URL_PATH) ?: '')
        );

        $types ??= env('page.caching.statics', '');

        if ($types) {
            // Remove file extension for static cache formats
            // To avoid creating 2 versions of same cache
            // While serving static content (e.g, .html).
            $id = preg_replace(
                '/\.(' . preg_quote($types, '/') . ')(?=$|[?#])/i',
                '',
                $id
            );
        }

        $id = strtr($id, [
            '/' => ':',
            '?' => ':',
            '&' => ':',
            '=' => ':',
            '#' => ':',
            ' ' => ':'
        ]);

        $prefix = ($prefix !== null && $prefix !== '') 
            ? trim($prefix, " \t\n\r\0\x0B:") 
            : '';

        return $hashValue 
            ? self::hash('xxh3', "{$prefix}:{$id}", fallbackAlgo: 'md5') 
            : str_replace("\0", ':', "{$prefix}:{$id}");
    }

    /**
     * Get the API route prefix.
     *
     * Reads the `app.api.prefix` configuration once and caches the result
     * for subsequent calls to avoid repeated environment lookups.
     *
     * Falls back to `'api'` if the value is not defined or empty.
     *
     * @return string Return the default application API prefix used for routing.
     */
    public static function getApiPrefix(): string
    {
        static $api;

        if ($api === null) {
            $value = env('app.api.prefix', 'api');
            $api = ($value === '')
                ? 'api'
                : (string) $value;
        }

        return $api;
    }

    /**
     * Determine whether the application is running in HMVC mode.
     *
     * This method reads the `feature.app.hmvc` configuration once and caches
     * the result for subsequent calls to avoid repeated environment lookups.
     *
     * @return bool Return true if HMVC mode is enabled, otherwise false.
     */
    public static function isHmvc(): bool
    {
        static $enabled;

        if ($enabled === null) {
            $enabled = (bool) env('feature.app.hmvc', false);
        }

        return $enabled;
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
        return self::isUriPrefix(self::getApiPrefix());
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
     * Check if the specified file or directory has the required access permissions.
     * 
     * This method checks for read and/or write permissions on a given file or directory.
     * Throws a `FileException` if the required permissions are not met and `$silent` is false.
     * 
     * @param string $permission File access permission to check ('r' for read, 'w' for write, 'rw' for both).
     * @param string|null $file The file or directory path to check. 
     *              If null, defaults to the application's writeable directory.
     * @param bool $silent Whether to suppress exceptions and return false instead (default: true).
     * 
     * @return bool Returns true if the file has the required permissions, false otherwise.
     * @throws FileException If the file does not have the required permissions and $silent is false.
     */
    public static function permission(
        string $permission = 'rw', 
        ?string $file = null, 
        bool $silent = true)
    : bool
    {
        $file ??= root('writeable');
  
        [$error, $code] = match (true) {
            $permission === 'rw' && (!is_readable($file) || !is_writable($file)) => [
                "Read and write permission denied for: '%s'.",
                ErrorCode::READ_WRITE_PERMISSION_DENIED
            ],
            $permission === 'r' && !is_readable($file) => [
                "Read permission denied for: '%s'.",
                ErrorCode::READ_PERMISSION_DENIED
            ],
            $permission === 'w' && !is_writable($file) => [
                "Write permission denied for: '%s'.",
                ErrorCode::WRITE_PERMISSION_DENIED
            ],
            default => [null, null],
        };

        if ($error === null) {
            return true;
        }

        if($silent){
            return false;
        }

        $error = sprintf($error, $file);

        if (PRODUCTION && Logger::tryDispatch('critical', $error)) {
            return false;
        }

        throw new FileException($error, $code);
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
        if (!property_exists($objectOrClass, $property)) {
            return false;
        }

        if (!$staticOnly) {
            return true;
        }

        try {
            $ref = new ReflectionClass($objectOrClass);

            $prop = $ref->getProperty($property);

            return $prop->isStatic()
                && ($prop->isPublic() || $prop->isProtected());
        } catch (Throwable) {
            return false;
        }
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

    /**
     * Build termination response and output.
     *
     * @param int $status
     * @param string $message
     * @param string $title
     * @param int $retry
     * 
     * @return int
     */
    private static function sendTermination(
        int $status, 
        string $message, 
        string $title,
        int $retry,
    ): int 
    {
        $exitCode = ($status === STATUS_SUCCESS || HttpStatus::isAccepted($status)) 
            ? STATUS_SUCCESS : STATUS_ERROR;

         if(self::isCommand()){
            Terminal::writeln(
                sprintf(
                    "(%d) [%s] %s\nRetry After: %d", 
                    $status, 
                    $title, 
                    strip_tags(
                        str_replace(['<br/>', '<br>'], PHP_EOL, $message)
                    ), 
                    $retry
                ), 
                stream: ($exitCode === STATUS_SUCCESS) 
                    ? Terminal::STD_OUT 
                    : Terminal::STD_ERR
            );
            return $exitCode;
        }
        
        $output = '';
        $type = 'text/plain; charset=utf-8';
        $accept = $_SERVER['HTTP_LMV_SENT_CONTENT_TYPE'] 
            ?? '';

        if (
            ($message[0] === '{' || $message[0] === '[')
            || str_contains($accept, 'application/json') 
            || (!$accept && self::isApiRequest())
        ) {
            $type = 'application/json; charset=utf-8';
            $output =  json_validate($message)
                ? $message 
                : json_encode(
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

        return $exitCode;
    }
}