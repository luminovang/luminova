<?php 
declare(strict_types=1);
/**
 * Luminova Framework constants and initialization functions.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */

if (defined('APP_ROOT')) {
    return;
}

if (!function_exists('json_validate')) {
    /**
     * Check if the input is a valid JSON object.
     *
     * @param mixed $input The input to check.
     * @param int $depth Maximum nesting depth of the structure being decoded (default: 512).
     * @param int $flags Optional flags (default: 0).
     *
     * @return bool Returns true if the input is valid JSON; false otherwise.
     */
    function json_validate(mixed $input, int $depth = 512, int $flags = 0): bool
    {
       if (!is_string($input)) {
            return false;
        }
        
        json_decode($input, null, $depth, $flags);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('array_is_list')) {
    /**
     * Check if array is list.
     * 
     * @param array $array The array to check.
     * 
     * @return bool Return true if array is sequential, false otherwise.
     */
    function array_is_list(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        if (!isset($array[0])) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }
}

if (!function_exists('array_first')) {
    /**
     * Get the first element of an array.
     *
     * Returns null if the array is empty. Works for both indexed and associative arrays.
     *
     * @param array $array The array to get the first element from.
     * 
     * @return mixed|null Return the first element of the array, or null if empty.
     */
    function array_first(array $array): mixed
    {
        return ($array === []) ? null : $array[array_key_first($array)];
    }
}

if (!function_exists('array_last')) {
    /**
     * Get the last element of an array.
     *
     * Returns null if the array is empty. Works for both indexed and associative arrays.
     *
     * @param array $array The array to get the last element from.
     * 
     * @return mixed|null Return the last element of the array, or null if empty.
     */
    function array_last(array $array): mixed
    {
        return ($array === []) ? null : $array[array_key_last($array)];
    }
}

/**
 * Executes a PHP function dynamically, checking if it's disabled before execution.
 *
 * This function is useful when you need to call a PHP function dynamically, 
 * but you want to ensure that the function is not disabled.
 *
 * @param string $function The name of the PHP function to execute.
 * @param mixed ...$arguments Any optional arguments to pass to the PHP function.
 *
 * @return mixed Return the result of the executed PHP function, or false if the function is disabled.
 *
 * @example - Call the 'set_time_limit' function dynamically:
 * 
 * ```php
 * $limit = set_function('set_time_limit', 300);
 * 
 * if($limit === false){
 *      echo "Execution limit is disabled";
 * }
 * ```
 */
function set_function(string $function, mixed ...$arguments): mixed 
{
    static $disables = null;

    if (!function_exists($function)) {
        return false;
    }

    $disables ??= ini_get('disable_functions');

    if ($disables && str_contains($disables, $function) ) {
       return false;
    }

    return $function(...$arguments);
}

/**
 * Sets the script's maximum execution time if the provided timeout exceeds the current limit.
 *
 * This function checks the current `max_execution_time` and compares it with the provided timeout. 
 * If the timeout is greater than the current limit and the `set_time_limit` function is not disabled, 
 * it updates the execution time to the new value.
 *
 * @param int $timeout The maximum execution time in seconds.
 *
 * @return bool Returns true if the execution time is successfully set, false otherwise.
 */
function set_max_execution_time(int $timeout): bool 
{
    if (PHP_OS_FAMILY === 'Windows') {
        return false;
    }

    $maxExecution = (int) ini_get('max_execution_time');

    if (
        ($maxExecution !== 0 && $timeout > $maxExecution) || 
        ($maxExecution > 0 && $timeout === 0)
    ) {
        return set_function('set_time_limit', $timeout);
    }

    return false;
}

/**
 * Convert a numeric string into its appropriate numeric type.
 * 
 * This method detects whether the given string represents
 * a floating-point number or an integer. If the string
 * contains a decimal point (.) or scientific notation (e),
 * it is converted to a float; otherwise, it is converted to an int.
 * 
 * @param string $value The numeric string to convert.
 * @param bool $toLowercase Whether to convert the string to lowercase before processing.
 *                    Useful when checking for scientific notation (e.g., "1E3").
 * 
 * @return float|int Returns the numeric value as int or float depending on the input.
 */
function to_numeric(string $value, bool $toLowercase = false): float|int
{
    $value = trim($value);

    if ($value === '') {
        return 0;
    }

    if ($toLowercase) {
        $value = strtolower($value);
    }

    if (ctype_digit($value)) {
        return (int) $value;
    }

    if (!is_numeric($value)) {
        return 0;
    }

    return (str_contains($value, '.') || str_contains($value, 'e'))
        ? (float) $value
        : (int) $value;
}

/**
 * Pattern to trim directory separators.
 * 
 * @var string TRIM_DS
 */
defined('TRIM_DS') || define('TRIM_DS', '/\\');

/**
 * Application project root.
 * 
 * @var string APP_ROOT
 */
defined('APP_ROOT') || define('APP_ROOT', (function () :string {
    $dir = __DIR__;

    while ($dir !== DIRECTORY_SEPARATOR) {
        if (
            file_exists($dir . '/.env') ||
            file_exists($dir . '/.dev.env')
        ) {
            return $dir . DIRECTORY_SEPARATOR;
        }

        $parent = dirname($dir);

        if ($parent === $dir) {
            break;
        }

        $dir = $parent;
    }

    return dirname(__DIR__) . DIRECTORY_SEPARATOR;
})());

/**
 * Application on localhost or what.
 * 
 * @var bool IS_LOCAL
 */
defined('IS_LOCAL') || define('IS_LOCAL', (function (): bool {

    // CLI detection
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
        return true;
    }

    // Custom override (your env flag)
    if (isset($_SERVER['NOVAKIT_EXECUTION_ENV'])) {
        return true;
    }

    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

    // Known local hosts
    if (
        in_array($serverName, ['localhost', '127.0.0.1'], true)
        || in_array($remoteAddr, ['127.0.0.1', '::1'], true)
    ) {
        return true;
    }

    return false;
})());

