<?php 
declare(strict_types=1);
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/

/**
 * @var string TRIM_DS pattern to trim directory separator.
*/
defined('TRIM_DS') || define('TRIM_DS', '/\\');

/**
 * @var string APP_ROOT system root 2 levels back.
*/
defined('APP_ROOT') || define('APP_ROOT', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);

if(!function_exists('setenv')){
    /**
     * Set an environment variable if it doesn't already exist.
     *
     * @param string $key The key of the environment variable.
     * @param string $value The value of the environment variable.
     * @param bool $append_to_env Weather to save or update the value in .env file (default: false).
     * 
     * @return bool Return true on success, otherwise false on failure.
     */
    function setenv(string $key, string $value, bool $append_to_env = false): bool
    {
        if ($key === '') {
            return false;
        }

        $key = trim($key);
        $value = trim($value);

        if (!getenv($key, true)) {
            putenv("{$key}={$value}");
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;

        if ($append_to_env) {
            $path = APP_ROOT . '.env';

            try {
                $file = new SplFileObject($path, 'a+');
                $content = '';
                $found = false;
                $pattern = '/^' . preg_quote($key, '/') . '\s*=/m';

                while (!$file->eof()) {
                    $line = $file->fgets();
                    
                    if (preg_match($pattern, $line)) {
                        $found = true;
                        $content .= "$key=$value\n";
                    }else{
                        $content .= $line;
                    }
                }

                if (!$found) {
                    $content .= "\n$key=$value";
                }

                (new SplFileObject($path, 'w'))->fwrite($content);

                return true;
            } catch (Throwable) {
                return false;
            }
        }

        return true;
    }
}

if(!function_exists('env')){
    /**
     * Get environment variable value from registered `ENV` variables.
     *
     * @param string $key The environment variable key to retrieve.
     * @param mixed $default Optional default value to return if the key is not found (default: null).
     * 
     * @return array|string|int|float|double|bool|null Return the value of the specified environment key or default value if not found.
     */
    function env(string $key, mixed $default = null): mixed 
    {
        if($key === ''){
            return '';
        }

        static $arrays = [];

        if (($value = $arrays[$key] ?? null) !== null) {
            return $value;
        }

        $value = $_ENV[$key] ?? ($_SERVER[$key] ?? getenv($key));

        // Value cannot be false it always stored as string or an array.
        if ($value === false) {
            return $default;
        }

        $result = match (strtolower($value)) {
            'true', 'enable' => true,
            'false', 'disable' => false,
            'blank' => '',
            'null' => null,
            default => ((str_starts_with($value, '[') && str_ends_with($value, ']')) 
                ? '__ENV_ARRAY__' 
                : (is_numeric($value) 
                    ? $value + 0 
                    : $value
                )
            )
        };

        if($result === '__ENV_ARRAY__'){
            if($value === '[]'){
                $arrays[$key] = [];
                return [];
            }

            $result = array_map(function($item) {
                $item = trim($item, " '\"\n\r\t\v\0");
                return match ($item) {
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    is_numeric($item) => $item + 0,
                    default => $item
                };
            }, explode(',', trim($value, '[] ')));
            
            $arrays[$key] = $result;
        }

        return $result;
    }
}

/**
 * Register environment variables from a .env file.
 *
 * @param string $path The path to the .env file.
 * 
 * @return void
 */
(function(string $path){
    if (file_exists($path)) {
        try{
            $file = new SplFileObject($path, 'r');
            while (!$file->eof()) {
                $line = trim($file->fgets());
                if (str_starts_with($line, '#') || str_starts_with($line, ';')) {
                    continue;
                }

                $parts = explode('=', $line, 2);
                if (isset($parts[1])) {
                    setenv($parts[0], $parts[1]);
                }
            }
            return;
        }catch(Throwable){}
        exit("Environment file not found on: {$path}, make sure you add .env file to your project root.");
    }
})(APP_ROOT . '.env');

/**
 * Define our public application front controller of not defined 
 * 
 * @var string FRONT_CONTROLLER
*/
defined('FRONT_CONTROLLER') || define('FRONT_CONTROLLER', APP_ROOT . 'public' . DIRECTORY_SEPARATOR);

/**
 * @var string DOCUMENT_ROOT document root directory path 
*/
defined('DOCUMENT_ROOT') || define('DOCUMENT_ROOT', realpath(FRONT_CONTROLLER . 'public') . DIRECTORY_SEPARATOR);

/**
 * @var int STATUS_OK success status code
*/
defined('STATUS_SUCCESS') || define('STATUS_SUCCESS', 0);

/**
 * @var int STATUS_ERROR error status code
*/
defined('STATUS_ERROR') || define('STATUS_ERROR', 1);

/**
 * @var int STATUS_SILENT Finish controller method without error or success status.
*/
defined('STATUS_SILENT') || define('STATUS_SILENT', 2);

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
defined('APP_NAME') || define('APP_NAME', env('app.name', 'Example'));

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
 * @var string URL_SCHEME get request url scheme http or https 
*/
defined('URL_SCHEME') || define('URL_SCHEME', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'));

/**
 * @var string APP_HOSTNAME get application hostname example.com
*/
defined('APP_HOSTNAME') || define('APP_HOSTNAME', env('app.hostname', 'example.com'));

/**
 * @var string APP_WWW_HOSTNAME get application url www.example.com
*/
defined('APP_WWW_HOSTNAME') || define('APP_WWW_HOSTNAME', 'www.' . APP_HOSTNAME);

/**
 * @var string APP_URL get application www hostname http://example.com
*/
defined('APP_URL') || define('APP_URL', URL_SCHEME . '://' . APP_HOSTNAME);

/**
 * @var string APP_WWW_URL get application www url https://www.example.com
*/
defined('APP_WWW_URL') || define('APP_WWW_URL', URL_SCHEME . '://' . APP_WWW_HOSTNAME);

/**
 * @var bool SHOW_DEBUG_BACKTRACE show debug tracer
*/
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', (bool) env('debug.show.tracer', false));

/**
 * @var bool NOVAKIT_ENV NovaKit executable command
*/
defined('NOVAKIT_ENV') || define('NOVAKIT_ENV', ($_SERVER['NOVAKIT_EXECUTION_ENV'] ?? null));

/**
 * @var bool PROJECT_ID Get the project ID, 
 * This is based on directory your project is located as product id or empty on php server.
*/
defined('PROJECT_ID') || define('PROJECT_ID', trim(dirname($_SERVER['SCRIPT_NAME']??''), TRIM_DS));

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

/**
 * @var int RETURN_STMT Return prepared statement.
*/
defined('RETURN_STMT') || define('RETURN_STMT', 7);

/**
 * Set error reporting.
*/
error_reporting(PRODUCTION ? 
    E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED :
    E_ALL
);
ini_set('display_errors', (!PRODUCTION && env('debug.display.errors', false)) ? '1' : '0');

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
ignore_user_abort((bool) env('script.ignore.abort', false));