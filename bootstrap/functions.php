<?php
declare(strict_types=1);
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @see https://luminova.ng/docs/0.0.0/global/functions
 */

use \App\Application;
use \App\Config\Files;
use \Luminova\Base\BaseFunction;
use \Luminova\Application\Foundation;
use \Luminova\Application\Factory;
use \Luminova\Application\Services;
use \Luminova\Functions\Escape;
use \Luminova\Http\Request;
use \Luminova\Http\UserAgent;
use \Luminova\Cookies\Cookie;
use \Luminova\Sessions\Session;
use \Luminova\Interface\ValidationInterface;
use \Luminova\Template\Response;
use \Luminova\Template\Layout;
use \Luminova\Exceptions\FileException;
use \Luminova\Exceptions\AppException;

if (!function_exists('root')) {
    /**
     * Find application root directory of your project.
     * This ensures that the return path is from root directory.
     *
     * @param string $suffix Optional path to prepend to the root directory.
     * 
     * @return string Return application document root, and optional appended suffix.
     * > The suffix must be a path not a filename if file name is passed, it return `/root/filename.foo/`.
     */
    function root(?string $suffix = null): string
    {
       $suffix = ($suffix === null ? '' : trim(str_replace('/', DIRECTORY_SEPARATOR, $suffix), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        if (file_exists(APP_ROOT . '.env')) {
            return APP_ROOT . $suffix;
        }

        $root = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR;
        if (file_exists($root . '.env')) {
            return $root . $suffix;
        }

        $root = realpath(__DIR__);
        if ($root === false) {
            return $suffix; 
        }

        while ($root !== DIRECTORY_SEPARATOR && !file_exists($root . DIRECTORY_SEPARATOR . '.env')) {
            $root = dirname($root);
        }

        return $root . ($root === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) . $suffix;
    }
}

if (!function_exists('filter_paths')) {
    /**
     * Filter the display path, to remove private directory paths before previewing to users.
     *
     * @param string $path The path to be filtered.
     * 
     * @return string Return the filtered path.
    */
    function filter_paths(string $path): string 
    {
        return Foundation::filterPath($path);
    }
}

if (!function_exists('app')) {
    /**
     * Get application container class shared instance or new instance if not shared. 
     * 
     * @return Application Return application shared instance.
    */
    function app(): Application 
    {
        static $app = null;
        $app ??= Application::getInstance();

        return $app;
    }
}

if (!function_exists('request')) {
    /**
     * Get HTTP request object.
     * 
     * @param bool $shared Return a shared instance (default: true).
     * 
     * @return Request|null Return http request object.
    */
    function request(bool $shared = true): ?Request 
    {
        return Factory::request($shared);
    }
}

if (!function_exists('start_url')) {
    /**
     * Get the start URL with an optional suffix and port hostname if available.
     * 
     * @param string $suffix Optional suffix to append to the start URL (default: null).
     * 
     * @return string Return the generated start URL of your project.
     * 
     * @example - If your application path is like: `/Some/Path/To/htdocs/my-project-path/public/` 
     * It returns depending on your development environment:
     * - http://localhost:8080
     * - http://localhost/my-project-path/public/
     * - http://localhost/public
     * - http://example.com:8080
     * - http://example.com/
    */
    function start_url(?string $suffix = null): string
    {
        $suffix = ($suffix=== null) ? '/' : '/' . ltrim($suffix, '/');

        if(PRODUCTION){
            return APP_URL . $suffix;
        }

        $hostname = $_SERVER['HTTP_HOST'] 
            ?? $_SERVER['HOST'] 
            ?? $_SERVER['SERVER_NAME'] 
            ?? $_SERVER['SERVER_ADDR'] 
            ?? '';

        return URL_SCHEME . '://' . $hostname . '/' . PROJECT_ID . $suffix;
    }
}

if (!function_exists('absolute_url')) {
    /**
     * Convert an application-relative path to an absolute URL.
     * 
     * @param string $path The relative path to convert to an absolute URL.
     * 
     * @return string Return the absolute URL of the specified path.
     * 
     * @example - If path is: /Applications/XAMPP/htdocs/project-path/public/asset/files/foo.text.
     * It returns: http://localhost/project-path/public/asset/files/foo.text.
    */
    function absolute_url(string $path): string
    {
        return Foundation::toAbsoluteUrl($path);
    }
}

if (!function_exists('func')) {
    /**
     * Return shared functions instance or a specific context instance.
     * If context is specified, return an instance of the specified context, 
     * otherwise return anonymous class which extends BaseFunction.
     *
     * @param string|null $context The context to return it's instance (default: null).
     * @param mixed $arguments [, mixed $... ] Optional initialization arguments based on context.
     *
     * @return anonymous-class-object<BaseFunction>|class-object<\T>|mixed Returns an instance of functions, 
     * object string, or boolean value depending on the context, otherwise null.
     * 
     *  Supported contexts:
     * 
     *  -   ip: - Return instance of 'Luminova\Functions\IP'.
     *  -   document:  Return instance of 'Luminova\Functions\IP'.
     *  -   tor:  Return instance of 'Luminova\Functions\Tor'.
     *  -   math:  Return instance of 'Luminova\Functions\Maths'.
     *
     * @throws AppException If an error occurs.
     * @throws RuntimeException If unable to call method.
     */
    function func(?string $context = null, mixed ...$arguments): mixed 
    {
        if ($context === null) {
            return Factory::functions();
        }

        if (in_array($context, ['ip', 'document', 'tor', 'math'], true)) {
            return Factory::functions()->{$context}(...$arguments);
        }

        return null;
    }
}

if(!function_exists('kebab_case')){
   /**
	 * Convert a string to kebab case.
	 *
	 * @param string $input The input string to convert.
     * @param bool $lower Weather to convert to lower case (default: true).
	 * 
	 * @return string Return the kebab-cased string.
	*/
    function kebab_case(string $input, bool $toLower = true): string 
    {
        $input = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $input);
        $input = trim(str_replace(' ', '-', $input), '-');

        if($toLower){
		    return strtolower($input);
        }

        return $input;
    }
}

if(!function_exists('locale')){
    /**
    * Set locale or return locale application string.
    *
    * @param string|null $locale If locale is present it will set it else return default locale
    *
    * @return string|true Return application locale or true if locale was set;
    */
    function locale(?string $locale = null): string|bool 
    {
        if($locale === null){
            return env('app.locale', 'en');
        }

        setenv('app.locale', $locale, true);
        return true;
    }
}

if(!function_exists('uuid')){
    /**
     * Generates a UUID string of the specified version such as `1, 2, 3, 4, or 5`.
     *
     * @param int $version The version of the UUID to generate (default: 4).
     * @param string|null $namespace The namespace for versions 3 and 5.
     * @param string|null $name The name for versions 3 and 5.
	 * 
     * @return string Return the generated UUID string.
     * @throws InvalidArgumentException If the namespace or name is not provided for versions 3 or 5.
     * 
     * To check if UUID is valid use `func()->isUuid(string, version)`
     */
    function uuid(int $version = 4, ?string $namespace = null, ?string $name = null): string 
    {
       return Factory::functions()->uuid($version, $namespace, $name);
    }
}

if(!function_exists('escape')){
    /**
    * Escapes a string or array of strings based on the specified context.
    * If input is null instance of escape class will be returned.
    *
    * @param string|array|null $input The string or array of strings to be escaped (default: null).
    *                           For array, you can optionally use the keys of the array to specify the escape context for each value.
    * @param string $context The escaper context in which the escaping should be performed (default:'html').
    * @param string|null $encoding The escape character encoding to use (default: 'utf-8').
    * 
    * @return array|string|Escape Return the escaped string or array of strings, otherwise return instance of escape if input is null.
    * @throws InvalidArgumentException When an invalid or blank encoding is provided.
    * @throws BadMethodCallException When an invalid context is called.
    * @throws RuntimeException When the string is not valid UTF-8 or cannot be converted.
    *
    * Supported Context Values: 
    *
    * - html - Escape general HTML content. 
    * - js -   Escape JavaScript code. 
    * - css -  Escape CSS styles. 
    * - url -  Escape URL, 
    * - attr - Escape HTML attributes.
    * - raw -  Raw output no escaping apply.
    */
    function escape(
        string|array|null $input = null, 
        string $context = 'html', 
        string|null $encoding = 'utf-8'
    ): array|string|Escape
    {
        if($input === null){
            return Factory::escaper(true, $encoding);
        }

        if ($context === 'raw') {
            return $input;
        }

        return Factory::functions()->escape($input, $context, $encoding);
    }
}

if(!function_exists('strict')){
    /**
	 * String input type, this function removes unwanted characters from a given string and return only allowed characters.
	 *
	 * @param string $input The input string to be sanitized.
	 * @param string $type  The expected input filter type.
     *      -   Filter Types: [int, digit, key, password, username, email, url, money, double, alphabet, phone, name, timezone, time, date, uuid, default]
	 * @param string $symbol The symbol to replace disallowed characters with (default: blank string).
	 *
	 * @return string Return sanitized string.
	 */
    function strict(string $input, string $type = 'default', string $replacer = ''): string 
    {
       return Factory::functions()->strictType($input, $type, $replacer);
    }
}

if(!function_exists('is_tor')){
    /**
    * Checks if the given IP address is a Tor exit node.
    *
    * @param string|null $ip The ip address to check, if NULL get current ip address.
    * @param int $expiration The expiration time to request for new exit nodes from tor api (default: 2592000 30 days).
    * 
    * @return bool Return true if ip address is a Tor exit node, otherwise false.
    * @throws FileException Throws if error occurs or unable to read or write to directory.
    */
    function is_tor(?string $ip = null, int $expiration = 2592000): bool
    {
        return Factory::functions()->ip()->isTor($ip, $expiration);
    }
}

if(!function_exists('ip_address')){
    /**
    * Get user IP address or return ip address information.
    *
    * @param bool $get_info Weather to true return ip address information instead (default: false).
    * @param array $options Optional data to return with IP information (default: none).
    * 
    * @return string|object|null Return client ip address or ip info, otherwise null if ip info not found.
    */
    function ip_address(bool $get_info = false, array $options = []): string|object|null
    {
        if($get_info){
            return Factory::functions()->ip()->info(null, $options);
        }

        return Factory::functions()->ip()->get();
    }
}

if(!function_exists('is_empty')){
    /**
     * Check if values are empty.
     * This will treat 0 as none empty if you want any other thing use php empty function instead.
     * 
     * @param mixed $values [, mixed $... ] Values to check if empty or not.
     * 
     * @return bool True if any of the values are empty, false otherwise.
    */
    function is_empty(mixed ...$values): bool 
    {
        foreach ($values as $value) {
            if (
                is_null($value) || 
                (is_string($value) && trim($value) === '') ||
                (is_numeric($value) && (int) $value !== 0 && empty($value)) || 
                (is_object($value) && $value instanceof Countable && count($value) === 0)
            ) {
                return true;
            }
        }
        return false;
    }
}

if(!function_exists('session')) {
    /**
     * Return session data if key is present else return session instance.
     *
     * @param string $key Key to retrieve the data.
     * @param bool $shared Use shared instance (default: true).
     *
     * @return Session|mixed Return session instance or value if key is present.
    */
    function session(?string $key = null, bool $shared = true): mixed
    {
        if ($key !== null && $key !== '') {
            return Factory::session($shared)->get($key);
        }

        return Factory::session($shared);
    }
}

if (!function_exists('cookie')) {
    /**
     * Create and return cookie instance.
     *
     * @param string $name Name of the cookie.
     * @param string $value Value of the cookie.
     * @param array  $options Options to be passed to the cookie.
     * @param bool $shared Use shared instance (default: false).
     * 
     * @return Cookie Return cookie instance.
    */
    function cookie(
        string $name, 
        string $value = '', 
        array $options = [], 
        bool $shared = false
    ): Cookie
    {
        return Factory::cookie($name, $value, $options, $shared);
    }
}

if(!function_exists('factory')) {
    /**
     * Returns a shared instance of a class in factory or factory instance if context is null.
     * 
     * @param string|null $context The factory context name. (default: null).
     * @param bool $shared Allow shared instance creation (default: true).
     * @param mixed $arguments [, mixed $... ] Optional class constructor initialization arguments.
     * 
     * * Factory Context Names: 
     * 
     * -   'task'           `\Luminova\Time\Task`
     * -   'session'        `\Luminova\Sessions\Session`
     * -   'cookie'         `\Luminova\Cookies\Cookie`
     * -   'functions'      `\Luminova\Base\BaseFunction`
     * -   'modules'        `\Luminova\Library\Modules`
     * -   'language'       `\Luminova\Languages\Translator`
     * -   'logger'         `\Luminova\Logger\Logger`
     * -   'fileManager'    `\Luminova\Storages\FileManager`
     * -   'validate'       `\Luminova\Security\Validation`
     * -   'response'       `\Luminova\Template\Response`
     * -   'request'        `\Luminova\Http\Request`
     * -   'service'        `\Luminova\Application\Services`
     * -   'notification'   `\Luminova\Notifications\Firebase\Notification`,
     * -   'caller'         `\Luminova\Application\Caller`
     * 
     * @return class-object<\T>|Factory|null Return instance of factory or instance of factory class, otherwise null.
     * @throws AppException Throws an exception if factory context does not exist or error occurs.
     * @example - using factory to load class like: `$config = factory('config');`.
     * 
     * Is same as:
     * 
     * `$config = \Luminova\Application\Factory::config();`
     * `$config = new \Luminova\Config\Configuration();`
    */
    function factory(?string $context = null, bool $shared = true, mixed ...$arguments): ?object
    {
        if($context === null || $context === ''){
            return new Factory();
        }

        $arguments[] = $shared;

        return Factory::$context(...$arguments);
    }
}

if(!function_exists('service')) {
    /**
     * Returns a shared instance of a class in services or service instance if context is null.
     * 
     * @param class-string<\T>|string|null $service The service class name or alias.
     * @param bool $shared Allow shared instance creation (default: true).
     * @param bool $serialize Allow object serialization (default: false).
     * @param mixed $arguments [, mixed $... ] Service initialization arguments.
     * 
     * @return class-object<\T>|Services|null Return service class instance or instance of service class.
     * @throws AppException Throws an exception if service does not exist or error occurs.
     * 
     * @example - Get config `$config = service('Config');`.
     * @example - Also get config `$config = Services::Config();`
     * 
     * Both are Same as:
     * ```
     * $config = new \Foo\Bar\Config();
     * ```
    */
    function service(
        ?string $service = null, 
        bool $shared = true, 
        bool $serialize = false, 
        mixed ...$arguments
    ): ?object
    {
        if($service === null || $service === ''){
            return Factory::service();
        }

        $arguments[] = $serialize;
        $arguments[] = $shared;

        return Factory::service()->{$service}(...$arguments);
    }
}

if(!function_exists('remove_service')) {
    /**
     * Delete a service or clear all services
     * If NULL is passed all cached services instances will be cleared.
     * Else delete a specific services instance and clear it's cached instances.
     * 
     * @param class-string<\T>|string $service The class name or alias, to delete and clear it cached.
     * 
     * @return bool Return true if the service was removed or cleared, false otherwise.
    */
    function remove_service(?string $service = null): bool
    {
        if($service === null){
            return Factory::service()->clear();
        }

        return Factory::service()->delete($service);
    }
}

if(!function_exists('browser')) {
    /**
     * Tells what the user's browser is capable of.
     * 
     * @param string|null $user_agent  The user agent string to analyze.
     * @param bool $return Set the return type, if `instance` return userAgent class object otherwise return array or json object.
     * @param bool $shared Allow shared instance creation (default: true).
     * 
     * @return array<string,mixed>|object<string,mixed>|UserAgent|false Return browser information.
     * 
     * Return Types: 
     * 
     * - array: - Return browser information as array.
     * - object: - Return browser information as object.
     * - instance: - Return browser information instance.
    */
    function browser(?string $user_agent = null, string $return = 'object', bool $shared = true): mixed
    { 
        if($return === 'instance'){
            return Factory::request($shared)->getUserAgent($user_agent);
        }

        $return = ($return === 'array');

        if (ini_get('browscap')) {
            $browser = get_browser($user_agent, $return);
            
            if ($browser !== false) {
                return $browser;
            }
        }

        return Factory::request($shared)->getUserAgent()->parse($user_agent, $return);
    }
}

if(!function_exists('is_platform')) {
    /**
     * Tells which operating system platform your application is running on.
     * 
     * @param string $os The platform name to check.
     * 
     * @return bool Return true if the platform is matching, false otherwise.
     * 
     * Predefine OS Values:
     * 
     * - mac - For macOS.
     * - windows - For Windows os.
     * - linux - For linux os.
     * - freebsd - For FreeBSD os.
     * - openbsd - For openbsd os.
     * - solaris - For solaris os.
     * - aws - For AWS OpsWorks.
     * - etc.
    */
    function is_platform(string $os): bool
    { 
        $os = strtolower($os);

        return match($os) {
            'mac' => str_contains(PHP_OS, 'Darwin'),
            'windows' => strtoupper(substr(PHP_OS, 0, 3)) === 'WIN',
            'freebsd' => strtoupper(PHP_OS) === 'FREEBSD',
            'openbsd' => strtoupper(PHP_OS) === 'OPENBSD',
            'solaris' => strtoupper(PHP_OS) === 'SOLARIS',
            'linux' => strtoupper(PHP_OS) === 'LINUX',
            'aws' => isset($_ENV['AWS_EXECUTION_ENV']),
            default => stripos(php_uname('s'), $os) !== false
        };
    }
}

if (!function_exists('text2html')) {
    /**
     * Converts text characters in a string to HTML entities. 
     * 
     * @param string $text A string containing the text to be processed.
     * 
     * @return string Return the processed text with HTML entities.
    */
    function text2html(?string $text): string
    { 
        if ($text === null ||  $text === '') {
            return '';
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
    }
}

if(!function_exists('nl2html')) {
    /**
     * Converts newline characters in a string to HTML entities. 
     * This is useful when you want to display text in an HTML textarea while preserving the original line breaks.
     * 
     * @param string|null $text A string containing the text to be processed.
     * 
     * @return string Return formatted string.
    */
    function nl2html(string|null $text): string
    {
        if($text === null ||  $text === ''){
            return '';
        }

        return str_replace(
            ["\n", "\r\n", '[br/]', '<br/>', "\t"], 
            ["&#13;&#10;", "&#13;&#10;", "&#13;&#10;", "&#13;&#10;", "&#09;"], 
            $text
        );
    }
}

if(!function_exists('import')) {
    /**
     * Import a custom library into your project 
     * You must place your external libraries in libraries/libs/ directory.
     * 
     * @param string $library the name of the library.
     * @example Foo/Bar/Baz
     * @example Foo/Bar/Baz.php
     * 
     * @return bool true if the library was successfully imported.
     * @throws RuntimeException if library could not be found.
    */
    function import(string $library): bool
    {
        require_once root('/libraries/libs/') . trim(rtrim($library, '.php'), DIRECTORY_SEPARATOR) . '.php';
        return true;
    }
 }

 if(!function_exists('logger')) {
    /**
     * Log a message at the given level.
     * This function will make use of your preferred psr logger class if available.
     *
     * @param string $level The log level to use.
     * @param string $message The message to log.
     * @param array $context Additional context data (optional).
     * 
     * Log Levels:
     * 
     * - emergency - Log emergency error that need attention.
     * - alert - Log alert message. 
     * - critical - Log critical issue that may cause app not to work properly. 
     * - error - Log minor error.
     * - warning - Log a warning message.
     * - notice - Log a notice to attend later.
     * - info - Log an information.
     * - debug - Log for debugging purpose.
     * - exception - Log an exception message.
     * - php_errors - Log any php related error.
     *
     * @return void
     * @throws \Luminova\Exceptions\InvalidArgumentException Throws if error occurs while login.
    */
    function logger(string $level, string $message, array $context = []): void
    {
        Factory::logger()->log($level, $message, $context);
    }
 }

 if (!function_exists('lang')) {
    /**
     * Translate multiple languages it supports nested array.
     *
     * @param string $lookup The language context annotation line to lookup.
     * @param string|null $default Optional fallback translation if not found.
     * @param string|null $locale The locale to use for translation (optional).
     * @param array $placeholders Optional matching placeholders for translation.
     *    - @example - Using index `['Peter', 'peter@foo.com] // "Error name {0} and email {1}"`.
     *    - @example Using keys `['name' => 'Peter', 'email' => 'peter@foo.com]` // "Error name {name} and email {email}"`.
     * 
     * 
     * @return string Return translated string.
     * @throws NotFoundException if translation is not found and default is not provided.
    */
    function lang(
        string $lookup, 
        ?string $default = null, 
        ?string $locale = null,
        array $placeholders = []
    ): string
    {
        $default ??= '';
        $instance = Factory::language();

        $defaultLocal = $instance->getLocale();

        if ($locale && $locale !== $defaultLocal) {
            $instance->setLocale($locale);
        }

        $translation = $instance->get($lookup, $default, $placeholders);

        if ($locale && $locale !== $defaultLocal) {
            $instance->setLocale($defaultLocal);
        }

        return $translation;
    }
}

if (!function_exists('path')) {
    /**
     * Get system or application path, converted to `unix` or `windows` directory separator style.
     * 
     * @param string $file Path file name to return.
     * 
     * Storage Context Names.
     *      - app.
     *      - system.
     *      - plugins.
     *      - library.
     *      - controllers.
     *      - writeable. 
     *      - logs.
     *      - caches.
     *      - public.
     *      - assets.
     *      - views.
     *      - routes.
     *      - languages.
     *      - services
     * 
     * @return string Return directory path, windows, unix or windows style path. 
    */
    function path(string $name): string
    {
        return Factory::fileManager()->getCompatible($name);
    }
}

if (!function_exists('get_column')) {
    /**
     * Return the values from a single column in the input array or an object.
     * 
     * @param array|object $from Array or an object to extract column values from.
     * @param null|string|int $property The column property key to extract.
     * @param string|int|null $index An optional column to use as the index/keys for the returned array.
     * 
     * @return array Returns an array of values representing a single column from the input array or object.
    */
    function get_column(array|object $from, null|string|int $property, null|string|int $index = null): array 
    {
        if (is_array($from)) {
            return array_column($from, $property, $index);
        }

        $from = (array) $from;

        if ($index !== null) {
            $columns = [];
            foreach ($from as $item) {
                if (is_object($item)) {
                    $key = $item->{$index};
                    $value = ($property === null) ? $item : $item->{$property};
                } else {
                    $key = $item[$index];
                    $value = ($property === null) ? $item : $item[$property];
                }
                $columns[$key] = $value;
            }
            return $columns;
        }

        return array_map(function($item) use ($property) {
            return is_object($item) ? $item->{$property} : $item[$property];
        }, $from);
    }
}

if (!function_exists('is_nested')) {
    /**
     * Check if array is a nested array.
     * 
     * @param array $array Array to check.
     * 
     * @return bool Return true if array is a nested array.
    */
    function is_nested(array $array): bool 
    {
        if ($array === []) {
            return false;
        }

        foreach ($array as $value) {
            if (is_array($value)) return true;
        }

        return false; 
    }
}

if (!function_exists('is_associative')) {
    /**
     * Check if array is associative.
     * 
     * @param array $array Array to check.
     * 
     * @return bool Return true if array is associative, false otherwise.
    */
    function is_associative(array $array): bool 
    {
        if ($array === [] || isset($array[0])) {
            return false;
        }

        foreach (array_keys($array) as $key) {
            if (is_int($key)) return false;
        }
    
        return true;
    }
}

if (!function_exists('is_json')) {
    /**
     * Check if the input is a valid JSON object.
     *
     * @param mixed $input The input to check.
     *
     * @return bool Returns true if the input is valid JSON; false otherwise.
     */
    function is_json(mixed $input): bool
    {
        if (!is_string($input)) {
            return false;
        }

        try{
            return is_array(json_decode($input, true, 512, JSON_THROW_ON_ERROR));
        }catch(JsonException){
            return false;
        }
    }
}

if (!function_exists('array_is_list')) {
    /**
     * Check if array is list.
     * 
     * @param array $array Array to check.
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

if (!function_exists('to_array')) {
    /**
     * Convert an object to an array.
     *
     * @param mixed $input The object to convert to an array.
     * 
     * @return array $array Finalized array representation of the object.
    */
    function to_array(mixed $input): array 
    {
        if (!is_object($input)) {
            return (array) $input;
        }
    
        $array = [];
        foreach ($input as $key => $value) {
            $array[$key] = is_object($value) ? to_array($value) : $value;
        }

        return $array;
    }
}

if (!function_exists('to_object')) {
    /**
     * Convert an array or string list to json object.
     *
     * @param array|string $input Array or String list to convert.
     * 
     * @return object|false $object Return JSON object, otherwise false.
    */
    function to_object(array|string $input): object|bool
    {
        if ($input === [] || $input === '') {
            return (object)[];
        }

        if (is_string($input)) {
            $input = list_to_array($input);
            if(!is_array($input)){
                return false;
            }
        }
    
        try{
            return json_decode(json_encode($input, JSON_THROW_ON_ERROR));
        }catch(JsonException){
            return false;
        }

        return false;
    }
}

if (!function_exists('list_to_array')) {
    /**
     * Convert string list to array.
     * 
     * @param string $list The string list.
     * 
     * @return array|false Return array, otherwise false.
     * 
     * @example list_to_array('a,b,c') => ['a', 'b', 'c'].
     * @example list_to_array('"a","b","c"') => ['a', 'b', 'c'].
    */
    function list_to_array(string $list): array|bool 
    {
        if ($list === '') {
            return false;
        }
    
        if (str_contains($list, "'")) {
            preg_match_all("/'([^']+)'/", $list, $matches);
            if (!empty($matches[1])) {
                return $matches[1];
            }
        }
    
        preg_match_all('/(\w+)/', $list, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
    
        return false;
    }
}

if (!function_exists('list_in_array')) {
   /**
     * Check if string list exist in array.
     * If any of the list doesn't exist in array it will return false
     * First it will have to convert the list to array using `list_to_array`.
     * 
     * @param string $list The string list to check.
     * @param array $array The array to check list in.
     * 
     * @return bool Return true exist, otherwise false.
    */
    function list_in_array(string $list, array $array = []): bool 
    {
        if($array === [] && $list === ''){
            return true;
        }

        if($array === [] || $list === ''){
            return false;
        }

        $map = list_to_array($list);

        if( $map === false){
            return false;
        }

        foreach ($map as $item) {
            if (!in_array($item, $array)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('is_list')) {
    /**
     * Check if string is a valid list format.
     * 
     * @param string $input string to check.
     * @param bool $trim Trim whitespace around the values.
     * 
     * @return bool Return true or false on failure.
    */
    function is_list(string $input, bool $trim = false): bool 
    {
        if ($trim) {
            $input = preg_replace('/\s*,\s*/', ',', $input);
            $input = preg_replace_callback('/"([^"]+)"/', fn($matches) => '"' . trim($matches[1]) . '"', $input);
        }
    
        if ($input === '') {
            return false;
        }

        return preg_match('/^(\s*"?[^\s"]+"?\s*,)*\s*"?[^\s"]+"?\s*$/', $input);
    }
}

if (!function_exists('write_content')) {
    /**
     * Write or append string contents or stream to file.
     * This function is an alternative for `file_put_contents`, it uses `SplFileObject` to write contents to file. 
     * 
     * @param string $filename Path to the file to write contents.
     * @param string|resource $content The contents to write to the file, either as a string or a stream resource.
     * @param int $flags [optional] The value of flags can be any combination of the following flags (with some restrictions), joined with the binary OR (|) operator.
     * @param resource $context [optional] A valid context resource created with stream_context_create.
     * 
     * @return bool Return true if successful, otherwise false on failure.
     * @throws FileException If unable to write file.
    */
    function write_content(string $filename, mixed $content, int $flag = 0, $context = null): bool 
    {
        return Factory::fileManager()->write($filename, $content, $flag, $context);
    }
}

if (!function_exists('get_content')) {
    /**
     * Reads the content of a file with options for specifying the length of data to read and the starting offset.
     * This function is an alternative for `file_get_contents`, it uses `SplFileObject` to open the file and read its contents. 
     * It can handle reading a specific number of bytes from a given offset in the file.
     * 
     * @param string $filename The path to the file to be read.
     * @param int $length The maximum number of bytes to read, if set to `0`, it read 8192 bytes at a time (default: 0).
     * @param int $offset The starting position in the file to begin reading from (default: 0).
     * @param bool $useInclude If `true`, the file will be searched in the include path (default: false). 
     * @param resource|null $context A context resource created with `stream_context_create()` (default: null).
     * 
     * @return string|false Returns the contents of the file as a string, or `false` on failure.
     * 
     * @throws FileException If an error occurs while opening or reading the file.
     */
    function get_content(
        string $filename, 
        int $length = 0, 
        int $offset = 0, 
        bool $useInclude = false, 
        $context = null
    ): string|bool 
    {
        return Factory::fileManager()->getContent($filename, $length, $offset, $useInclude, $context);
    }
}

if (!function_exists('make_dir')) {
    /**
     * Attempts to create the directory specified by pathname if not exist.
     * 
     * @param string $path Directory path to create.
     * @param int $permissions Unix file permissions.
     * @param bool $recursive Allows the creation of nested directories (default: true)
     * 
     * @return bool Return true if files already existed or was created successfully, otherwise false.
     * @throws RuntimeException If path is not readable.
     * @throws FileException If unable to create directory
    */
    function make_dir(string $path, ?int $permissions = null, bool $recursive = true): bool 
    {
        return Factory::fileManager()->mkdir($path, ($permissions ?? Files::$dirPermissions ?? 0777), $recursive);
    }
}

if (!function_exists('validate')) {
    /**
     * Validate input fields or return validation instance.
     * If input and rules are specified, it will do the validation and return instance which you can then called method `$validation->isPassed()`
     * To check if passed or failed, or get the error information.
     *
     * @param array $inputs Input fields to validate on (e.g, `$_POST`, `$_GET` or `$this->request->getBody()`).
     * @param array $rules Validation filter rules to apply on each input field.
     * @param array $messages Validation error messages to apply on each filter on input field.
     *     
     * @example - Rules arguments example:
     * ```php
     * ['email' => 'required|email|max|min|length']
     * ```
     * 
     * @example - Message arguments example:
     * ```php
     * [
     *   'email' => [
     *        'required' => 'email is required',
     *        'email' => 'Invalid [value] while validating [rule] on [field]'
     *    ]
     *  ]
     * ```
     * 
     * @return ValidationInterface Return validation instance.
    */
    function validate(?array $inputs = null, ?array $rules = null, array $messages = []): ValidationInterface 
    {
        $instance = Factory::validate();

        if ($inputs !== null && $rules !== null) {
            $instance->setRules($rules, $messages);
            $instance->validate($inputs);
        }
        
        return $instance;
    }
}

if (!function_exists('get_class_name')) {
    /**
     * Get class basename from namespace or object.
     * 
     * @param string|class-object<\T> $from Class name or class object.
     * 
     * @return string Return the class basename.
    */
    function get_class_name(string|object $from): string 
    {
        if (is_string($from)) {
            if(($pos = strrpos($from, '\\')) !== false){
                return substr($from, $pos + 1);
            }

            return $from;
        }

        return get_class_name(get_class($from));
    }
}


if (!function_exists('is_command')) {
    /**
     * Find whether application is running in cli mode.
     *
     * @return bool Return true if request is made in cli mode, false otherwise.
    */
    function is_command(): bool
    {
        return Foundation::isCommand();
    }
}

if (!function_exists('is_dev_server')) {
    /**
     * Check if the application is running locally on development server.
     *
     * @return bool Return true if is development server, false otherwise.
    */
    function is_dev_server(): bool
    {
        if(isset($_SERVER['NOVAKIT_EXECUTION_ENV'])){
            return true;
        }

        if(($server = ($_SERVER['SERVER_NAME'] ?? false)) !== false){
            if ($server === '127.0.0.1' || $server === '::1' || $server === 'localhost') {
                return true;
            }
            
            if (str_contains($server, 'localhost') || str_contains($server, '127.0.0.1')) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('response')) {
    /** 
    * Initiate a view response object. 
    *
    * @param int $status int $status HTTP status code (default: 200 OK).
    * @param bool $encode Enable content encoding like gzip, deflate.
    *
    * @return Response Return vew response object. 
    */
    function response(int $status = 200, bool $encode = true): Response
    {
        return Factory::response($status, true)->setStatus($status)->encode($encode);
    }
}

if (!function_exists('is_blob')) {
    /**
     * Find whether the type of a variable is blob.
     *
     * @param mixed $value Value to check.
     * 
     * @return bool Return true if the value is a blob, false otherwise.
    */
    function is_blob(mixed $value): bool 
    {
        return Factory::fileManager()->isResource($value, 'stream');
    }
}

if (!function_exists('which_php')) {
    /**
     * Get the PHP script executable path.
     *
     * @return string|null Return PHP executable path or null.
    */
    function which_php(): ?string
    {
        if (defined('PHP_BINARY')) {
            return PHP_BINARY;
        }
    
        if (isset($_SERVER['_']) && str_contains($_SERVER['_'], 'php')) {
            return $_SERVER['_'];
        }
    
        return null;
    }
}

if (!function_exists('status_code')) {
    /**
     * Convert status to int, return run status based on result.
     * In CLI, 0 is considered success while 1 is failure.
     * In some occasions, void or null may be returned, treating it as success.
     * 
     * @param mixed $result The response from the callback function or method to check (e.g, `void`, `bool`, `null`, `int`).
     * @param bool $return_int Weather to return int or bool (default: int).
     * 
     * @return int|bool Return status response as boolean or integer value.
     */
    function status_code(mixed $result = null, bool $return_int = true): int|bool
    {
        if ($result === false || (is_int($result) && $result == 1)) {
            return $return_int ? 1 : false;
        }

        return $return_int ? 0 : true;
    }
}

if (!function_exists('is_utf8')) {
    /**
     * Checks if a given string is UTF-8 encoded.
     *
     * @param string $input The string to check for UTF-8 encoding.
     * 
     * @return bool Returns true if the string is UTF-8 encoded, false otherwise.
    */
    function is_utf8(string $input): bool 
    {
        return preg_match('//u', $input) === 1;
    }
}

if (!function_exists('has_uppercase')) {
    /**
     * Checks if a given string contains an uppercase letter.
     *
     * @param string $string The string to check uppercase.
     * 
     * @return bool Returns true if the string has uppercase, false otherwise.
    */
    function has_uppercase(string $string): bool 
    {
        for ($i = 0; $i < strlen($string); $i++) {
            if (ctype_upper($string[$i])) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('href')) {
    /**
     * Create a hyperlink to another view or file.
     * 
     * @param string|null $view The view path or file to create the link for (default: null).
     * @param bool $absolute Whether to use an absolute URL (default: false).
     * 
     * @return string Return the generated hyperlink.
     */
    function href(?string $view = null, bool $absolute = false): string 
    {
        $view = (($view === null) ? '' : ltrim($view, '/'));

        if($absolute){
            return APP_URL . '/' . $view;
        }

        static $relative = null;

        if($relative == null){
            $relative = app()->link();
        }

        return $relative . $view;
    }
}

if (!function_exists('asset')) {
    /**
     * Create a link to a file in the assets folder.
     * 
     * @param string|null $filename The filename or path within the assets folder (default: null).
     * @param bool $absolute Whether to use an absolute URL (default: false).
     * 
     * @return string Return the generated URL to the assets file or base assets folder if no filename is provided.
    */
    function asset(?string $filename = null, bool $absolute = false): string 
    {
        $filename = 'assets/' . (($filename === null) ? '' : ltrim($filename, '/'));

        if($absolute){
            return APP_URL . '/' . $filename;
        }

        return href($filename);
    }
}

if (!function_exists('camel_case')) {
    /**
     * Convert a string to camel case.
     *
     * @param string $input The string to convert.
     * 
     * @return string The string converted to camel case.
    */
    function camel_case(string $input): string
    {
        $input = str_replace(['-', ' '], '_', $input);
        $parts = explode('_', $input);

        $camelCase = '';
        $firstPart = true;
        foreach ($parts as $part) {
            $camelCase .= $firstPart ? $part : ucfirst($part);
            $firstPart = false;
        }
        
        return $camelCase;
    }    
}

if (!function_exists('string_length')) {
    /**
     * Calculate string length based on different charsets.
     *
     * @param string $content The content to calculate length for.
     * @param string|null $charset The character set of the content.
     * 
     * @return int The calculated Content-Length.
     */
    function string_length(string $content, ?string $charset = null): int 
    {
        $charset ??= env('app.charset', 'utf-8');
        switch (strtolower($charset)) {
            case 'utf-8':
            case 'utf8':
                return mb_strlen($content, '8bit');
            case 'iso-8859-1':
            case 'latin1':
                return strlen($content);
            case 'windows-1252':
                $content = mb_convert_encoding($content, 'ISO-8859-1', 'UTF-8');
                return strlen($content);
            default:
                return is_utf8($content) ? mb_strlen($content, '8bit') : strlen($content);
        }
    }
}

if (!function_exists('layout')) {
    /**
     * PHP Template layout helper class.
     * Allow you to extend and inherit a section of another template view file.
     * 
     * @param string $file Layout filename without the extension path.
     * 
     * @return Layout Returns the layout class instance.
     * @throws RuntimeException Throws if layout file is not found.
     * 
     * @example  - Usage examples:
     * ```
     * layout('foo')
     * ```
     *  or
     * ```
     * layout('foo/bar/baz')
     * ```
     * 
     * > All layouts must be stored in `resources/views/layout/` directory.
    */
    function layout(string $file): Layout
    {
        return Layout::getInstance()->layout($file);
    }
}

if (!function_exists('get_mime')) {
    /**
     * Detect MIME Content-type for a file.
     * 
     * @param string $filename Path to the file.
     * 
     * @return string|false Return the content type in MIME format, otherwise false.
    */
    function get_mime(string $filename): string|bool
    {
        $mime = mime_content_type($filename);
        
        if ($mime === false && ($finfo = finfo_open(FILEINFO_MIME_TYPE)) !== false) {
            $mime = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mime;
        }

        return $mime;
    }
}

if (!function_exists('shared')) {
    /**
     * Temporarily stores and retrieves values within the same scope.
     *
     * @param string $key The key to identify the value.
     * @param mixed $value The value to store (optional).
     * @param mixed $default The default value return if key not found (default: NULL).
     * 
     * @return mixed Returns the value associated with the key, or default value if the key does not exist.
     */
    function shared(string $key, mixed $value = null, mixed $default = null): mixed 
    {
        static $preference = [];

        if ($value !== null) {
            $preference[$key] = $value;
            return $value;
        }

        if(array_key_exists($key, $preference)){
            return $preference[$key];
        }

        return $default;
    }
 }