/**
 * Create cached version of env file.
 * 
 * @param array<string,mixed> $entries Env entries.
 * @param string $path Path to save cache.
 * 
 * @return bool Return true if saved, otherwise false.
 * @internal env helper
 */
function __cache_env(array $entries, string $path): bool 
{
    $code  = "<?php\n";
    $code .= "/**\n";
    $code .= " * Auto-generated environment cache.\n";
    $code .= " * Generated by Luminova on " . date(DATE_ATOM) . "\n";
    $code .= " */\n\n";
    $code .= "return ";
    $code .= var_export($entries, true);
    $code .= ";\n";

    return file_put_contents($path, $code) !== false;
}

/**
 * Trim env value, to strip slashes and quote.
 *
 * @param string $value The value to trim.
 * 
 * @return string Returns trimmed string.
 * @internal env helper
 */
function __trim_env(string $value): string 
{
    if($value === ''){
        return '';
    }

    if (
        (str_starts_with($value, "'") && str_ends_with($value, "'")) ||
        (str_starts_with($value, '"') && str_ends_with($value, '"'))
    ) {
        $value = substr($value, 1, -1);
    }

    return stripslashes($value);
}

/**
 * Convert env string array format to PHP array.
 * 
 * @param string $value The string to convert (e.g, `[a, b, c, [1,2,5]]`).
 * 
 * @return array Returns an array representation of string.
 * @internal env helper
 */
function __env_to_array(string $value): array {
    return array_map(function($item) {
        $item = trim($item, " \"\n\r\t\v\0");

        if (str_starts_with($item, '[') && str_ends_with($item, ']')) {
            return __env_to_array($item);
        }

        return match ($item) {
            'true'  => true,
            'false' => false,
            'null'  => null,
            is_numeric($item) => to_numeric($item, true),
            default => __trim_env($item)
        };
    }, explode(',', trim($value, '[] ')));
}

/**
 * Resolve env file based on environment.
 *  
 * @return string The path to the env file.
 */
function __env_file(bool $touch = true): ?string 
{
    $production  = APP_ROOT . '.env';
    $development = APP_ROOT . '.dev.env';
    $isLocal = (IS_LOCAL || defined('PRODUCTION') && PRODUCTION);

    if ($isLocal && is_file($development)) {
        return $development;
    }

    if (is_file($production)) {
        return $production;
    }

    if(!$touch){
        return null;
    }

    $env = $isLocal ? $development : $production;

    if (@file_put_contents($env, '') === false) {
        $break = (PHP_SAPI === 'cli') ? "\n\n" : '<br/><br/>';

        exit(sprintf(
            'Failed to create environment file.%sEnsure "%s" exists in the project root.',
            $break,
            basename($env)
        ));
    }

    return $env;
}

