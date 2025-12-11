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
use \ReflectionClass;
use Luminova\Runtime;
use Luminova\Config\Env;
use Luminova\Http\Header;
use Luminova\Routing\Router;
use Luminova\Http\HttpStatus;
use Luminova\Command\Terminal;
use Luminova\Logger\NovaLogger;
use Luminova\Exceptions\{
    ClassException, 
    RuntimeException, 
    InvalidArgumentException
};

final class Luminova 
{
    /**
     * Framework version code. 
     * 
     * @var string VERSION
     */
    public const VERSION = '3.8.7';

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
     * System paths for filtering.
     * 
     * @var array<int,string> SYSTEM_PATHS
     */
    public const SYSTEM_PATHS = [
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
     * Application document root uri.
     * 
     * @var ?string $rootUri
     */
    private static ?string $rootUri = null;

    /**
     * Hold termination state.
     *
     * @var bool $isTerminated
     */
    private static bool $isTerminated = false;

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
     * Resolve an application kernel service or return the kernel instance.
     *
     * When `$service` is `null`, the kernel instance is returned. Otherwise,
     * the requested service is resolved through the application kernel.
     * 
     * @deprecated Use Kernel::resolve(), global helper kernel() or Application::make() instead
     *
     * @param string|null $service The service identifier,
     *        class/interface name, or `null` to return the kernel instance.
     * @param bool $shared Whether to reuse a shared instance when supported.
     * @param mixed ...$arguments Arguments passed to the service resolver.
     *
     * @return Kernel|mixed The resolved service or kernel result.
     *
     * @throws RuntimeException If the requested service cannot be resolved.
     * @throws ClassException If the service/abstract is not available.
     */
    public static function kernel(
        ?string $service = null,
        bool $shared = true,
        mixed ...$arguments
    ): mixed 
    {
        return \Luminova\Funcs\kernel(
            $service,
            $shared,
            ...$arguments
        );
    }

    /**
     * Build an absolute path from the application root directory.
     * 
     * Generates a normalized path based on `APP_ROOT`, with optional
     * subdirectory and filename appended. All input paths are sanitized
     * to ensure consistent separators.
     * 
     * When `$normalize` is enabled, the final path is converted to the
     * operating system directory separator for filesystem usage.
     *
     * @param string|null $path Optional subdirectory relative to the application root (e.g., 'writeable/logs').
     * @param string|null $filename Optional filename to append to the path (e.g., 'debug.log').
     * @param bool $normalize When true, the final path is normalized to OS-specific separators
     *        for filesystem operations (default: `false` uses `/`).
     *
     * @return string Returns a normalized absolute path based on `APP_ROOT`.
     * 
     * @see self::appRoot()
     *
     * @example - Usage:
     *
     * ```php
     * $file = Luminova::root('writeable/logs', 'debug.log');
     *
     * // Output:
     * /var/www/app/writeable/logs/debug.log
     * ```
     * 
     * @example - With OS Compatible:
     *
     * ```php
     * $file = Luminova::root('writeable/logs', 'debug.log', true);
     *
     * // Example outputs:
     * // Linux:  /var/www/app/writeable/logs/debug.log
     * // macOS:  /Applications/XAMPP/htdocs/app/writeable/logs/debug.log
     * // Windows: C:\wamp64\www\app\writeable\logs\debug.log
     * ```
     *
     * > **Note:**
     * >
     * > - Input separators (`\` and `/`) are automatically normalized.
     * > - The function does not validate whether the path exists.
     * > - Use `$normalize = true` only for direct filesystem access.
     */
    public static function root(?string $path = null, ?string $filename = null, bool $normalize = false): string
    {
        $ds = '/';
        $fullPath = self::appRoot();

        if($path !== null && $path !== '' && $path !== $ds){
            $fullPath .= \trim($path, '/\\') . '/';
        }

        if ($filename !== null && $filename !== '') {
            $fullPath .= \trim($filename, '/\\');
        }

        if (!$normalize) {
            return \str_replace(
                ['\\', '/'], 
                '/', 
                $fullPath
            );
        }

        return \str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $fullPath);
    }

