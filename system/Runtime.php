<?php
declare(strict_types=1);
/**
 * Luminova Framework runtime.
 * 
 * @package Luminova
 * @author Ujah Chigozie Peter 
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova;

use Closure;
use DateTimeZone;
use \Luminova\Luminova;
use Luminova\Config\Env;
use Luminova\Logger\Entry;
use Luminova\Logger\Logger;
use Luminova\Debugger\Performance;
use Luminova\Exceptions\ErrorCode;
use Luminova\Exceptions\FileException;
use Luminova\Exceptions\InvalidArgumentException;

/**
 * Runtime and shared memory.
 *
 * Lightweight in-memory storage for application key/value data.
 * Works as a static runtime registry. Comparable in concept to Swift's
 * `UserDefaults` or Android/Java `SharedPreferences`, but not persistent.
 *
 * @see \Luminova\Funcs\shared() Global helper for quick access.
 *
 * @example - Usages:
 * ```php
 * use Luminova\Runtime;
 *
 * Runtime::set('THEME', true);
 *
 * $context = Runtime::get('THEME', false);
 *
 * if (Runtime::has('THEME')) {
 *     // Key exists...
 * }
 *
 * Runtime::remove('THEME');
 * Runtime::clear();
 * ```
 *
 * > **Note**
 * > This storage is runtime-only:
 * > - Data is lost at the end of each request.
 * > - Values are not shared across processes or workers.
 * > - Nothing is written to disk.
 */