/**
 * Sets an environment variable, optionally saving it to the `.env` file.
 *
 * @param string $key The environment variable key.
 * @param string $value The value to set for the environment variable.
 * @param bool $persist Whether to store/update the variable in the `.env` file (default: false).
 * 
 * @return bool Returns true on success, false on failure.
 *
 * @example Temporarily set an environment variable for the current runtime:
 * ```php
 * setenv('FOO_KEY', 'foo value');
 * ```
 *
 * @example Set an environment variable and persist it in the `.env` file:
 * ```php
 * setenv('FOO_KEY', 'foo value', true);
 * ```
 *
 * @example Add or update an environment variable as a disabled entry:
 * ```php
 * setenv(';FOO_KEY', 'foo value', true);
 * ```
 */
function setenv(string $key, string $value, bool $persist = false): bool
{
    $key = trim($key);

    if ($key === '') {
        return false;
    }

    $value = trim($value);
    $isComment = str_starts_with($key, ';');

    if($isComment){
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
    }else{
        $_SERVER[$key] = $value;

        if (!getenv($key, true)) {
            putenv("{$key}={$value}");
        }

        if(IS_LOCAL){
            $_ENV[$key] = $value;
        }
    }

    if (!$persist) {
        return true;
    }

    $path = __env_file();

    try {
        $file = new SplFileObject($path, 'a+');
        $file->seek(0);

        $lines = '';
        $found = false;
        $pattern = preg_quote($isComment ? trim($key, "; \t") : $key, '/');
        $pattern = '/^[;]*\s*' . $pattern . '\s*=\s*(.*)$/mi';

        while (!$file->eof()) {
            $line = $file->fgets();
            
            if (preg_match($pattern, $line)) {
                $found = true;
                $lines .= "{$key}={$value}\n";
            }else{
                $lines .= $line;
            }
        }

        if (!$found) {
            $lines .= "\n{$key}={$value}";
        }

        $saved = (new SplFileObject($path, 'w'))->fwrite($lines) !== false;
        $envCache = APP_ROOT . 'writeable/.env-cache.php';

        if($saved && !IS_LOCAL && is_file($envCache)){
            $entries = include $envCache;
            $entries[$key] = $value;
    
            __cache_env($entries, $envCache);
        }

        $entries = $lines = null;

        return $saved;
    } catch (Throwable) {
        return false;
    }
}

/**
 * Retrieve an environment variable's value.
 *
 * @param string $key The name of the environment variable.
 * @param mixed $default Optional fallback value if the key is not found (default: null).
 * 
 * @return array|string|int|float|bool|null The environment variable's value, or the default if not found.
 *         Supports automatic type conversion for booleans, numbers, null, and arrays.
 */
function env(string $key, mixed $default = null): mixed 
{
    $key = trim($key);

    if ($key === '') {
        return '';
    }

    $value = $_SERVER[$key] 
        ?? $_ENV[$key] 
        ?? getenv($key) ?: '__ENV_KEY_EMPTY__';

    if ($value === '__ENV_KEY_EMPTY__') {
        return $default;
    }

    if (!$value || $value === true || !is_string($value)) {
        return $value;
    }

    $value = trim($value);

    if ($value === '[]' || is_numeric($value)) {
        $value = ($value === '[]') 
            ? (is_array($default) ? $default : [])
            : to_numeric($value, true);

        $_SERVER[$key] = $value;

        if(IS_LOCAL){
            $_ENV[$key] = $value;
        }

        return $value;
    }

    $type = match (strtolower($value)) {
        'true', 'enable'   => true,
        'false', 'disable' => false,
        'null'             => null,
        'blank'            => '',
        default            => '__ENV_CONTINUE_SEARCH__'
    };

    if($type === null){
        return $type;
    }

    if ($type !== '__ENV_CONTINUE_SEARCH__') {
        $_SERVER[$key] = $type;

        if(IS_LOCAL){
            $_ENV[$key] = $type;
        }

        return $type;
    }

    $value = __trim_env($value);

    if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
        $value = __env_to_array($value);
        $_SERVER[$key] = $value;

        if(IS_LOCAL){
            $_ENV[$key] = $value;
        }
    }

    return $value;
}

