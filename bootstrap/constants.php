<?php 
declare(strict_types=1);
/**
 * Luminova Framework constants and initialization functions.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */

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
defined('APP_ROOT') || define('APP_ROOT', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);

if(!function_exists('setenv')){
    /**
     * Sets an environment variable, optionally saving it to the `.env` file.
     *
     * @param string $key The environment variable key.
     * @param string $value The value to set for the environment variable.
     * @param bool $updateToEnv Whether to store/update the variable in the `.env` file (default: false).
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
    function setenv(string $key, string $value, bool $updateToEnv = false): bool
    {
        $key = trim($key);

        if ($key === '') {
            return false;
        }

        $value = trim($value);
        $isComment = str_starts_with($key, ';');

        if($isComment){
            unset($_ENV[$key], $_SERVER[$key]);
        }else{
            if (!getenv($key, true)) {
                putenv("{$key}={$value}");
            }

            $_ENV[$key] = $_SERVER[$key] = $value;
        }

        if (!$updateToEnv) {
            return true;
        }
        $path = APP_ROOT . '.env';

        try {
            $file = new SplFileObject($path, 'a+');
            $file->seek(0);

            $lines = '';
            $found = false;
            $pattern = '/^[;]*\s*' . preg_quote($isComment ? trim($key, "; \t") : $key, '/') . '\s*=\s*(.*)$/mi';

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

            return (new SplFileObject($path, 'w'))->fwrite($lines) !== false;
        } catch (Throwable) {
            return false;
        }
    }
}

if(!function_exists('env')){
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

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: '__ENV_KEY_EMPTY__';

        if ($value === '__ENV_KEY_EMPTY__') {
            return $default;
        }

        if (!$value || $value === true || !is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if (is_numeric($value)) {
            return $_ENV[$key] = $_SERVER[$key] = $value + 0;
        }

        if($value === '[]'){
            return $_ENV[$key] = $_SERVER[$key] = [];
        }

        $normalized = match (strtolower($value)) {
            'true', 'enable'   => true,
            'false', 'disable' => false,
            'null'             => null,
            'blank'            => '',
            default            => '__ENV_CONTINUE_SEARCH__'
        };

        if($normalized === null){
            return $normalized;
        }

        if ($normalized !== '__ENV_CONTINUE_SEARCH__') {
            return $_ENV[$key] = $_SERVER[$key] = $normalized;
        }

        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $items = array_map(function($item) {
                $item = trim($item, " '\"\n\r\t\v\0");
                return match ($item) {
                    'true'  => true,
                    'false' => false,
                    'null'  => null,
                    is_numeric($item) => $item + 0,
                    default => $item
                };
            }, explode(',', trim($value, '[] ')));

            return $_ENV[$key] = $_SERVER[$key] = $items ?: [];
        }

        return $value;
    }
}

/**
 * Register environment variables from a .env file.
 *
 * @param string $path The path to the .env file.
 * 
 * @return void
 */
(function (string $path) {
    if (file_exists($path)) {
        try {
            $file = new SplFileObject($path, 'r');

            while (!$file->eof()) {
                $line = trim($file->fgets());

                if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                    continue;
                }

                [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
                setenv($key, $value);
            }
            return;
        } catch (Throwable) {}
    }

    exit(sprintf(
        "RuntimeError: Missing environment configuration.%sEnsure the required environment file (.env) exists in the project root.",
        (php_sapi_name() === 'cli') ? "\n\n" : '<br/><br/>'
    ));
})(APP_ROOT . '.env');

/**
 * Application public front controller directory.
 * 
 * @var string FRONT_CONTROLLER
 */
defined('FRONT_CONTROLLER') || define('FRONT_CONTROLLER', APP_ROOT . 'public' . DIRECTORY_SEPARATOR);

/**
 * Application document root directory. 
 * 
 * @var string DOCUMENT_ROOT
 */
defined('DOCUMENT_ROOT') || define('DOCUMENT_ROOT', realpath(FRONT_CONTROLLER . 'public') . DIRECTORY_SEPARATOR);

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
 * @var int STATUS_SILENT
 */
defined('STATUS_SILENT') || define('STATUS_SILENT', 2);

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
 * Application production mode boolean flag.
 * 
 * @var bool PRODUCTION
 */
defined('PRODUCTION') || define('PRODUCTION', ENVIRONMENT === 'production');

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
defined('URL_SCHEME') || define('URL_SCHEME', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'));

/**
 * Application environment hostname (e.g, example.com).
 * 
 * @var string APP_HOSTNAME
 */
defined('APP_HOSTNAME') || define('APP_HOSTNAME', env('app.hostname', 'example.com'));

/**
 * Application environment hostname alias (e.g, www.example.com).
 * 
 * @var string APP_WWW_HOSTNAME
 */
defined('APP_WWW_HOSTNAME') || define('APP_WWW_HOSTNAME', 'www.' . APP_HOSTNAME);

/**
 * Application environment URL (e.g, http://example.com).
 * 
 * @var string APP_URL
 */
defined('APP_URL') || define('APP_URL', URL_SCHEME . '://' . APP_HOSTNAME);

/**
 * Application environment URL alternative (e.g, http://www.example.com).
 * 
 * @var string APP_WWW_URL
 */
defined('APP_WWW_URL') || define('APP_WWW_URL', URL_SCHEME . '://' . APP_WWW_HOSTNAME);

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
 * Application project identifier string.
 * 
 * @var bool PROJECT_ID 
 * > This is based on directory your project is located as product id or empty on php server.
 */
defined('PROJECT_ID') || define('PROJECT_ID', trim(dirname($_SERVER['SCRIPT_NAME']??''), TRIM_DS));

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
 * Database fetch mode to return all result as an associative array.
 * 
 * @var int FETCH_ALL
 */
defined('FETCH_ALL') || define('FETCH_ALL', 6);

/**
 * Database fetch mode to return all columns as an associative array.
 * 
 * @var int FETCH_COLUMN_ASSOC
 */
defined('FETCH_COLUMN_ASSOC') || define('FETCH_COLUMN_ASSOC', 7);

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
 * Set error reporting.
 */
error_reporting(PRODUCTION ? 
    E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_NOTICE & ~E_USER_DEPRECATED :
    E_ALL
);
ini_set('display_errors', (!PRODUCTION && env('debug.display.errors', false)) ? '1' : '0');

/**
 * Set default timezone
 */
date_default_timezone_set(env('app.timezone', 'UTC'));

/**
 * Limits the maximum execution time
 */
set_time_limit((int) env('script.execution.limit', 30));

/**
 * Set whether a client disconnect should abort script execution
 */
ignore_user_abort((bool) env('script.ignore.abort', false));