final class Runtime
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
    private const ALL_KEYS = [
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
     * Last error details.
     *
     * @var array{
     *      code?:string|int,
     *      info?:array{type:int,message:string,file:string,line:int},
     *      backtrace?:array
     * } $lastError
     */
    private static array $lastError = [];

    /**
     * Supported runtime containers
     * 
     * @var array<string,string> RUNTIME_ENV
     */
    private const RUNTIME_ENV = [
        'docker'     => 'docker',
        'kubernetes' => 'kubepods',
        'containerd' => 'containerd',
        'podman'     => 'libpod',
        'novakit'    => 'novakit',
    ];

    /**
     * Runtimes
     *
     * @var array<string,bool> $runtimes
     */
    private static array $runtimes = [];

    /**
     * PHP disabled functions.
     *
     * @var string[]|null $disabledFunctions
     */
    private static ?array $disabledFunctions = null;

    /**
     * Profiling start mode.
     *
     * @var bool isProfiling
     */
    private static bool $isProfiling = false;

    /**
     * Prevent initialization.
     */
    private function __construct() {}

    /**
     * Determine whether the current runtime environment matches the given name.
     *
     * The comparison is case-insensitive and ignores surrounding whitespace.
     *
     * @param string $name The runtime environment name to check.
     *
     * @return bool `true` if the current runtime matches the given name,
     *     otherwise `false`.
     *
     * @see self::name()
     * @see self::isDocker()
     * @see self::isContainer()
     * @see self::isOutsideContainer()
     */
    public static function isEnv(string $name): bool
    {
        $name = strtolower(trim($name));

        return $name !== ''
            && self::name() === $name;
    }

    /**
     * Determine whether the current request is local.
     *
     * A request is considered local when its host or address identifies the local
     * machine or a private network commonly used for local development.
     *
     * @return bool `true` if the request is local, otherwise `false`.
     *
     * @see self::isLocalhost()
     * @see self::isLocalAddress()
     * @see \IS_LOCAL
     *
     * @note This is a network-based heuristic and does not determine whether the
     *       application is running in a development or production environment.
     */
    public static function isLocal(): bool
    {
        return self::isLocalhost()
            || self::isLocalAddress();
    }

    /**
     * Determine whether the current request uses a local or private address.
     *
     * Checks the request host and remote address for private IPv4 addresses
     * commonly used by local networks and development environments.
     *
     * Recognized private IPv4 ranges include:
     * - `10.0.0.0/8`
     * - `172.16.0.0/12`
     * - `192.168.0.0/16`
     *
     * The loopback range `127.0.0.0/8` is handled by {@see self::isLocalhost()}.
     *
     * @return bool `true` if the request uses a private IPv4 address, otherwise `false`.
     *
     * @see self::isLocalhost()
     */
    public static function isLocalAddress(): bool
    {
        static $isLocalAddress = null;

        if ($isLocalAddress !== null) {
            return $isLocalAddress;
        }

        $host = strtolower(
            $_SERVER['HTTP_HOST']
            ?? $_SERVER['SERVER_NAME']
            ?? $_SERVER['REMOTE_ADDR']
            ?? ''
        );

        if ($host === '') {
            return $isLocalAddress = false;
        }

        if (substr_count($host, ':') === 1) {
            $host = strstr($host, ':', true);
        }

        if(!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
            return $isLocalAddress = false;
        }

        return $isLocalAddress = (
            str_starts_with($host, '10.')
            || str_starts_with($host, '192.168.')
            || preg_match('/^172\.(1[6-9]|2\d|3[0-1])\./', $host) === 1
        );
    }

    /**
     * Determine whether the current request is addressed to localhost.
     *
     * Checks the request host, falling back to the remote address when the host
     * is unavailable. Localhost includes the `localhost` hostname, its subdomains,
     * and IPv4/IPv6 loopback addresses.
     *
     * Recognized localhost values include:
     * - `localhost`
     * - `*.localhost`
     * - `127.0.0.0/8`
     * - `::1`
     *
     * @return bool `true` if the request is addressed to localhost, otherwise `false`.
     * 
     * @see self::isLocalAddress()
     * @see \IS_LOCALHOST
     */
    public static function isLocalhost(): bool
    {
        static $isLocalhost = null;

        if ($isLocalhost !== null) {
            return $isLocalhost;
        }

         if(PHP_SAPI === 'cli'){
            $host = strtolower(gethostname() ?: '');

            return $isLocalhost = match (true) {
                $host === 'localhost',
                $host === '127.0.0.1',
                $host === '::1',
                str_ends_with($host, '.local'),
                str_starts_with($host, 'dev.'),
                str_starts_with($host, 'test.')
                    => true,
                default => false,
            };
        }

        $host = strtolower(
            $_SERVER['HTTP_HOST']
            ?? $_SERVER['SERVER_NAME']
            ?? $_SERVER['REMOTE_ADDR']
            ?? ''
        );

        if ($host === '') {
            return $isLocalhost = false;
        }

        if (str_starts_with($host, '[')) {
            $host = strstr($host, ']', true);
            $host = ltrim($host, '[');
        } elseif (substr_count($host, ':') === 1) {
            $host = strstr($host, ':', true);
        }

        return $isLocalhost = (
            $host === 'localhost'
            || $host === '127.0.0.1'
            || $host === '::1'
            || str_ends_with($host, '.localhost')
            || (
                filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
                && str_starts_with($host, '127.')
            )
        );
    }

    /**
     * Determine whether the application is running in a Docker environment.
     *
     * This is a convenience wrapper around {@see self::isContainer()} that
     * checks specifically for the Docker runtime.
     *
     * @return bool `true` if Docker is detected, otherwise `false`.
     */
    public static function isDocker(): bool
    {
        return self::isContainer('docker');
    }

    /**
     * Determine whether the application is running in a supported runtime
     * environment.
     *
     * When no runtime is specified, checks whether any supported runtime is
     * detected. When a runtime is specified, checks only that runtime.
     *
     * The `php` alias is treated as `novakit`.
     *
     * Supported runtimes include `docker`, `kubernetes`, `containerd`,
     * `podman`, and `novakit`.
     *
     * @param string|null $runtime Runtime name to check, or `null` to check
     *                             for any supported runtime.
     *
     * @return bool `true` if the requested runtime is detected, otherwise `false`.
     */
    public static function isContainer(?string $runtime = null): bool
    {
        $runtime = ($runtime !== null) ? strtolower(trim($runtime)) : null;

        if ($runtime === 'php') {
            $runtime = 'novakit';
        }

        $name = $runtime ?? 'all';

        if (array_key_exists($name, self::$runtimes)) {
            return self::$runtimes[$name];
        }

        return self::$runtimes[$name] = self::whichRuntime($runtime);
    }

    /**
     * Determine whether the application is running outside a detected
     * runtime environment.
     * 
     * Checks if any supported container or runtime environment
     * is detected.
     *
     * Returns `true` when no supported runtime environment is detected.
     *
     * @return bool `true` if no supported runtime is detected, otherwise `false`.
     */
    public static function isOutsideContainer(): bool
    {
        $env = self::name();

        if($env !== null && $env !== ''){
            return false;
        }

        return !self::isContainer();
    }

    /**
     * Get the name of the current runtime environment.
     *
     * The runtime name is resolved from `$_SERVER`, `$_ENV`, or the process
     * environment, in that order.
     *
     * @return string|null The runtime name, or `null` if none is defined.
     * 
     * @see self::isEnv()
     * @see self::isDocker()
     * @see self::isContainer()
     * @see self::isOutsideContainer()
     */
    public static function name(): ?string
    {
        $exists = false;
        $runtime = Env::value('RUNTIME_ENV', $exists);

        if(!$exists){
            return null;
        }

        if (!is_string($runtime) || $runtime === '') {
            return null;
        }

        return strtolower(trim($runtime));
    }

    /**
     * Determine whether the application is running in CLI (Command-Line
     * Interface) mode.
     *
     * Uses the PHP SAPI and common CLI indicators to distinguish command-line
     * execution from web requests.
     *
     * @return bool `true` if running in CLI mode, otherwise `false`.
     */
    public static function isCommand(): bool
    {
        static $isCli;

        if ($isCli !== null) {
            return $isCli;
        }

        if (
            isset($_SERVER['REMOTE_ADDR'])
            || isset($_SERVER['HTTP_USER_AGENT'])
        ) {
            return $isCli = false;
        }

        return $isCli = (
            PHP_SAPI === 'cli'
            || isset($_SERVER['argv'])
            || defined('STDIN')
        );
    }

    /**
     * Determine whether HMVC mode is enabled.
     *
     * The `feature.app.hmvc` configuration value is read once and cached
     * for subsequent calls.
     *
     * @return bool `true` if HMVC mode is enabled, otherwise `false`.
     */
    public static function isHmvc(): bool
    {
        static $isHmvc;

        return $isHmvc ??= (bool) Env::get('feature.app.hmvc', false);
    }

    /**
     * Determine whether the application is running on a specific platform.
     *
     * Supports common operating system families, BSD variants, Solaris, and
     * common AWS and Azure runtime environments. Custom platform names are
     * matched against the operating system name returned by {@see php_uname()}.
     *
     * **Supported Platform Values:**
     *
     * - `mac` — macOS.
     * - `windows` — Windows.
     * - `linux` — Linux.
     * - `freebsd` — FreeBSD.
     * - `openbsd` — OpenBSD.
     * - `bsd` — Any BSD-based operating system.
     * - `solaris` — Solaris.
     * - `aws` — AWS runtime environment.
     * - `azure` — Azure runtime environment.
     *
     * Custom platform names are also supported and are matched case-insensitively
     * against the current operating system name.
     *
     * @param string $name Platform name to check, such as `windows`, `linux`,
     *        `mac`, `aws`, or `azure`.
     *
     * @return bool `true` if the current platform matches the specified name,
     *              otherwise `false`.
     *
     * @example - Examples:
     * ```php
     * Runtime::isPlatform('windows');
     * Runtime::isPlatform('linux');
     * Runtime::isPlatform('mac');
     * Runtime::isPlatform('aws');
     * Runtime::isPlatform('azure');
     * ```
     */
    public static function isPlatform(string $name): bool
    {
        $name = strtolower(trim($name));

        return match ($name) {
            'mac' => PHP_OS_FAMILY === 'Darwin',

            'windows' => PHP_OS_FAMILY === 'Windows'
                || DIRECTORY_SEPARATOR === '\\',

            'linux' => PHP_OS_FAMILY === 'Linux',

            'freebsd' => PHP_OS === 'FreeBSD',

            'openbsd' => PHP_OS === 'OpenBSD',

            'bsd' => PHP_OS_FAMILY === 'BSD',

            'solaris' => PHP_OS_FAMILY === 'Solaris',

            'aws' => getenv('AWS_EXECUTION_ENV') !== false
                || getenv('AWS_REGION') !== false,

            'azure' => getenv('WEBSITE_INSTANCE_ID') !== false
                || getenv('AZURE_FUNCTIONS_ENVIRONMENT') !== false,

            default => str_contains(
                strtolower(php_uname('s')),
                $name
            ),
        };
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

        $disabled ??= self::disabledFunctions(true);

        return isset($disabled[strtolower($function)]);
    }

    /**
     * Returns the list of disabled PHP functions (via `disable_functions` directive).
     *
     * This method retrieves the list of functions that are disabled in the PHP configuration. 
     * It can return the list as a simple array of function names or as an associative array
     * with function names as keys and `true` as values.
     * 
     * @param bool $flip Whether to return an associative lookup array.
     * 
     * @return ($flip is true ? array<string, true> : array<int, string>) Returns an array of disabled function names or an associative array if `$flip` is true.
     */
    public static function disabledFunctions(bool $flip = false): array
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
     * Call a PHP function if it exists and is not disabled.
     *
     * Returns `false` when the function is unavailable due to being undefined
     * or disabled by PHP configuration.
     *
     * @param string $function Function name to call.
     * @param mixed ...$arguments Arguments passed to the function.
     *
     * @return mixed Function result, or null if the function is unavailable.
     *
     * @example - Example:
     * ```php
     * $result = Runtime::tryFunction('set_time_limit', 300);
     *
     * if ($result === null) {
     *     echo 'Function is unavailable.';
     * }
     * ```
     */
    public static function tryFunction(string $function, mixed ...$arguments): mixed
    {
        if (
            !function_exists($function) 
            || self::isFunctionDisabled($function)
        ) {
            return null;
        }

        return $function(...$arguments);
    }

    /**
     * Check whether the given value is callable or represents a callable reference.
     *
     * In addition to standard PHP callables, two-element arrays are accepted as
     * callable references even when the target cannot currently be called.
     *
     * When `$strict` is enabled, the referenced class and method must exist.
     *
     * @param mixed $input The value to check.
     * @param bool $strict Whether to require the referenced class and method to exist.
     *
     * @return bool `true` if the value is callable or a valid callable reference,
     *              otherwise `false`.
     */
    public static function isCallable(mixed $input, bool $strict = false): bool
    {
        if(!$input){
            return false;
        }

        if (($input instanceof Closure) || is_callable($input)) {
            return true;
        }

        if (!is_array($input) || count($input) !== 2) {
            return false;
        }

        [$class, $method] = $input;

        if(!is_string($class) || !is_string($method)){
            return false;
        }

        if (!$strict) {
            return true;
        }

        return class_exists($class)
            && method_exists($class, $method);
    }

    /**
     * Get the last captured PHP error.
     *
     * Returns the internally captured error when available. If no internal
     * error has been captured, falls back to PHP's last recorded error.
     * Returns an empty array when no error is available.
     *
     * @return array{type:int,message:string,file:string,line:int} Error details.
     */
    public static function lastError(): array
    {
        return self::$lastError['info']
            ?? error_get_last()
            ?? [];
    }

     /**
     * Retrieves the last stored error code or a default value.
     * 
     * @param string|int $default Default error code if none was stored (default: `E_ERROR`).
     * 
     * @return string|int Return the last stored error code, or the provided default.
     */
    public static function lastErrorCode(string|int $default = E_ERROR): string|int
    {
        return self::$lastError['code'] 
            ?? self::$lastError['type']
            ?? $default;
    }

    /**
     * Retrieves the last debug backtrace from the shared error context.
     * 
     * This method accesses a shared memory `$trace` to retrieve
     * the stored debug backtrace. If the backtrace is not set, it returns an empty array.
     * 
     * @return array Return the debug backtrace or an empty array if not available.
     */
    public static function lastErrorBacktrace(): array 
    {
        return self::$lastError['backtrace'] ?? [];
    }

    /**
     * Store the last captured PHP error.
     *
     * @param array{type:int,message:string,file:string,line:int} $error Error details.
     */
    public static function setLastError(array $error): void
    {
        self::$lastError['info'] = $error;
    }

    /**
     * Stores the last error code in the shared error context.
     * 
     * Accessible via `Runtime::getLastErrorCode()`.
     * 
     * @param string|int $code The last error code value.
     * 
     * @return void
     */
    public static function setLastErrorCode(string|int $code): void 
    {
        self::$lastError['code'] = $code;
    }

    /**
     * Clear the last captured PHP error.
     *
     * Clears both the internally stored error and PHP's last recorded error.
     */
    public static function clearLastError(): void
    {
        self::$lastError = [];
        error_clear_last();
    }

     /**
     * Stores the last debug backtrace in the shared error context.
     * 
     * Accessible via `Runtime::lastErrorBacktrace()`.  
     * Can either replace the current backtrace or prepend to it.
     * 
     * @param array $backtrace Array of backtrace information.
     * @param bool $push If true (default), prepends to the existing backtrace.
     * 
     * @return void
     */
    public static function setLastBacktrace(array $backtrace, bool $push = true): void 
    {
        if($push && isset(self::$lastError['backtrace'])){
            self::$lastError['backtrace'] = array_merge(
                $backtrace, 
                self::$lastError['backtrace']
            );

            return;
        }

        self::$lastError['backtrace'] = $backtrace;
    }

    /**
     * Set the internal multibyte encoding.
     * 
     * @param string $encoding The default internal encoding.
     *
     * @return bool Returns false if encoding is null/empty or the function is unavailable.
     */
    public static function setEncoding(string $encoding): bool
    {
        if (
            $encoding 
            && self::tryFunction('mb_internal_encoding', $encoding)
        ){
            Env::set('app.mb.encoding', $encoding);
            return true;
        }

        return false;
    }

    /**
     * Check whether a file or directory has the required access permissions.
     *
     * Checks each requested permission and returns `true` only when all
     * permissions are available. When a permission check fails, this method
     * returns `false` by default. If `$silent` is `false`, it logs the failure
     * in production and throws a `FileException`.
     *
     * Permissions may be specified using shorthand (`r`, `w`, `x`) or readable
     * names (`read`, `write`, `execute`). Multiple permissions can be combined
     * using underscores, plus signs, or commas, and their order is not significant.
     *
     * @param string|'read'|'write'|'execute'|'r'|'w'|'x' $permission One or more permission access to check.
     * @param string|null $file The file or directory path to check.
     *     Defaults to the application's writeable directory when `null`.
     * @param bool $silent Whether to suppress the exception and return `false`
     *     when a required permission is unavailable.
     *
     * @return bool `true` if all required permissions are available,
     *     otherwise `false`.
     *
     * @throws FileException If a required permission is unavailable and
     *     `$silent` is `false`.
     * 
     * @see self::hasPermission()
     */
    public static function permission(
        string $permission = 'rw',
        ?string $file = null,
        bool $silent = true
    ): bool 
    {
        $file ??= Luminova::root('writeable');

        $result = self::tryPermissions($permission, $file);

        if ($result === true) {
            return true;
        }

        if ($silent) {
            return false;
        }

        [$error, $code] = self::getPermissionError($result, $file);

        if (PRODUCTION && Logger::tryDispatch('critical', $error)) {
            return false;
        }

        throw new FileException($error, $code);
    }

    /**
     * Check whether a file or directory has the specified access permissions.
     *
     * Supports read, write, and execute permissions individually or in
     * combination. Permissions can be specified using shorthand (`r`, `w`, `x`)
     * or readable names (`read`, `write`, `execute`). Multiple permissions may
     * be separated by underscores, plus signs, or commas, and their order is
     * not significant.
     *
     * Supported permissions:
     *
     * - `r` or `read` - Read access.
     * - `w` or `write` - Write access.
     * - `x` or `execute` - Execute access.
     *
     * @param string|'read'|'write'|'execute'|'r'|'w'|'x' $permission One or more permission access to check.
     * @param string $file The file or directory path to check.
     *
     * @return bool `true` if all requested permissions are available,
     *     otherwise `false`.
     *
     * @example - Examples:
     * ```php
     * Runtime::hasPermission('read', $file);
     * Runtime::hasPermission('read_write', $file);
     * Runtime::hasPermission('write+read', $file);
     * Runtime::hasPermission('execute,read,write', $file);
     * Runtime::hasPermission('rwx', $file);
     * ```
     */
    public static function hasPermission(
        string $permission,
        string $file
    ): bool 
    {
        return self::tryPermissions($permission, $file) === true;
    }

    /**
     * Sets the default PHP timezone.
     *
     * If the specified timezone is already active, no changes are made and `true`
     * is returned. The timezone may be provided as a `DateTimeZone` instance or
     * a valid timezone identifier.
     *
     * @param DateTimeZone|string $timezone The timezone to set.
     *
     * @return bool Returns `true` on success, or `false` if the timezone is empty,
     *              invalid, or `date_default_timezone_set()` is unavailable.
     * 
     * ```php
     * $timezone = 'UTC';
     * 
     * Runtime::setTimezone();
     * echo date_default_timezone_get() // UTC
     * 
     * // Optionally apply to runtime env only
     * Env::set('app.timezone', $timezone);
     * echo env('app.timezone') // UTC
     * ```
     */
    public static function setTimezone(DateTimeZone|string $timezone): bool
    {
        $timezone = ($timezone instanceof DateTimeZone)
            ? $timezone->getName()
            : trim($timezone);

        if ($timezone === '') {
            return false;
        }
        
        if (
            date_default_timezone_get() !== $timezone &&
            !date_default_timezone_set($timezone)
        ) {
            return false;
        }

        Env::set('app.timezone', $timezone);
        return true;
    }

    /**
     * Set max script execution time (seconds).
     *
     * Updates the limit only when increasing it or disabling it (0).
     * Returns false if the operation is not allowed or fails.
     *
     * @param int $seconds Time limit in seconds (0 = unlimited)
     *
     * @return bool Returns true on success, false otherwise
     */
    public static function setExecutionTime(int $seconds): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return false;
        }

        $maxExecution = (int) ini_get('max_execution_time');

        if (
            (($maxExecution !== 0 && $seconds > $maxExecution) || ($maxExecution > 0 && $seconds === 0))
            && self::tryFunction('set_time_limit', $seconds)
        ) {
            Env::set('script.execution.limit', $seconds);
            return true;
        }
        

        return false;
    }

    /**
     * Set whether the script should continue after client disconnect.
     *
     * @param bool $ignore Whether to ignore or continue.
     * 
     * @return bool Returns true or false if the function is unavailable.
     */
    public static function setIgnoreUserAbort(bool $ignore): bool
    {
        if(!self::tryFunction('ignore_user_abort', $ignore)){
            return false;
        }

        Env::set('script.ignore.abort', (int) $ignore);

        return true;
    }

    /**
     * Set the default locale for all categories.
     * 
     * @param string $locale The default application locale to set.
     * @param int $category The category affected by the locale setting (e.g, `LC_*`).
     *
     * @return bool Returns true applied locale string, or false on failure.
     */
    public static function setLocale(string $locale, int $category = LC_ALL): bool
    {
        if($locale === '' || $locale === '0' || $locale === []){
            return false;
        }

        if(setlocale($category, $locale) === false){
            return false;
        }

        if($category === LC_ALL){
            Env::set('app.locale', $locale);
        }

        return true;
    }

    /**
     * Retrieve the configured static page cache types.
     *
     * Reads the `page.caching.statics` environment value and normalizes its
     * pipe separators by removing surrounding whitespace. The resolved value
     * is cached for subsequent calls.
     *
     * @return string A normalized pipe-separated list of static cache types,
     *                or an empty string when none are configured.
     *
     * @example - Example:
     * ```php
     * // page.caching.statics = html | json | xml
     * $types = Runtime::getStaticCacheTypes();
     *
     * // html|json|xml
     * ```
     */
    public static function getStaticCacheTypes(): string
    {
        static $types = null;

        if ($types !== null) {
            return $types;
        }

        $configs = Env::get('page.caching.statics', '');

        if ($configs === '' || $configs === null) {
            return $types = '';
        }

        $result = [];

        foreach (explode('|', $configs) as $config) {
            $value = trim($config);

            if ($value === '') {
                continue;
            }

            $result[$value] = $value;
        }

        if($result === []){
            return $types = '';
        }

        return $types = implode('|', array_values($result));
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
     * use Luminova\Runtime;
     *
     * Runtime::set('theme', 'dark');
     * Runtime::set('config', ['debug' => true]);
     * Runtime::set('config', ['cache' => false]); // merges with existing array
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
     * use Luminova\Runtime;
     *
     * Runtime::add('routes', 'home', '/');
     * Runtime::add('routes', 'login', '/login');
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
     * use Luminova\Runtime;
     * 
     * $theme = Runtime::get('theme') ?? 'light';
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
     * use Luminova\Runtime;
     * 
     * if (Runtime::has('theme')) { ... }
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
     * use Luminova\Runtime;
     *
     * // Remove a user-defined key
     * Runtime::remove('theme');
     *
     * // Force removal of a core key (not recommended)
     * Runtime::remove(Runtime::CLASS_METADATA, false);
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
     * use Luminova\Runtime;
     *
     * Runtime::clear();
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
     * use Luminova\Runtime;
     * 
     * $count = Runtime::count();
     * ```
     */
    public static function count(): int
    {
        return count(self::$storage);
    }

    /**
     * Start or stop recording application performance profiling.
     *
     * Profiling is only active when debugging is enabled and the application
     * is not running in production.
     *
     * @param string $mode The profiling mode (typically: `start` or `stop`).
     * @param null|array{
     *     command:string,
     *     name:string,
     *     group:string,
     *     arguments:string[],
     *     options: array<string,mixed>,
     *     input:string,
     *     params:string[],
     * } $command Optional CLI command context for profiling stop.
     * 
     * **Command structure:**
     *
     * ```
     * [
     *     'command'    => string,        // Original CLI input (without PHP binary)
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
    public static final function profiling(string $mode, ?array $command = null): void
    {
        if (
            !self::isProfilingEnabled()  
            || (self::$isProfiling && $mode === 'start')
            || (!self::$isProfiling && $mode === 'stop')
        ) {
            return;
        }

        match ($mode) {
            'start' => Performance::start(),
            'stop'  => Performance::stop(command: $command),
            default => throw new InvalidArgumentException(sprintf(
                'Invalid profiling mode "%s". Expected "start" or "stop".',
                $mode
            ))
        };

        self::$isProfiling = $mode === 'start';
    }

    /**
     * Determine whether performance profiling is enabled.
     *
     * Profiling must first be enabled through the debug configuration. When the
     * application is running in production, profiling must also be explicitly
     * enabled through the production configuration.
     *
     * The result is cached for the lifetime of the current request.
     *
     * @return bool `true` if performance profiling is enabled, otherwise `false`.
     */
    public static function isProfilingEnabled(): bool
    {
        static $isProfilingEnabled;

        return $isProfilingEnabled ??= Env::get('debug.show.performance.profiling', false)
            && (!PRODUCTION || Env::get('production.show.performance.profiling', false));
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
     * @ignore
     * @codeCoverageIgnore
     */
    public static function tips(): void
    {
        if (!PRODUCTION || !Env::get('debug.alert.performance.tips', false)) {
            return;
        }

        if (mt_rand(1, 100) > 1) {
            return;
        }

        $entry = new Entry('warning');
        $off = 'Disable this warning by setting "debug.alert.performance.tips=false" in your .env file.';

        if (function_exists('opcache_get_status') && !ini_get('opcache.enable')) {
            $entry->add(
                "OPcache is installed but disabled. 
                Enable it to improve performance. {$off}"
            );
        }

        if (
            Env::get('feature.route.attributes') 
            && !Env::get('feature.route.cache.attributes', false)
        ) {
            $entry->add(
                "Route attribute caching is disabled. 
                Turn it on to avoid repeated reflection scans. {$off}"
            );
        }

        if (!$entry->isEmpty()) {
            $entry->log();
        }
    }

    /**
     * Detect a supported runtime environment.
     *
     * Detection uses runtime-specific marker files, Linux cgroup information,
     * and the `RUNTIME_ENV` environment variable as a fallback for explicitly
     * requested runtimes.
     *
     * @param string|null $runtime Runtime name to detect, or `null` to detect
     *                             any supported runtime.
     *
     * @return bool `true` if the requested runtime is detected, otherwise `false`.
     */
    private static function whichRuntime(?string $runtime): bool
    {
        if ($runtime !== null && !isset(self::RUNTIME_ENV[$runtime])) {
            return false;
        }

        if ($runtime === null) {
            if (
                is_file('/.dockerenv') 
                || is_file('/run/.containerenv')
                || getenv('container') === 'podman'
            ) {
                return true;
            }

            $pattern = implode('|', self::RUNTIME_ENV);
        } else {
            if ($runtime === 'docker' && is_file('/.dockerenv')) {
                return true;
            }

            if ($runtime === 'podman') {
                if (getenv('container') === 'podman' || is_file('/run/.containerenv')) {
                    return true;
                }
            }

            $pattern = self::RUNTIME_ENV[$runtime] . '|' . $runtime;
        }

        if (is_readable('/proc/1/cgroup')) {
            $cgroup = file_get_contents('/proc/1/cgroup');

            if (
                $cgroup !== false
                && preg_match('/(?:' . $pattern . ')/i', $cgroup) === 1
            ) {
                return true;
            }
        }

        if ($runtime === null) {
            return false;
        }

        return preg_match(
            '/(?:' . $pattern . ')/i',
            (string) self::name()
        ) === 1;
    }

    /**
     * Build the permission error message and error code.
     *
     * Converts the supplied permission expression into a readable permission
     * name and determines the appropriate error code. Supports shorthand and
     * readable permission names, including combined permissions.
     *
     * @param string $name The permission expression name that failed.
     * @param string $file The file or directory path associated with the error.
     *
     * @return array{0: string, 1: int} The error message and error code.
     */
    private static function getPermissionError(string $name, string $file): array
    {
        $code = match ($name) {
            'read'    => ErrorCode::READ_PERMISSION_DENIED,
            'write'   => ErrorCode::WRITE_PERMISSION_DENIED,
            'execute' => ErrorCode::EXECUTE_PERMISSION_DENIED,
            default   => ErrorCode::PERMISSION_DENIED,
        };

        return [
            sprintf(
                "Permission '%s' denied for: '%s'.", 
                $name,
                $file
            ),
            $code
        ];
    }

    /**
     * Parse a file access permission expression into individual permissions.
     *
     * Supports shorthand permissions (`r`, `w`, `x`) and readable permission
     * names (`read`, `write`, `execute`). Combined permissions may be separated
     * by underscores, plus signs, or commas, and their order is not significant.
     *
     * @param string $permission The permission expression to parse.
     *
     * @return array<int,string>|null The parsed permissions, or `null` if the
     *     expression is empty or cannot be parsed.
     */
    private static function parsePermissions(string $permission): ?array
    {
        $permission = strtolower(trim($permission));

        if ($permission === '') {
            return null;
        }

        if (preg_match('/^[rwx]+$/', $permission)) {
            return str_split($permission);
        }

        $permissions = preg_split(
            '/[_+,]+/',
            $permission,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        return $permissions ?: null;
    }

    /**
     * Check the requested file access permissions and return the first failure.
     *
     * Parses the permission expression and checks each requested permission
     * against the specified file or directory. Returns the readable name of
     * the first permission that is unavailable, `unknown` when the expression
     * is invalid, or `true` when all requested permissions are available.
     *
     * @param string $permission The permission expression to check.
     * @param string $file The file or directory path to check.
     *
     * @return string|bool The name of the first unavailable permission,
     *     `unknown` for an invalid permission expression, or `true` when all
     *     requested permissions are available.
     */
    private static function tryPermissions(
        string $permission,
        string $file
    ): string|bool 
    {
        $permissions = self::parsePermissions($permission);

        if ($permissions === null) {
            return 'unknown';
        }

        foreach ($permissions as $expression) {
            [$isPermission, $name] = match ($expression) {
                'r', 'read'    => [is_readable($file), 'read'],
                'w', 'write'   => [is_writable($file), 'write'],
                'x', 'execute' => [is_executable($file), 'execute'],
                default        => [false, 'unknown'],
            };

            if (!$isPermission) {
                return $name;
            }
        }

        return true;
    }
}