    /**
     * Terminates the current request with a formatted response.
     *
     * The response format is determined by the request `Accept` header:
     * - `application/json` → JSON
     * - `application/xml` or `text/xml` → XML
     * - `text/html` → HTML
     * - otherwise → plain text
     *
     * The optional termination hook is triggered before the process exits.
     *
     * @param int $status HTTP status code.
     * @param string $message Termination message.
     * @param string|null $title Optional response title.
     * @param int $retry Cache retry duration in seconds.
     * @param null|'html'|'xml'|'json'|'plain' $httpOutput HTTP output format.
     * @param bool $hookOnTerminated Whether to trigger the `onTerminated` hook.
     *
     * @return never This method always terminates the current process.
     */
    public static function terminate(
        int $status,
        string $message,
        ?string $title = null,
        int $retry = 3600,
        ?string $httpOutput = null,
        bool $hookOnTerminated = true
    ): never 
    {
        if (self::$isTerminated) {
            exit(STATUS_ERROR);
        }

        self::$isTerminated = true;

        $title ??= HttpStatus::phrase($status, 'Terminated');
        $exitCode = STATUS_ERROR;

        if ($message !== '' && !HttpStatus::isNoContent($status)) {
            $exitCode = self::sendTermination(
                $status,
                $message,
                $title,
                $retry,
                $httpOutput
            );
        } else {
            Header::sendNoContentHeaders($retry);
            Header::clearOutputBuffers('all');
        }

        if ($hookOnTerminated) {
            try {
                ob_start();
                Kernel::resolve(Kernel::SERVICE_APPLICATION)
                    ->trigger('onTerminated', [
                        'context' => Runtime::isCommand() ? 'CLI' : 'HTTP',
                        'status'  => $status,
                        'message' => $message,
                        'title'   => $title,
                    ]);
                ob_end_flush();
            } catch (Throwable) {}
        }

        NovaLogger::close();
        exit($exitCode);
    }

    /**
     * Get the application document root URI.
     *
     * The URI is derived from `SCRIPT_NAME` by removing the entry script name
     * and normalizing path separators to forward slashes. The returned URI
     * always ends with `/`.
     *
     * @return string The application document root URI, such as `/` or `/admin/`.
     */
    public static function documentRootUri(): string
    {
        if (self::$rootUri !== null) {
            return self::$rootUri;
        }

        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');

        if ($script === '/') {
            return self::$rootUri = '/';
        }

        $lastSlash = strrpos($script, '/');

        return self::$rootUri = ($lastSlash === false)
            ? '/'
            : substr($script, 0, $lastSlash + 1);
    }

    /**
     * Get the application document root filesystem path.
     *
     * The document root is the public directory exposed by the web server.
     *
     * @return string The absolute filesystem path to the application document root.
     */
    public static function documentRoot(): string
    {
        return DOCUMENT_ROOT;
    }

    /**
     * Get the application project root filesystem path.
     *
     * The project root contains the application source code and configuration,
     * including the public document root.
     *
     * @return string The absolute filesystem path to the application project root.
     */
    public static function appRoot(): string
    {
        static $root;

        if ($root !== null) {
            return $root;
        }

        $ds = DIRECTORY_SEPARATOR;
        $dir = APP_BASE_PATH;

        while ($dir !== DIRECTORY_SEPARATOR) {
            if (
                is_file($dir . '/.env')
                || is_file($dir . '/.dev.env')
            ) {
                return $root = str_replace(['/', '\\'], $ds, $dir) . $ds;
            }

            $parent = dirname($dir);

            if ($parent === $dir) {
                break;
            }

            $dir = $parent;
        }

        return $root = str_replace(
            ['/', '\\'], 
            $ds, 
            APP_BASE_PATH
        ) . $ds;
    }

    /**
     * Get the application entry script path or URI.
     *
     * @param bool $uri Whether to return the web URI instead of the filesystem path.
     *
     * @return string The application entry script URI or absolute filesystem path.
     */
    public static function entryScript(bool $uri = true): string
    {
        if ($uri) {
            return self::documentRootUri() . 'index.php';
        }

        return self::documentRoot() . 'index.php';
    }

    /**
     * Convert an application path to a fully qualified URL.
     *
     * Normalizes the path by removing the application and public directory
     * prefixes as needed, then resolves it against the application base URL.
     *
     * @param string $path The application-relative file or route path.
     *
     * @return string The fully qualified URL.
     *
     * @example - Examples:
     * ```php
     * Luminova::toAbsoluteUrl('public/images/logo.png');
     *
     * // Development:
     * // http://localhost/my-project/public/images/logo.png
     *
     * // Production:
     * // https://example.com/images/logo.png
     *
     * Luminova::toAbsoluteUrl('about');
     *
     * // Development:
     * // http://localhost/my-project/public/about
     *
     * // Production:
     * // https://example.com/about
     * ```
     */
    public static function toAbsoluteUrl(string $path): string
    {
        if (!PRODUCTION && Runtime::isOutsideContainer()) {
            $base = self::documentRootUri();

            if (str_starts_with($path, $base)) {
                $path = substr($path, strlen($base));
            }
        } else {
            $path = self::toDisplayPath($path);
        }

        $path = trim($path, TRIM_DS);

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
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

        $uri = trim(self::documentRootUri(), '/');

        if ($relative) {
            return ($uri === '') 
                ? $route 
                : "/{$uri}{$route}";
        }

        $hostname = $_SERVER['HTTP_HOST'] 
            ?? $_SERVER['HOST'] 
            ?? $_SERVER['SERVER_NAME'] 
            ?? 'localhost';

        $base = URL_SCHEME . '://' . $hostname;

        if ($uri !== '') {
            $base .= '/' . $uri;
        }

        return $base . $route;
    }

