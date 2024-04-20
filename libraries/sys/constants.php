<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
/**
 * @var string APP_ROOT system root 2 levels back
*/
defined('APP_ROOT') || define('APP_ROOT', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

/**
 * Define our public application front controller of not defined 
 * 
 * @var string FRONT_CONTROLLER
*/
defined('FRONT_CONTROLLER') || define('FRONT_CONTROLLER', realpath(rtrim(getcwd(), '\\/ ')) . DIRECTORY_SEPARATOR);

/**
 * @var string DOCUMENT_ROOT document root directory path 
*/
defined('DOCUMENT_ROOT') || define('DOCUMENT_ROOT', realpath(FRONT_CONTROLLER . 'public') . DIRECTORY_SEPARATOR);

if (!function_exists('root')) {
    /**
     * Return to the root directory of your project.
     *
     * @param string $directory The directory of the file you are calling root from.
     * @param string $suffix Prepend a path to root directory.
     * 
     * @return string $path + $suffix
     */
    function root(string $directory = __DIR__, string $suffix = ''): string
    {
        $suffix = trim($suffix, DIRECTORY_SEPARATOR) . ($suffix ? DIRECTORY_SEPARATOR : '');

        if (file_exists(APP_ROOT . '.env')) {
            return APP_ROOT . $suffix;
        }

        if (file_exists(DOCUMENT_ROOT . '.env')) {
            return DOCUMENT_ROOT . $suffix;
        }

        $path = realpath($directory);

        if ($path === false) {
            return $suffix; 
        }

        if (file_exists($path . DIRECTORY_SEPARATOR . '.env')) {
            return $path . DIRECTORY_SEPARATOR . $suffix;
        }

        while ($path !== DIRECTORY_SEPARATOR && !file_exists($path . DIRECTORY_SEPARATOR . '.env')) {
            $path = dirname($path);
        }

        return ($path !== DIRECTORY_SEPARATOR) ? $path . DIRECTORY_SEPARATOR . $suffix : $suffix;
    }
}

if(!function_exists('env')){
    /**
     * Get environment variables.
     *
     * @param string $key The key to retrieve.
     * @param mixed $default The default value to return if the key is not found.
     * 
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed 
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', 'enable' => true,
            'false', 'disable' => false,
            'blank', => '',
            'null' => null,
            default => $value
        };
    }
}

if(!function_exists('setenv')){
    /**
     * Set an environment variable if it doesn't already exist.
     *
     * @param string $key The key of the environment variable.
     * @param string $value The value of the environment variable.
     * @param bool $append_to_env Save or update to .env file 
     * 
     * @return bool true on success or false on failure.
     */
    function setenv(string $key, string $value, bool $append_to_env = false): bool
    {
        $count = 0;
        if (!getenv($key, true)) {
            putenv("{$key}={$value}");
            $count++;
        }
    
        if (empty($_ENV[$key])) {
            $_ENV[$key] = $value;
            $count++;
        }
    
        if (empty($_SERVER[$key])) {
            $_SERVER[$key] = $value;
            $count++;
        }
    
        if ($append_to_env) {
            $envFile = APP_ROOT . '.env';
            $envContents = file_get_contents($envFile);
            if($envContents === false){
                return false;
            }
            $keyExists = (strpos($envContents, "$key=") !== false || strpos($envContents, "$key =") !== false);
            //$keyValueExists = preg_match('/^' . preg_quote($key, '/') . '\s*=\s*.*$/m', $envContents);
           
            if (!$keyExists) {
                return file_put_contents($envFile, "\n$key=$value", FILE_APPEND) !== false;
            } else {
                $newContents = preg_replace_callback('/(' . preg_quote($key, '/') . ')\s*=\s*(.*)/',
                    function($match) use ($value) {
                        return $match[1] . '=' . $value;
                    },
                    $envContents
                );

                return file_put_contents($envFile, $newContents) !== false;
            }
        }

        return $count > 0;
    }
}

if (!function_exists('filter_paths')) {
    /**
     * Filter the path to match to allowed in error directories preview.
     *
     * @param string $path The path to be filtered.
     * 
     * @return string
    */
    function filter_paths(string $path): string 
    {
        $matching = '';
        $allowPreviews = ['system', 'app', 'resources', 'writeable', 'libraries', 'routes', 'bootstrap', 'builds'];
        foreach ($allowPreviews as $directory) {
            $separator = $directory . DIRECTORY_SEPARATOR; 
            if (strpos($path, $separator) !== false) {
                $matching = $separator;
                break;
            }
        }

        if ($matching !== '') {
            $filter = substr($path, strpos($path, $matching));

            return $filter;
        }

        return basename($path);
    }
}

/**
 * Initialize and load the environment variables
*/
\Luminova\Config\DotEnv::register(APP_ROOT . '.env');

/**
 * Set default timezone
*/
date_default_timezone_set(env("app.timezone", 'UTC'));

/**
 * Limits the maximum execution time
*/
set_time_limit((int) env("script.execution.limit", 30));

/**
 * Set whether a client disconnect should abort script execution
*/
ignore_user_abort((bool) env('script.ignore.abort', true));

/**
 * @var int STATUS_OK success status code
 * 
 * @deprecated use STATUS_SUCCESS instead
*/
defined('STATUS_OK') || define('STATUS_OK', 0);

/**
 * @var int STATUS_OK success status code
*/
defined('STATUS_SUCCESS') || define('STATUS_SUCCESS', 0);

/**
 * @var int STATUS_ERROR error status code
*/
defined('STATUS_ERROR') || define('STATUS_ERROR', 1);

/**
 * @var string APP_VERSION application version
*/
defined('APP_VERSION') || define('APP_VERSION', env('app.version', '1.0.0'));

/**
 * @var string APP_FILE_VERSION application version
*/
defined('APP_FILE_VERSION') || define('APP_FILE_VERSION', env('app.file.version', '1.0.0'));

/**
 * @var string APP_NAME application name
*/
defined('APP_NAME') || define('APP_NAME', env('app.name', ''));

/**
 * @var string ENVIRONMENT application development state
*/
defined('ENVIRONMENT') || define('ENVIRONMENT', env('app.environment.mood', 'development'));

/**
 * @var bool PRODUCTION check if on production
*/
defined('PRODUCTION') || define('PRODUCTION', ENVIRONMENT === 'production');

/**
 * @var bool MAINTENANCE check if on maintenance mode
*/
defined('MAINTENANCE') || define('MAINTENANCE', (bool) env('app.maintenance.mood', false));

/**
 * @var string URL_PROTOCOL get request url protocol http or https 
*/
defined('URL_PROTOCOL') || define('URL_PROTOCOL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://'));

