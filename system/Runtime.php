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
use Throwable;
use DateTimeZone;
use Luminova\Luminova;
use Luminova\Config\Env;
use Luminova\Logger\Entry;
use Luminova\Logger\Logger;
use Luminova\Utility\Converter;
use Luminova\Debugger\Performance;
use Luminova\Exceptions\ErrorCode;
use Luminova\Exceptions\FileException;
use Luminova\Exceptions\RuntimeException;
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
        'k8s'        => 'kubepods',
        'containerd' => 'containerd',
        'podman'     => 'libpod',
        'novakit'    => 'novakit',
    ];

    /** 
     * Canonical runtime names.
     * 
     * @var array<string,string> PLATFORM_ALIASES
     */
    private const PLATFORM_ALIASES = [
        'macos'   => 'mac',
        'osx'     => 'mac',
        'darwin'  => 'mac',
        'win'     => 'windows',
        'win32'   => 'windows',
        'sunos'   => 'solaris',
        'k8s'     => 'kubernetes',
        'amazon'  => 'aws',
        'google'  => 'gcp',
        'gcloud'  => 'gcp',
        'alicloud' => 'alibaba',
        'aliyun'   => 'alibaba',
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
     * @var bool isStartedProfiling
     */
    private static bool $isStartedProfiling = false;

    /**
	 * Output compression enable.
	 *
	 * @var bool|null $obCompression
	 */
	private static ?bool $obCompression = null;

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
            || self::isPhpServer()
            || self::isLocalAddress();
    }

    /**
     * Determine whether the application is running through 
     * the Luminova PHP development server.
     *
     * Checks the PHP built-in server SAPI and verifies the server identifier
     * against the current NOVAKIT version to distinguish Luminova-managed
     * server instances from other PHP development servers.
     *
     * @return bool Returns true if running through the Luminova PHP development server, false otherwise.
     */
    public static function isPhpServer(): bool
    {
        if (PHP_SAPI !== 'cli-server') {
            return false;
        }

        $phpServer = $_SERVER['LUMINOVA_PHP_CLI_SERVER'] 
            ?? $_ENV['LUMINOVA_PHP_CLI_SERVER']
            ?? getenv('LUMINOVA_PHP_CLI_SERVER');

        return !empty($phpServer) 
            && Luminova::NOVAKIT_VERSION === $phpServer;
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
     */
    public static function isLocalhost(): bool
    {
        static $isLocalhost = null;

        if ($isLocalhost !== null) {
            return $isLocalhost;
        }

         if(self::isCommand()){
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
     * Determines whether the application is running in production mode.
     *
     * Uses the PRODUCTION constant when defined; otherwise, determines the
     * environment based on whether the application is running on localhost.
     *
     * @return bool Returns true if the application is in production mode, false otherwise.
     */
    public static function isProduction(): bool
    {
        static $isProd = null;

        return $isProd ??= (
            (defined('PRODUCTION') && PRODUCTION) 
            || !self::isLocalhost()
        );
    }

    /**
     * Determines whether the application is running in staging mode.
     *
     * Checks the STAGING constant and caches the result for subsequent calls.
     *
     * @return bool Returns true if the application is in staging mode, false otherwise.
     */
    public static function isStaging(): bool
    {
        static $isStaging = null;

        return $isStaging ??= (defined('STAGING') && STAGING);
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
     * Determine whether the current environment is Windows Subsystem for Linux (WSL).
     *
     * @return bool True if running under WSL, otherwise false.
     */
    public static function isWsl(): bool
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return false;
        }

        if (Env::has('WSL_DISTRO_NAME', 'WSL_INTEROP')) {
            return true;
        }

        $version = self::readFile('/proc/version');

        return $version !== null
            && stripos($version, 'microsoft') !== false;
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
        $runtime = Env::raw('RUNTIME_ENV', $exists);

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
            in_array(PHP_SAPI, ['cli', 'phpdbg'], true)
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

        return $isHmvc ??= (bool) Env::get(
            'feature.app.hmvc', 
            false
        );
    }

    /**
     * Determine whether the current runtime matches any of the specified
     * operating systems, runtime environments, or cloud platforms.
     *
     * Supports predefined platform names and aliases, including macOS, Windows,
     * Linux, BSD variants, Solaris, WSL, Docker, Kubernetes, AWS, Azure, and GCP.
     * Unknown names are matched case-insensitively against the operating system
     * name returned by `php_uname('s')`.
     *
     * Multiple names may be provided. Returns `true` if any name matches the
     * current runtime, otherwise `false`. Empty names are ignored.
     *
     * @param string ...$names One or more platform names or aliases to check.
     *
     * @return bool True if the runtime matches at least one specified platform, otherwise false.
     *
     * @example - Examples:
     * ```php
     * Runtime::isPlatform('windows');
     * Runtime::isPlatform('linux');
     * Runtime::isPlatform('macos');
     * Runtime::isPlatform('wsl');
     * Runtime::isPlatform('docker');
     * Runtime::isPlatform('kubernetes');
     * Runtime::isPlatform('aws');
     * Runtime::isPlatform('azure');
     * Runtime::isPlatform('gcp');
     * Runtime::isPlatform('openbsd');
     *
     * // Match any
     * Runtime::isPlatform('linux', 'mac');
     * Runtime::isPlatform('aws', 'azure', 'gcp');
     * ```
     */
    public static function isPlatform(string ...$names): bool
    {
        foreach ($names as $name) {
            if (self::isPlatformMatch($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether PHP session upload progress tracking is enabled.
     *
     * Reads the current runtime configuration and caches the result.
     *
     * @return bool Returns true if upload progress tracking is enabled with configured name.
     * 
     * @see Luminova\Http\Uploader::progress() For tracking upload progress.
     */
    public static function isUploadProgressEnabled(): bool
    {
        static $enabled = null;

        if ($enabled !== null) {
            return $enabled;
        }

        return $enabled = filter_var(
            self::tryGetIniValue('session.upload_progress.enabled', '0'),
            FILTER_VALIDATE_BOOLEAN
        ) && self::tryGetIniValue('session.upload_progress.name', '') !== '';
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
     * Check whether the current response can be compressed.
     *
     * Compression is disabled when headers have already been sent, the request
     * uses HTTP range delivery, PHP output compression is enabled, a Content-Encoding
     * header already exists, or an active output buffer uses `ob_gzhandler`.
     *
     * @return bool True if response compression can be enabled, false otherwise.
     */
	public static function isOutputCompressionEnabled(): bool
	{
		return self::$obCompression ??= self::canCompressResponse();
	}

	/**
	 * Check whether response compression is enabled.
	 *
	 * Verifies that response compression is enabled in configuration and that
	 * the current response environment supports compression (`env(output.compression.enable)`).
	 *
	 * @return bool True if response compression is enabled and available.
	 */
	public static function isResponseCompressionEnabled(): bool
	{
		if ((bool) Env::get('output.compression.enable', false) === false) {
			return false;
		}

		return self::isOutputCompressionEnabled();
	}

    /**
     * Defines the standard CLI stream constants.
     *
     * The method defines `STDIN`, `STDOUT`, and `STDERR` when they are not already defined.
     * Opens `php://stdin`, `php://stdout`, and `php://stderr` using `tryFopen()`.
     *
     * @return void
     *
     * @throws RuntimeException If a required stream cannot be opened.
     */
    public static function defineStandardStreams(): void
    {
        defined('STDIN') || define('STDIN', self::tryFopen('php://stdin', 'r'));
        defined('STDOUT') || define('STDOUT', self::tryFopen('php://stdout', 'w'));
        defined('STDERR') || define('STDERR', self::tryFopen('php://stderr', 'w'));
    }

    /**
     * Clears or flushes PHP output buffers using the selected mode.
     *
     * Supported modes:
     * - `auto`: Clears buffers while preserving the base buffer level.
     * - `all`: Clears buffers until the specified minimum level is reached.
     * - `top`: Clears only the current top-most buffer.
     * - `flush`: Flushes the current top-most buffer without ending it.
     *
     * This method does nothing in CLI and PHPDBG environments.
     *
     * @param 'auto'|'all'|'top'|'flush' $mode Buffer handling mode.
     * @param int $limit Minimum output buffer level to preserve in `auto`
     *                   and `all` modes. Must be greater than or equal to 0.
     *                   A value of 0 allows all buffers to be cleared.
     *
     * @return bool True if at least one buffer was cleared or flushed
     *              successfully, false otherwise.
     */
    public static function cleanOutputBuffers(string $mode = 'auto', int $limit = 0): bool
    {
        if (self::isCommand()) {
            return false;
        }

        if ($limit < 0 || !in_array($mode, ['auto', 'all', 'top', 'flush'], true)) {
            return false;
        }

        $level = ob_get_level();

        if ($level === 0) {
            return false;
        }

        switch ($mode) {
            case 'top':
                return @ob_end_clean();

            case 'flush':
                if (!@ob_flush()) {
                    return false;
                }

                flush();
                return true;

            case 'auto':
            case 'all':
                $stopAt = ($mode === 'auto') ? max($limit, 1) : $limit;
                $cleared = false;

                while (ob_get_level() > $stopAt) {
                    if (!@ob_end_clean()) {
                        break;
                    }

                    $cleared = true;
                }

                return $cleared;
        }

        return false;
    }

    /**
     * Starts an output buffer with an optional compression handler.
     *
     * When response compression is enabled and the compression handler is
     * requested, the supplied handler takes precedence over the configured
     * handler. If neither is provided, `ob_gzhandler` is used as the default.
     *
     * A normal output buffer is started when compression is disabled or
     * the compression handler is not requested.
     *
     * Existing output buffers can optionally be cleared before starting
     * the new buffer.
     *
     * @param bool $clearIfSet Whether to clear existing output buffers before
     *                         starting a new buffer (default: false).
     * @param bool $useCompressionHandler Whether to use a compression handler
     *                                    when response compression is enabled
     *                                    (default: true).
     * @param callable|null $handler Optional output handler. When null, the
     *                               handler is resolved from the
     *                               `output.compression.handler` environment
     *                               setting, falling back to `ob_gzhandler`.
     * @param int $chunkSize The output buffer chunk size (default: 0).
     * @param int $flags Bitmask controlling the output buffer operations
     *                   allowed for this buffer (default: PHP_OUTPUT_HANDLER_STDFLAGS).
     *
     * @return bool True if the output buffer was started successfully,
     *              false if headers were already sent, existing buffers
     *              could not be cleared, or buffer creation failed.
     *
     * @throws InvalidArgumentException If the configured output handler is invalid.
     */
    public static function setOutputHandler(
        bool $clearIfSet = false,
        bool $useCompressionHandler = true,
        ?callable $handler = null,
        int $chunkSize = 0,
        int $flags = PHP_OUTPUT_HANDLER_STDFLAGS
    ): bool 
    {
        if (headers_sent()) {
            return false;
        }

        if (ob_get_level() > 0) {
            if (!$clearIfSet || !self::cleanOutputBuffers('all')) {
                return false;
            }
        }

        if (!$useCompressionHandler || !self::isResponseCompressionEnabled()) {
            return ob_start(null, $chunkSize, $flags);
        }

        $callback = $handler ?? Env::get('output.compression.handler', null);

        if ($callback === null || $callback === '') {
            $callback = 'ob_gzhandler';
        } elseif (!is_callable($callback)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid output handler %s.',
                ($handler === null)
                    ? 'configured in "output.compression.handler"'
                    : get_debug_type($handler)
            ));
        }

        return ob_start($callback, $chunkSize, $flags);
    }

    /**
     * Returns the list of disabled PHP functions (via `disable_functions` directive).
     *
     * This method retrieves the list of functions that are disabled in the PHP configuration. 
     * It can return the list as a simple array of function names or as an associative array
     * with function names as keys and `true` as values.
     * 
     * @param bool $flipValue Whether to return an associative lookup array.
     * 
     * @return ($flipValue is true ? array<string, true> : array<int, string>) Returns an array of disabled function names or an associative array if `$flipValue` is true.
     */
    public static function disabledFunctions(bool $flipValue = false): array
    {
        if (self::$disabledFunctions !== null) {
            return ($flipValue && isset(self::$disabledFunctions[0])) 
                ? array_flip(self::$disabledFunctions) 
                : self::$disabledFunctions;
        }

        $list = self::tryGetIniValue('disable_functions') ?: '';

        if ($list === '') {
            return self::$disabledFunctions = [];
        }

        $disabled = [];

        foreach (explode(',', $list) as $function) {
            $function = trim($function);
            if($function === ''){
                continue;
            }

            if ($flipValue) {
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

        if (
            self::isProduction()
            && Logger::tryDispatch('critical', $error)
        ) {
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
     * Get the current PHP memory limit in bytes.
     *
     * Returns the configured PHP memory limit as an integer value in bytes.
     * Returns `0` when the memory limit cannot be determined and `PHP_INT_MAX`
     * when the memory limit is unlimited.
     *
     * The returned value can optionally be divided and a minimum reserve can
     * be enforced.
     *
     * @param int $division Optional positive division factor used to scale down
     *                       the memory limit.
     * @param int|null $reserve Optional minimum memory value in bytes.
     *
     * @return int The memory limit in bytes, or `0` when it cannot be determined.
     *
     * @example - Example:
     * ```php
     * $memoryLimit = Runtime::memoryLimit();
     * echo $memoryLimit; // e.g., 134217728 (128MB)
     * ```
     *
     * @example - Division Example:
     * ```php
     * $memoryLimit = Runtime::memoryLimit(division: 3);
     * echo $memoryLimit; // e.g., 44739242
     * ```
     *
     * @see self::setMemoryLimit() for setting runtime memory limit.
     * @see self::safeMemoryLimit() for calculating a safe memory limit for an operation.
     * @see Converter::toBytes() for converting memory limit strings to bytes.
     */
    public static function memoryLimit(
        int $division = 0,
        int $reserve = 0
    ): int 
    {
        $limit = self::tryGetIniValue('memory_limit');

        if ($limit === false) {
            return 0;
        }

        $limit = trim($limit);

        $bytes = ($limit === '' || $limit === '-1') 
            ? PHP_INT_MAX 
            : Converter::toBytes($limit);

        if ($division > 0) {
            $bytes = intdiv($bytes, $division);
        }

        if ($reserve > 0) {
            $bytes = max($reserve, $bytes);
        }

        return $bytes;
    }

    /**
     * Sets a PHP configuration directive at runtime.
     *
     * @param string $name The PHP configuration directive name.
     * @param string|int|float|bool|null $value The value to assign.
     * @param string|null $label Optional label used in error messages.
     *
     * @return string Returns the previous value of the directive.
     * @throws RuntimeException If the configuration directive cannot be updated.
     * 
     * @see ini_set()
     * @see self::trySetIniValue()
     */
    public static function setIniValue(
        string $name,
        string|int|float|bool|null $value,
        ?string $label = null
    ): string 
    {
        static $iniAvailable = null;
        $iniAvailable ??= function_exists('ini_set');

        if ($iniAvailable === false) {
            throw new RuntimeException(
                'PHP function "ini_set" is disabled or unavailable.'
            );
        }

        $result = ini_set($name, $value);

        if ($result === false) {
            throw new RuntimeException(sprintf(
                'Unable to configure %s with value: %s.',
                $label ?? sprintf('PHP directive "%s"', $name),
                var_export($value, true)
            ));
        }

        return $result;
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
     * @return string|false Returns the previous value on success, or false on failure.
     */
    public static function trySetIniValue(string $name, mixed $value): string|false
    {
        static $iniAvailable = null;

        if ($iniAvailable ??= function_exists('ini_set')) {
            return @ini_set($name, (string) $value);
        }

        return false;
    }

    /**
     * Retrieves the value of a defined PHP constant.
     *
     * Returns the specified default value if the constant is not defined.
     *
     * @param string $name The constant name, including its class or namespace if applicable.
     * @param mixed $default The fallback value when the constant is not defined.
     *
     * @return mixed Returns the constant value if defined, otherwise the default value.
     */
    public static function getConstantValue(string $name, mixed $default = null): mixed
    {
        return defined($name) 
            ? constant($name) 
            : $default;
    }

    /**
     * Retrieves the value of a PHP configuration directive.
     *
     * @param string $name The PHP configuration directive name.
     *
     * @return string Returns the directive value.
     * @throws RuntimeException If ini_get() is unavailable or the directive cannot be read.
     * 
     * @see self::tryGetIniValue()
     */
    public static function getIniValue(string $name): string
    {
        $result = self::tryGetIniValue($name);

        if ($result === false) {
            throw new RuntimeException(sprintf(
                'Unable to retrieve PHP configuration directive "%s".', 
                $name
            ));
        }

        return $result;
    }

    /**
     * Attempts to retrieve the value of a PHP configuration directive.
     *
     * @param string $name The PHP configuration directive name.
     * @param string|false $default Default value returned when the directive cannot be read.
     *
     * @return string|false Returns the directive value, or the default value on failure.
     * 
     * @see self::getIniValue()
     */
    public static function tryGetIniValue(
        string $name,
        string|false $default = false
    ): string|false
    {
        static $iniGetAvailable = null;

        if (!($iniGetAvailable ??= function_exists('ini_get'))) {
            return $default;
        }

        $result = @ini_get($name);

        return ($result === false) ? $default : $result;
    }

    /**
     * Calculate a safe memory limit for an operation.
     *
     * Returns the requested memory amount without exceeding the available
     * PHP memory limit. The current memory usage is deducted from the
     * configured PHP memory limit, and only half of the remaining memory
     * is made available to provide a safety margin for additional
     * allocations and runtime overhead.
     *
     * When PHP memory usage cannot be determined, `0` is returned.
     * When PHP has no memory limit, the requested amount is returned.
     *
     * @param int $megabytes Maximum memory to allow in megabytes.
     *
     * @return int The safe memory limit in bytes.
     * 
     * @see self::setMemoryLimit() for setting runtime memory limit.
     */
    public static function safeMemoryLimit(int $megabytes = 16): int
    {
        $limit = self::memoryLimit();

        if ($limit === 0) {
            return 0;
        }

        $requested = max(1, $megabytes) * 1024 * 1024;

        if ($limit === PHP_INT_MAX) {
            return $requested;
        }

        $available = $limit - memory_get_usage(true);

        if ($available <= 0) {
            return 0;
        }

        return min($requested, intdiv($available, 2));
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
     * Set the PHP script execution time limit at runtime.
     *
     * In safe mode, the requested execution time will not exceed the current
     * PHP `max_execution_time` unless the current limit is unlimited. A value
     * of `0` represents an unlimited execution time.
     *
     * When safe mode is disabled, the requested execution time may increase
     * or decrease the current PHP execution limit.
     *
     * @param int $seconds Maximum execution time in seconds. Use `0` for
     *                     unlimited execution time.
     *
     * @return bool Returns `true` if the execution limit was changed or already
     *              matched, otherwise `false`.
     * 
     * @see self::setMemoryLimit()
     */
    public static function setExecutionTime(int $seconds): bool 
    {
        if ($seconds < 0 || self::isPlatform('windows')) {
            return false;
        }

        $current = self::tryGetIniValue('max_execution_time');

        if($current === false){
            return false;
        }

        $current = (int) $current;

        if ($current > 0) {
            $seconds = min($seconds, $current);
        } elseif ($current === 0 && $seconds > 0) {
            return true;
        }

        if ($current === $seconds) {
            return true;
        }

        if (!self::tryFunction('set_time_limit', $seconds)) {
            return false;
        }

        Env::set('script.execution.limit', $seconds);

        return true;
    }

    /**
     * Set the PHP memory limit at runtime.
     *
     * In safe mode, the requested limit is capped at the current PHP memory
     * limit and will not increase the configured limit. When safe mode is
     * disabled, the requested limit may increase the current PHP memory limit.
     *
     * The limit is never reduced below the memory currently in use.
     *
     * @param int $megabytes Memory limit in megabytes.
     * @param bool $safeMode Whether to prevent increasing the current PHP
     *                       memory limit and adjust megabytes based available limit.
     *
     * @return bool Returns `true` if the memory limit was changed or already satisfied,
     *              otherwise `false`.
     * 
     * @see self::setExecutionTime()
     */
    public static function setMemoryLimit(
        int $megabytes,
        bool $safeMode = true
    ): bool 
    {
        if ($megabytes <= 0) {
            return false;
        }

        $current = self::memoryLimit();

        if ($current === 0) {
            return false;
        }

        $requested = $megabytes * 1024 * 1024;
        $usage = memory_get_usage(true);

        if ($safeMode) {
            $requested = max($requested, $usage);

            if ($current !== PHP_INT_MAX) {
                $requested = min($requested, $current);
            }
        }

        if ($requested < $usage) {
            return false;
        }

        if ($current === $requested) {
            return true;
        }

        return ini_set('memory_limit', $requested . 'B') !== false;
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
     * @example - Example:
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
     * @param 'start|'stop' $mode The profiling mode (typically: `start` or `stop`).
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
            || (self::$isStartedProfiling && $mode === 'start')
            || (!self::$isStartedProfiling && $mode === 'stop')
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

        self::$isStartedProfiling = $mode === 'start';
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
        static $isProfilingEnabled = null;

        return $isProfilingEnabled ??= Env::get(
            'debug.show.performance.profiling', 
            false
        )
            && (
                !self::isProduction()
                || Env::get('production.show.performance.profiling', false)
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
     * 
     * @ignore
     * @codeCoverageIgnore
     */
    public static function tips(): void
    {
        if (
            !self::isProduction()
            || !Env::get('debug.alert.performance.tips', false)
        ) {
            return;
        }

        if (mt_rand(1, 100) > 1) {
            return;
        }

        $entry = new Entry('warning');
        $off = 'Disable this warning by setting "debug.alert.performance.tips=false" in your .env file.';

        if (
            function_exists('opcache_get_status') 
            && !self::tryGetIniValue('opcache.enable')
        ) {
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
                @is_file('/.dockerenv') 
                || @is_file('/run/.containerenv')
                || getenv('container') === 'podman'
            ) {
                return true;
            }

            $pattern = implode('|', self::RUNTIME_ENV);
        } else {
            if ($runtime === 'docker' && @is_file('/.dockerenv')) {
                return true;
            }

            if ($runtime === 'podman') {
                if (getenv('container') === 'podman' || @is_file('/run/.containerenv')) {
                    return true;
                }
            }

            $pattern = self::RUNTIME_ENV[$runtime] . '|' . $runtime;

            if($runtime === 'docker'){
                $pattern .= '|containerd';
            }
        }

        if ($runtime === null) {
            return false;
        }

        $cgroup = self::readFile('/proc/1/cgroup') 
            ?? self::name();

        return $cgroup !== null && preg_match(
            '/(?:' . $pattern . ')/i',
            $cgroup
        ) === 1;
    }

    /**
     * Whether the current runtime matches the given platform name.
     * 
     * Unknown or empty names return false.
     *
     * @param string $name The platform name to match.
     * @return bool `true` if the current platform matches the specified name, otherwise `false`.
     */
    private static function isPlatformMatch(string $name): bool
    {
        static $platforms = [];
        $name = strtolower(trim($name));

        if ($name === '') {
            return false;
        }

        $name = self::PLATFORM_ALIASES[$name] ?? $name;

        return $platforms[$name] ??= self::detectPlatform($name);
    }

    /**
     * Detect platform.
     *
     * @param string $name Platform name.
     * 
     * @return bool Return true if match.
     */
    private static function detectPlatform(string $name): bool
    {
        return match ($name) {
            // Operating systems
            'mac'       => PHP_OS_FAMILY === 'Darwin',
            'windows'   => PHP_OS_FAMILY === 'Windows' 
                || DIRECTORY_SEPARATOR === '\\',
            'linux'     => PHP_OS_FAMILY === 'Linux',
            'freebsd'   => PHP_OS === 'FreeBSD',
            'openbsd'   => PHP_OS === 'OpenBSD',
            'netbsd'    => PHP_OS === 'NetBSD',
            'dragonfly' => PHP_OS === 'DragonFly',
            'bsd'       => PHP_OS_FAMILY === 'BSD' 
                || str_ends_with(PHP_OS, 'BSD'),
            'solaris'   => PHP_OS_FAMILY === 'Solaris',

            // Runtime flavours
            'wsl'        => self::isWsl(),
            'docker'     => self::isDocker(),
            'kubernetes' => Env::has('KUBERNETES_SERVICE_HOST'),

            // Cloud providers
            'aws'   => Env::has(
                'AWS_EXECUTION_ENV',
                'AWS_REGION',
                'AWS_LAMBDA_FUNCTION_NAME',
                'ECS_CONTAINER_METADATA_URI_V4',
                'ECS_CONTAINER_METADATA_URI',
            ),
            'azure' => Env::has(
                'WEBSITE_INSTANCE_ID',
                'AZURE_FUNCTIONS_ENVIRONMENT',
                'CONTAINER_APP_NAME',
            ),
            'gcp'   => Env::has(
                'K_SERVICE',
                'GAE_ENV',
                'GAE_APPLICATION',
                'FUNCTION_TARGET',
                'GOOGLE_CLOUD_PROJECT',
            ),
            'alibaba' => Env::has(
                'ALIBABA_CLOUD_ACCOUNT_ID',
                'ALIBABA_CLOUD_ACCESS_KEY_ID',
                'FC_FUNCTION_NAME',
                'FC_SERVICE_NAME',
            ),
            default => $name === strtolower(php_uname('s')),
        };
    }
         
    /**
     * Read a small system file without raising warnings.
     *
     * @param string $path
     * @param int $byte 
     * 
     * @return string|null
     */
    private static function readFile(string $path, int $byte = 8192): ?string
    {
        if (!@is_readable($path)) {
            return null;
        }

        $contents = @file_get_contents(
            $path, 
            false, 
            null, 
            0, 
            $byte
        );

        return ($contents === false) ? null : $contents;
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
     * Determines whether the current response can be compressed.
     *
     * Compression is disabled when headers have already been sent, the request
     * uses HTTP range delivery, PHP output compression is enabled, a Content-Encoding
     * header already exists, or an active output buffer uses `ob_gzhandler`.
     *
     * @return bool True if response compression can be enabled, false otherwise.
     */
    private static function canCompressResponse(): bool
    {
        if (
            headers_sent()
            || !empty($_SERVER['HTTP_RANGE'])
            || filter_var(self::tryGetIniValue('zlib.output_compression'), FILTER_VALIDATE_BOOL)
        ) {
            return false;
        }

        foreach (headers_list() as $header) {
            if (preg_match('/^\s*Content-Encoding\s*:/i', $header)) {
                return false;
            }
        }

        foreach (ob_get_status(true) as $buffer) {
            if (($buffer['name'] ?? '') === 'ob_gzhandler') {
                return false;
            }
        }

        return true;
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

    /**
     * Opens a file using `fopen()` with exception handling.
     *
     * If the file can't be opened or an error occurs, a RuntimeException is thrown.
     *
     * @param string $filename Path to the file.
     * @param string $mode File access mode (e.g., 'r', 'w').
     *
     * @return resource|null Return a valid stream resource.
     * @throws \RuntimeException If the file can't be opened.
     */
    private static function tryFopen(string $filename, string $mode): mixed
    {
        $error = null;
        $handle = null;

        try {
            $handle = @fopen($filename, $mode);
        } catch (Throwable $e) {
            $error = $e;
        }

        if ($handle === false || !is_resource($handle)) {
            if(str_starts_with($filename, 'php://std')){
                return $filename;
            }

            throw new \RuntimeException(sprintf(
                'Failed to open file "%s" with mode "%s"%s',
                $filename,
                $mode,
                $error ? ': ' . $error->getMessage() : ''
            ), previous: $error);
        }

        return $handle;
    }
}