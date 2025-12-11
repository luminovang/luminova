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
use \Luminova\Config\Env;

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
defined('APP_ROOT') || define('APP_ROOT', (static function () :string {
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
 * Register environment variables from a `.env` file.
 */
Env::register();

/**
 * Sets an environment variable, optionally saving it to the `.env` file.
 *
 * @param string $key The environment variable key.
 * @param string $value The value to set for the environment variable.
 * @param bool $persist Whether to store/update the variable in the `.env` file (default: false).
 * 
 * @return bool Returns true on success, false on failure.
 * @see \Luminova\Config\Env::set() for more details on how the variable is stored and persisted.
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

   return Env::set($key, $value, $persist);
}

/**
 * Retrieve an environment variable's value.
 *
 * @param string $key The name of the environment variable.
 * @param mixed $default Optional fallback value if the key is not found (default: null).
 * 
 * @return array|string|int|float|bool|null The environment variable's value, or the default if not found.
 *         Supports automatic type conversion for booleans, numbers, null, and arrays.
 * 
 * @see \Luminova\Config\Env::get() for details on how the value is retrieved and parsed.
 */
function env(string $key, mixed $default = null): mixed 
{
    $key = trim($key);

    if ($key === '') {
        return '';
    }

    return Env::get($key, $default);
}

/**
 * Application document root (public front controller) directory.
 * 
 * @var string DOCUMENT_ROOT (e.g, `/path/to/project/public/`, `/usr/www/example.com/public/`)
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
 * Application local environment boolean flag.
 * 
 * @var bool IS_LOCAL
 */
defined('IS_LOCAL') || define('IS_LOCAL', Env::isLocal());

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
 * Novakit development server executable script path.
 * 
 * @var ?string NOVAKIT_ENV
 */
defined('NOVAKIT_ENV') || define('NOVAKIT_ENV', ($_SERVER['NOVAKIT_EXECUTION_ENV'] ?? null));

/**
 * Application index controller file.
 * 
 * @var string APP_CONTROLLER_INDEX  (e.g, `www/example.com/public/index.php`)
 * > Based on base-directory of your project. returns empty on PHP development server.
 */
defined('APP_CONTROLLER_INDEX') || define('APP_CONTROLLER_INDEX', 
    trim(dirname($_SERVER['SCRIPT_NAME'] ?? DOCUMENT_ROOT . 'index.php'), TRIM_DS)
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
 * @deprecated Use APP_CONTROLLER_INDEX instead
 */
defined('CONTROLLER_SCRIPT_PATH') || define('CONTROLLER_SCRIPT_PATH', APP_CONTROLLER_INDEX);

/**
 * Application on localhost or what.
 * 
 * @var bool IS_LOCALHOST
 * @deprecated Use IS_LOCAL instead
 */
defined('IS_LOCALHOST') || define('IS_LOCALHOST', IS_LOCAL);