/**
 * @var string SERVER_PROTOCOL get request server protocol HTTP/1.1
*/
defined('SERVER_PROTOCOL') || define('SERVER_PROTOCOL', (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'));

/**
 * @var string APP_HOSTNAME get application hostname example.com
*/
defined('APP_HOSTNAME') || define('APP_HOSTNAME', env('app.hostname', 'example.com'));

/**
 * @var string APP_URL get application www hostname http://example.com
*/
defined('APP_URL') || define('APP_URL', URL_PROTOCOL . APP_HOSTNAME);

/**
 * @var string APP_WWW_HOSTNAME get application url www.example.com
*/
defined('APP_WWW_HOSTNAME') || define('APP_WWW_HOSTNAME', 'www.' . APP_HOSTNAME);

/**
 * @var string APP_WWW_URL get application www url https://www.example.com
*/
defined('APP_WWW_URL') || define('APP_WWW_URL', URL_PROTOCOL . APP_WWW_HOSTNAME);

/**
 * @var string REQUEST_HOSTNAME get application current request hostname https://www.example.com
*/
defined('REQUEST_HOSTNAME') || define('REQUEST_HOSTNAME', URL_PROTOCOL . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : APP_HOSTNAME));

/**
 * @var string REQUEST_URL get application current request url https://www.example.com/path/?query=
*/
defined('REQUEST_URL') || define('REQUEST_URL', REQUEST_HOSTNAME . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));

/**
 * @var bool SHOW_DEBUG_BACKTRACE show debug tracer
*/
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', (bool) env('debug.show.tracer', false));

/**
 * @var bool NOVAKIT_ENV NovaKit executable command
*/
defined('NOVAKIT_ENV') || define('NOVAKIT_ENV', (isset($_SERVER['NOVAKIT_EXECUTION_ENV']) ? $_SERVER['NOVAKIT_EXECUTION_ENV'] : null));

/**
 * @var int FETCH_ASSOC Fetch as an associative array
*/
defined('FETCH_ASSOC') || define('FETCH_ASSOC', 0);

/**
 * @var int FETCH_NUM Fetch as an array integer index
*/
defined('FETCH_NUM') || define('FETCH_NUM', 1);

/**
 * @var int FETCH_BOTH Fetch as an array integer index and associative
*/
defined('FETCH_BOTH') || define('FETCH_BOTH', 2);

/**
 * @var int FETCH_OBJ Fetch as an object
*/
defined('FETCH_OBJ') || define('FETCH_OBJ', 3);

/**
 * @var int FETCH_COLUMN Fetch as an array columns integer index
*/
defined('FETCH_COLUMN') || define('FETCH_COLUMN', 4);

/**
 * @var int FETCH_NUM_OBJ Fetch as an object with string integer property names
*/
defined('FETCH_NUM_OBJ') || define('FETCH_NUM_OBJ', 5);

/**
 * @var int FETCH_ALL Fetch all as an associative array
*/
defined('FETCH_ALL') || define('FETCH_ALL', 6);

/**
 * @var int FETCH_COLUMN_ASSOC Fetch all as an associative array
*/
defined('FETCH_COLUMN_ASSOC') || define('FETCH_COLUMN_ASSOC', 7);

/**
 * @var int RETURN_NEXT Fetch next or single record.
*/
defined('RETURN_NEXT') || define('RETURN_NEXT', 0);

/**
 * @var int RETURN_2D_NUM Fetch all as 2d array integers
*/
defined('RETURN_2D_NUM') || define('RETURN_2D_NUM', 1);

/**
 * @var int RETURN_ID Fetch last inserted id
*/
defined('RETURN_ID') || define('RETURN_ID', 2);

/**
 * @var int RETURN_INT Fetch count of records
*/
defined('RETURN_INT') || define('RETURN_INT', 3);

/**
 * @var int RETURN_COUNT Fetch number if affected rows.
*/
defined('RETURN_COUNT') || define('RETURN_COUNT', 4);

/**
 * @var int RETURN_COLUMN Fetch all result columns
*/
defined('RETURN_COLUMN') || define('RETURN_COLUMN', 5);

/**
 * @var int RETURN_ALL Fetch all as results
*/
defined('RETURN_ALL') || define('RETURN_ALL', 6);