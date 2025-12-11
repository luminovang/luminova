<?php 
declare(strict_types=1);
/**
 * Luminova Framework constants and initialization functions.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng/docs/0.0.0/global/constants
 */
use Luminova\Runtime;
use Luminova\Luminova;
use Luminova\Config\Env;

if (defined('APP_ROOT')) {
    return;
}

/**
 * Directory separators used when trimming directory paths.
 *
 * @var string
 */
defined('TRIM_DS') || define('TRIM_DS', '/\\');

/**
 * Absolute path to the application project root directory.
 *
 * @var string
 */
defined('APP_ROOT') || define('APP_ROOT', Luminova::appRoot());

/**
 * Absolute path to the application document root directory.
 *
 * This is the directory containing the public front controller.
 *
 * @var string
 *
 * @example `/path/to/project/public/`
 * @example `/usr/www/example.com/public/`
 */
defined('DOCUMENT_ROOT') || define('DOCUMENT_ROOT', APP_ROOT . 'public' . DIRECTORY_SEPARATOR);

/**
 * Status code indicating successful execution.
 *
 * @var int
 */
defined('STATUS_SUCCESS') || define('STATUS_SUCCESS', 0);

/**
 * Status code indicating failed execution.
 *
 * @var int
 */
defined('STATUS_ERROR') || define('STATUS_ERROR', 1);

/**
 * Status code indicating that execution completed without a success or error status.
 *
 * @var int
 */
defined('STATUS_SILENCE') || define('STATUS_SILENCE', 2);

/**
 * Application version.
 *
 * @var string
 */
defined('APP_VERSION') || define('APP_VERSION', Env::get('app.version', '1.0.0'));

/**
 * Version identifier for application asset files.
 *
 * This value can be used for cache busting when asset files are updated.
 *
 * @var string
 */
defined('APP_FILE_VERSION') || define('APP_FILE_VERSION', Env::get('app.file.version', '1.0.0'));

/**
 * Application name.
 *
 * @var string
 */
defined('APP_NAME') || define('APP_NAME', Env::get('app.name', 'Example'));

/**
 * Application environment name.
 *
 * @var string
 */
defined('ENVIRONMENT') || define('ENVIRONMENT', Env::get('app.environment.mood', 'development'));

/**
 * Indicates whether the application is running in staging mode.
 *
 * @var bool
 */
defined('STAGING') || define('STAGING', ENVIRONMENT === 'staging');

/**
 * Indicates whether the application is running in production mode.
 *
 * Staging is treated as production for features that should apply to
 * production-like environments.
 *
 * @var bool
 *
 * @example
 * ```php
 * if (PRODUCTION) {
 *     // Production or staging environment.
 * }
 * ```
 *
 * @see STAGING
 */
defined('PRODUCTION') || define('PRODUCTION', STAGING || ENVIRONMENT === 'production');

/**
 * Indicates whether application maintenance mode is enabled.
 *
 * @var bool
 */
defined('MAINTENANCE') || define('MAINTENANCE', (bool) Env::get('app.maintenance.mood', false));

/**
 * Protocol scheme used by the application URL.
 *
 * @var string
 */
defined('URL_SCHEME') || define(
    'URL_SCHEME',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'
);

/**
 * Application hostname.
 *
 * @var string
 *
 * @example `example.com`
 */
defined('APP_HOSTNAME') || define('APP_HOSTNAME', Env::get('app.hostname', 'example.com'));

/**
 * Application hostname alias.
 *
 * @var string
 *
 * @example `www.example.com`
 */
defined('APP_HOSTNAME_ALIAS') || define('APP_HOSTNAME_ALIAS', 'www.' . APP_HOSTNAME);

/**
 * Application base URL.
 *
 * @var string
 *
 * @example `https://example.com`
 */
defined('APP_URL') || define('APP_URL', URL_SCHEME . '://' . APP_HOSTNAME);

/**
 * Application base URL alias.
 *
 * @var string
 *
 * @example `https://www.example.com`
 */
defined('APP_URL_ALIAS') || define('APP_URL_ALIAS', URL_SCHEME . '://' . APP_HOSTNAME_ALIAS);

/**
 * Indicates whether debug backtrace output is enabled.
 *
 * @var bool
 */
defined('SHOW_DEBUG_BACKTRACE') 
    || define('SHOW_DEBUG_BACKTRACE', (bool) Env::get('debug.show.tracer', false));

/**
 * Fetch mode that returns each row as an associative array.
 *
 * @var int
 */
defined('FETCH_ASSOC') || define('FETCH_ASSOC', 0);

/**
 * Fetch mode that returns each row as a numerically indexed array.
 *
 * @var int
 */
defined('FETCH_NUM') || define('FETCH_NUM', 1);

/**
 * Fetch mode that returns each row with both numeric and associative keys.
 *
 * @var int
 */
defined('FETCH_BOTH') || define('FETCH_BOTH', 2);

/**
 * Fetch mode that returns each row as an object.
 *
 * @var int
 */
defined('FETCH_OBJ') || define('FETCH_OBJ', 3);

/**
 * Fetch mode that returns a single column as a numerically indexed array.
 *
 * @var int
 */