/**
 * Anonymous function to register environment variables from a `.env` file.
 * 
 * @return void
 */
(static function (): void {
    $envCache = APP_ROOT . 'writeable/.env-cache.php';

    if(!IS_LOCAL && is_file($envCache)){
        $_SERVER += include $envCache;
        return;
    }

    $path = __env_file();

    try {
        $entries = [];
        $params = [];
        $file = new SplFileObject($path, 'r');

        while (!$file->eof()) {
            $line = trim($file->fgets());

            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);

            if(!$key){
                continue;
            }

            $value = trim($value);

            if (preg_match_all('/\$\{([_a-zA-Z][\w\.]*)\}/', $value, $matches)) {
                $params[$key] = $value;
                continue;
            }

            if(setenv($key, $value) && !IS_LOCAL){
                $entries[$key] = env($key);
            }
        }

        if($params !== []){
            foreach ($params as $name => $param) {
                $value = preg_replace_callback(
                    '/\$\{([_a-zA-Z][\w\.]*)\}/',
                    function ($matches) use ($name): mixed {
                        $key = $matches[1];
                        $env = env($key, '__ENV_KEY_EMPTY__');

                        if ($env !== '__ENV_KEY_EMPTY__') {
                            return $env;
                        }

                        if(!IS_LOCAL){
                            return '';
                        }

                        exit(sprintf(
                            'RuntimeError: Missing environment value for parameter "%s" in "%s"',
                            $key,
                            $name
                        ));
                    },
                    $param
                );

                if (!getenv($name, true)) {
                    $strValue = is_array($value) 
                        ? '[' . implode(',', $value) . ']' 
                        : $value;

                    putenv("{$name}={$strValue}");
                }

                $_SERVER[$name] = $value;

                if(!IS_LOCAL){
                    $entries[$name] = $value;
                    continue;
                }

                $_ENV[$name] = $value;
            }
        }

        if(!IS_LOCAL){
            __cache_env($entries, $envCache);
        }

        $entries = $params = null;

        return;
    } catch (Throwable $e) {
        exit(sprintf(
            "RuntimeError: Failed to parse environment configuration.%s%s",
            !IS_LOCAL 
                ? ''
                : ((PHP_SAPI === 'cli') ? "\n\n" : '<br/><br/>') . $e->getMessage()
        ));
    }
})();

/**
 * Application document root (public front controller) directory.
 * 
 * @var string DOCUMENT_ROOT (e.g, `/path/to/project/public/`)
 */
defined('DOCUMENT_ROOT') || define('DOCUMENT_ROOT', APP_ROOT . 'public' . DIRECTORY_SEPARATOR);

/**
 * Status code indicating success code.
 * 
 * @var int STATUS_OK
 */
defined('STATUS_SUCCESS') || define('STATUS_SUCCESS', 0);

/**
 * Status code indicating failure code.
 * 
 * @var int STATUS_ERROR
 */
defined('STATUS_ERROR') || define('STATUS_ERROR', 1);

/**
 * Finish controller method without error or success status.
 * 
 * @var int STATUS_SILENCE
 */
defined('STATUS_SILENCE') || define('STATUS_SILENCE', 2);

/**
 * Application version code.
 * 
 * @var string APP_VERSION
 */
defined('APP_VERSION') || define('APP_VERSION', env('app.version', '1.0.0'));

/**
 * Application assets files version code.
 * 
 * @var string APP_FILE_VERSION
 */
defined('APP_FILE_VERSION') || define('APP_FILE_VERSION', env('app.file.version', '1.0.0'));

/**
 * Application name.
 * 
 * @var string APP_NAME
 */
defined('APP_NAME') || define('APP_NAME', env('app.name', 'Example'));

/**
 * Application state mode name.
 * 
 * @var string ENVIRONMENT
 */
defined('ENVIRONMENT') || define('ENVIRONMENT', env('app.environment.mood', 'development'));

/**
 * Application staging mode boolean flag.
 * 
 * @var bool STAGING
 */
defined('STAGING') || define('STAGING', ENVIRONMENT === 'staging');