    /**
     * Get the application API route prefix.
     *
     * Reads the `app.api.prefix` configuration once and caches the result
     * for subsequent calls.
     *
     * Falls back to `'api'` when the configured value is undefined or empty.
     *
     * @return string The application API route prefix.
     */
    public static function apiPrefix(): string
    {
        static $api;

        if ($api === null) {
            $value = Env::get('app.api.prefix', 'api');

            $api = ($value === '')
                ? 'api'
                : (string) $value;
        }

        return $api;
    }

    /**
     * Convert a file path to a display-friendly path.
     *
     * Removes the leading path up to the first known application directory,
     * such as `app` or `system`, to avoid exposing the full server filesystem
     * path in errors, logs, and debug output.
     *
     * If no known directory is found, the normalized original path is returned.
     *
     * @param string $path The file path to convert.
     *
     * @return string The display path starting at the matched application directory,
     *                or the normalized original path when no match is found.
     *
     * @example - Example:
     * ```php
     * Luminova::toDisplayPath('/var/www/project/app/Controllers/Home.php');
     * // app/Controllers/Home.php
     * ```
     */
    public static function toDisplayPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        foreach (self::SYSTEM_PATHS as $directory) {
            $needle = '/' . trim($directory, '/') . '/';

            if (($position = strpos($path, $needle)) !== false) {
                return substr($path, $position + 1);
            }
        }

        return $path;
    }

    /**
     * Check whether a class or object has a property, with optional static-only filtering.
     *
     * Uses `ReflectionClass` when `$staticOnly` is true to determine 
     * if a property is declared as `static`.
     * 
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
     * Get the base name from one or more fully qualified class names.
     *
     * Accepts a single class name, a comma-separated list of class names, or
     * an array of class names. The return format matches the input format.
     *
     * When validation is enabled, each class name must refer to an already
     * loaded class and must not contain a static class member reference such
     * as `ClassName::method()`.
     *
     * @param string[]|string $class One or more fully qualified class names.
     * @param bool $validate Whether to validate that each class is already loaded.
     *
     * @return string[]|string Base class name(s), preserving the input format.
     *
     * @example - Single class:
     * ```php
     * Luminova::getClassBasename('\App\Controllers\HomeController');
     * // Returns: 'HomeController'
     * ```
     *
     * @example - Comma-separated classes:
     * ```php
     * Luminova::getClassBasename('App\Models\User, App\Services\Log');
     * // Returns: 'User, Log'
     * ```
     *
     * @example - Array:
     * ```php
     * Luminova::getClassBasename([
     *     'App\Models\User',
     *     'App\Services\Log',
     * ]);
     * // Returns: ['User', 'Log']
     * ```
     *
     * @example - Validate classes:
     * ```php
     * Luminova::getClassBasename('App\Models\User', true);
     * // Returns: 'User' if the class is already loaded, otherwise ''.
     * ```
     */
    public static function getClassBasename(array|string $class, bool $validate = false): array|string
    {
        $isArray = is_array($class);
        $classes = $isArray ? $class : explode(',', $class);

        $baseNames = array_map(
            static function (string $class) use ($validate): string {
                $class = trim($class, " \t\n\r\0\x0B\\");

                if ($validate && (
                    str_contains($class, '::') ||
                    !class_exists($class)
                )) {
                    return '';
                }

                $position = strrpos($class, '\\');

                return ($position === false)
                    ? $class
                    : substr($class, $position + 1);
            },
            $classes
        );

        return $isArray ? $baseNames : implode(', ', $baseNames);
    }

    /**
     * Build termination response and output.
     *
     * @param int $status
     * @param string $message
     * @param string $title
     * @param int $retry
     * @param string|null $httpOutput
     * 
     * @return int
     */
    private static function sendTermination(
        int $status, 
        string $message, 
        string $title,
        int $retry,
        ?string $httpOutput = null
    ): int 
    {
        $exitCode = ($status === STATUS_SUCCESS || HttpStatus::isAccepted($status)) 
            ? STATUS_SUCCESS : STATUS_ERROR;

         if(Runtime::isCommand()){
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
            ?? $httpOutput
            ?? '';

        if (
            $accept === 'json'
            || ($message[0] === '{' || $message[0] === '[')
            || str_contains($accept, 'json') 
            || (!$accept && Router::isApiRequest())
        ) {
            $type = 'application/json; charset=utf-8';
            $output =  json_validate($message)
                ? $message 
                : json_encode(
                    ['status' => $status, 'error' => $title, 'message' => $message], 
                    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                 );
        } elseif ($accept === 'html' || ($accept && str_contains($accept, 'html'))) {
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $message = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
            $type = 'text/html; charset=utf-8';

            $output = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>{$title}</title></head><body>";
            $output .= "<h1>{$status} {$title}</h1><p>{$message}</p>";
            $output .= "</body></html>";
        } elseif ($accept === 'xml' || ($accept && str_contains($accept, 'xml'))) {
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