defined('FETCH_COLUMN') || define('FETCH_COLUMN', 4);

/**
 * Fetch mode that returns each row as an object with numeric property names.
 *
 * @var int
 *
 * @deprecated
 */
defined('FETCH_NUM_OBJ') || define('FETCH_NUM_OBJ', 5);

/**
 * Fetch mode that returns a two-column result as an associative array.
 *
 * The first column is used as the array key and the second column as its value.
 *
 * @var int
 */
defined('FETCH_KEY_PAIR') || define('FETCH_KEY_PAIR', 6);

/**
 * Fetch mode that maps result columns to properties of the requested class.
 *
 * @var int
 */
defined('FETCH_CLASS') || define('FETCH_CLASS', 7);

/**
 * Return mode that returns the next result record.
 *
 * @var int
 */
defined('RETURN_NEXT') || define('RETURN_NEXT', 0);

/**
 * Return mode that returns all rows as numerically indexed arrays.
 *
 * @var int
 */
defined('RETURN_2D_NUM') || define('RETURN_2D_NUM', 1);

/**
 * Return mode that returns the last inserted record ID.
 *
 * @var int
 */
defined('RETURN_ID') || define('RETURN_ID', 2);

/**
 * Return mode that returns an integer result.
 *
 * @var int
 */
defined('RETURN_INT') || define('RETURN_INT', 3);

/**
 * Return mode that returns the number of affected rows.
 *
 * @var int
 */
defined('RETURN_COUNT') || define('RETURN_COUNT', 4);

/**
 * Return mode that returns a single result column.
 *
 * @var int
 */
defined('RETURN_COLUMN') || define('RETURN_COLUMN', 5);

/**
 * Return mode that returns all query results.
 *
 * @var int
 */
defined('RETURN_ALL') || define('RETURN_ALL', 6);

/**
 * Return mode that returns the prepared statement object.
 *
 * @var int
 */
defined('RETURN_STMT') || define('RETURN_STMT', 7);

/**
 * Return mode that returns the MySQLi result object.
 *
 * @var int
 */
defined('RETURN_RESULT') || define('RETURN_RESULT', 8);

/**
 * Return mode that streams rows one at a time.
 *
 * This mode is intended for use with iterative processing such as a `while`
 * loop.
 *
 * @var int
 */
defined('RETURN_STREAM') || define('RETURN_STREAM', 9);

/**
 * Parameter type representing a NULL value.
 *
 * @var int
 */
defined('PARAM_NULL') || define('PARAM_NULL', 0);

/**
 * Parameter type representing an integer value.
 *
 * @var int
 */
defined('PARAM_INT') || define('PARAM_INT', 1);

/**
 * Parameter type representing a string value.
 *
 * @var int
 */
defined('PARAM_STR') || define('PARAM_STR', 2);

/**
 * Parameter type representing large object data.
 *
 * @var int
 */
defined('PARAM_LOB') || define('PARAM_LOB', 3);

/**
 * Parameter type representing a boolean value.
 *
 * @var int
 */
defined('PARAM_BOOL') || define('PARAM_BOOL', 5);

/**
 * Parameter type representing a floating-point value.
 *
 * This is an internal parameter type and is not a standard PDO parameter type.
 *
 * @var int
 */
defined('PARAM_FLOAT') || define('PARAM_FLOAT', 192);

/**
 * Indicates whether the application is running in a local environment.
 *
 * @var bool
 *
 * @deprecated Use {@see Runtime::isLocal()} instead.
 */
defined('IS_LOCAL') || define('IS_LOCAL', Runtime::isLocal());

/**
 * Indicates whether the application is running on localhost.
 *
 * @var bool
 *
 * @deprecated Use {@see Runtime::isLocalhost()} instead.
 */
defined('IS_LOCALHOST') || define('IS_LOCALHOST', Runtime::isLocalhost());

/**
 * Relative path to the application index controller file.
 *
 * The value is based on the base directory of the application and is empty
 * when running through the PHP development server.
 *
 * @var string
 *
 * @example `www/example.com/public/index.php`
 *
 * @deprecated Use {@see \Luminova\Luminova::entryScript()} instead.
 */
defined('APP_CONTROLLER_INDEX') || define(
    'APP_CONTROLLER_INDEX',
    trim(dirname($_SERVER['SCRIPT_NAME'] ?? DOCUMENT_ROOT . 'index.php'), TRIM_DS) . '/index.php'
);

/**
 * Alias for {@see APP_CONTROLLER_INDEX}.
 *
 * @var string
 *
 * @deprecated Use {@see APP_CONTROLLER_INDEX} instead.
 */
defined('CONTROLLER_SCRIPT_PATH') || define('CONTROLLER_SCRIPT_PATH', APP_CONTROLLER_INDEX);

/**
 * Absolute path to the Novakit development server executable.
 *
 * The value is `null` when the application is not running under Novakit.
 *
 * @var string|null
 *
 * @deprecated Use {@see Runtime::isContainer('novakit')} instead.
 */
defined('NOVAKIT_ENV') || define(
    'NOVAKIT_ENV',
    (($_SERVER['RUNTIME_ENV'] ?? null) === 'novakit') ? APP_ROOT . 'novakit' : null
);