/**
 * Application production mode boolean flag.
 * 
 * @var bool PRODUCTION
 * @example - Example:
 * ```php
 * // Strictly check production only
 * if(!STAGING && PRODUCTION){
 * 
 * }
 * 
 * // Or
 * if(PRODUCTION){
 *      if(!STAGING){
 * 
 *      }
 * }
 * ```
 * > **Note:**
 * > If `STAGING` is enabled, production will always return true.
 */
defined('PRODUCTION') || define('PRODUCTION', (STAGING || ENVIRONMENT === 'production'));

/**
 * Application maintenance mode boolean flag.
 * 
 * @var bool MAINTENANCE
 */
defined('MAINTENANCE') || define('MAINTENANCE', (bool) env('app.maintenance.mood', false));

/**
 * Application server protocol URL scheme (e.g, http or https).
 * 
 * @var string URL_SCHEME
 */
defined('URL_SCHEME') || define('URL_SCHEME', 
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'
);

/**
 * Application hostname (e.g, example.com).
 * 
 * @var string APP_HOSTNAME
 */
defined('APP_HOSTNAME') || define('APP_HOSTNAME', env('app.hostname', 'example.com'));

/**
 * Application hostname alias (e.g, www.example.com).
 * 
 * @var string APP_HOSTNAME_ALIAS
 */
defined('APP_HOSTNAME_ALIAS') || define('APP_HOSTNAME_ALIAS', 'www.' . APP_HOSTNAME);

/**
 * Application URL (e.g, http://example.com).
 * 
 * @var string APP_URL
 */
defined('APP_URL') || define('APP_URL', URL_SCHEME . '://' . APP_HOSTNAME);

/**
 * Application URL alias (e.g, http://www.example.com).
 * 
 * @var string APP_URL_ALIAS
 */
defined('APP_URL_ALIAS') || define('APP_URL_ALIAS', URL_SCHEME . '://' . APP_HOSTNAME_ALIAS);

/**
 * Application debug backtrace boolean mode.
 * 
 * @var bool SHOW_DEBUG_BACKTRACE
 */
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', (bool) env('debug.show.tracer', false));

/**
 * NovaKit development server executable script path.
 * 
 * @var bool NOVAKIT_ENV
 */
defined('NOVAKIT_ENV') || define('NOVAKIT_ENV', ($_SERVER['NOVAKIT_EXECUTION_ENV'] ?? null));

/**
 * Application project controller script path.
 * 
 * @var bool CONTROLLER_SCRIPT_PATH 
 * > Based on base-directory of your project. returns empty on PHP development server.
 */
defined('CONTROLLER_SCRIPT_PATH') || define('CONTROLLER_SCRIPT_PATH', 
    trim(dirname($_SERVER['SCRIPT_NAME'] ?? APP_ROOT . 'public/index.php'), TRIM_DS)
);

/**
 * Database fetch mode to return result as an associative array.
 * 
 * @var int FETCH_ASSOC
 */
defined('FETCH_ASSOC') || define('FETCH_ASSOC', 0);

/**
 * Database fetch mode to return result as an 2D array of integers (indexed).
 * 
 * @var int FETCH_NUM
 */
defined('FETCH_NUM') || define('FETCH_NUM', 1);

/**
 * Database fetch mode to return result as an 2D array of integers or an associative.
 * 
 * @var int FETCH_BOTH
 */
defined('FETCH_BOTH') || define('FETCH_BOTH', 2);

/**
 * Database fetch mode to return result as an object.
 * 
 * @var int FETCH_OBJ
 */
defined('FETCH_OBJ') || define('FETCH_OBJ', 3);

/**
 * Database fetch mode to return result as an array columns integer index.
 * 
 * @var int FETCH_COLUMN
 */
defined('FETCH_COLUMN') || define('FETCH_COLUMN', 4);

/**
 * Database fetch mode to return result as an object with string integer property names.
 * 
 * @var int FETCH_NUM_OBJ
 */
defined('FETCH_NUM_OBJ') || define('FETCH_NUM_OBJ', 5);

/**
 * Fetch a two-column result into an array where the first column is a key and the second column is the value.
 * 
 * @var int FETCH_KEY_PAIR
 */
defined('FETCH_KEY_PAIR') || define('FETCH_KEY_PAIR', 6);

/**
 * Fetch instance of the requested class, by mapping the columns to named properties in the class.
 * 
 * @var int FETCH_CLASS
 */
defined('FETCH_CLASS') || define('FETCH_CLASS', 7);

/**
 * Database statement return mode to return next result (single record).
 * 
 * @var int RETURN_NEXT
 */
defined('RETURN_NEXT') || define('RETURN_NEXT', 0);

/**
 * Database statement return mode to return result as 2D array integers.
 * 
 * @var int RETURN_2D_NUM
 */
defined('RETURN_2D_NUM') || define('RETURN_2D_NUM', 1);

/**
 * Database statement return mode to return last inserted id.
 * 
 * @var int RETURN_ID
 */
defined('RETURN_ID') || define('RETURN_ID', 2);

/**
 * Database statement return mode to return count of records.
 * 
 * @var int RETURN_INT
 */
defined('RETURN_INT') || define('RETURN_INT', 3);

/**
 * Database statement return mode to return number of affected rows.
 * 
 * @var int RETURN_COUNT 
 */
defined('RETURN_COUNT') || define('RETURN_COUNT', 4);

/**
 * Database statement return mode to return all result columns.
 * 
 * @var int RETURN_COLUMN
 */
defined('RETURN_COLUMN') || define('RETURN_COLUMN', 5);

/**
 * Database statement return mode to return all as results.
 * 
 * @var int RETURN_ALL
 */
defined('RETURN_ALL') || define('RETURN_ALL', 6);

/**
 * Database statement return mode to return prepared statement object.
 * 
 * @var int RETURN_STMT
 */
defined('RETURN_STMT') || define('RETURN_STMT', 7);

/**
 * Database statement return mode to return MYSQLI result object.
 * 
 * @var int RETURN_RESULT
 */
defined('RETURN_RESULT') || define('RETURN_RESULT', 8);

/**
 * Return mode to fetch rows one at a time (use in while loop).
 * 
 * @var int RETURN_STREAM
 */
defined('RETURN_STREAM') || define('RETURN_STREAM', 9);

/**
 * Null value type (e.g., for SQL NULL values)
 * 
 * @var int PARAM_NULL
 */
defined('PARAM_NULL') || define('PARAM_NULL', 0);

/**
 * Integer value type (e.g., for IDs, counters)
 * 
 * @var int PARAM_INT
 */
defined('PARAM_INT') || define('PARAM_INT', 1);

/**
 * String value type (e.g., for text, UUIDs, dates)
 * 
 * @var int PARAM_STR
 */
defined('PARAM_STR') || define('PARAM_STR', 2);

/**
 * Large Object type (e.g., BLOBs or large binary/text data)
 * 
 * @var int PARAM_LOB
 */
defined('PARAM_LOB') || define('PARAM_LOB', 3);

/**
 * Boolean value type (true/false)
 * 
 * @var int PARAM_BOOL
 */
defined('PARAM_BOOL') || define('PARAM_BOOL', 5);

/**
 * Floating-point number type (e.g., decimals)
 * Not standard in PDO; used for internal handling
 * 
 * @var int PARAM_FLOAT
 */
defined('PARAM_FLOAT') || define('PARAM_FLOAT', 192);

/**
 * Application on localhost or what.
 * 
 * @var bool IS_LOCALHOST
 * @deprecated Use IS_LOCAL instead
 */
defined('IS_LOCALHOST') || define('IS_LOCALHOST', IS_LOCAL);

/**
 * Set error reporting.
 */
error_reporting((PRODUCTION && !STAGING) ? 
    E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED :
    E_ALL
);
ini_set('display_errors', ((STAGING || !PRODUCTION) && env('debug.display.errors', false)) ? '1' : '0');

if(PHP_SAPI !== 'cli'){
    ini_set('error_prepend_string', '<span class="php-core-error">');
    ini_set('error_append_string', '</span>');
}

/**
 * Set exception tracing arguments reporting.
 */
ini_set('zend.exception_ignore_args', (!STAGING && PRODUCTION) ? '1' : '0');

/**
 * Limits the maximum execution time
 */
set_max_execution_time((int) env('script.execution.limit', 30));

/**
 * Set default timezone
 */
set_function('date_default_timezone_set', env('app.timezone', 'UTC'));

/**
 * Set internal encoding
 */
set_function('mb_internal_encoding', env('app.mb.encoding', null));

/**
 * Set whether a client disconnect should abort script execution
 */
set_function('ignore_user_abort', (bool) env('script.ignore.abort', false));