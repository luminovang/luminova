<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use \Luminova\Application\Factory;
use \Luminova\Application\Services;
use \Luminova\Http\Request;
use \Luminova\Http\UserAgent;
use \Luminova\Cookies\Cookie;
use \Luminova\Template\ViewResponse;
use \Luminova\Interface\ValidationInterface;
use \App\Controllers\Config\Files;
use \App\Controllers\Utils\Functions;
use \App\Controllers\Application;
use \Luminova\Template\Layout;
use \Luminova\Exceptions\FileException;

if (!function_exists('app')) {
    /**
     * Get application container class shared instance or new instance if not shared. 
     * 
     * @return class-Application<BaseApplication> Application shared instance.
    */
    function app(): Application 
    {
        static $app = null;

        if($app === null){
            $app = Application::getInstance();
        }

        return $app;
    }
}

if (!function_exists('request')) {
    /**
     * Get request object
     * 
     * @param bool $shared Return a shared instance (default: true).
     * 
     * @return class-object<Request>|null Return Request object
    */
    function request(bool $shared = true): ?Request 
    {
        return Factory::request($shared);
    }
}

if (!function_exists('start_url')) {
    /**
     * Get start url with port hostname suffix if available.
     * 
     * @param string $suffix Optional pass a suffix to the start url.
     * 
     * @example http://localhost:8080, http://localhost/public/
     * @example http://localhost/project-path/public/
     * 
     * @return string Return start url.
    */
    function start_url(?string $suffix = ''): string
    {
        $request = request();
        $start = $request->getScheme() . '://' . $request->getHostname();

        if(!PRODUCTION){
            $start .= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        }

        /*if(NOVAKIT_ENV === null && !PRODUCTION){
            //$start .= basename(root()) . '/public/';
            $start .= dirname($_SERVER['SCRIPT_NAME']);
        }*/

        return $start . '/' . ltrim($suffix, '/');
    }
}

if (!function_exists('func')) {
    /**
     * Return shared functions instance or a specific context instance.
     *
     * If context is specified, return an instance of the specified context, otherwise return a Functions instance or null.
     * Supported contexts: 
     *  -   ip, 
     *  -   document, 
     *  -   escape, 
     *  -   tor, 
     *  -   math.
     *
     * @param string|null $context The context to return instance for.
     * @param mixed ...$params Additional parameters based on context.
     *
     * @return class-object<Functions>|object|null|string|bool Returns an instance of Functions, 
     *    -   object string, or boolean value depending on the context.
     *
     * @throws Exception If an error occurs.
     * @throws RuntimeException If unable to initialize method.
     */
    function func(?string $context = null, mixed ...$params): mixed 
    {
        if ($context === null) {
            return Factory::functions();
        }

        $context = strtolower($context);

        if (in_array($context, ['ip', 'document', 'escape', 'tor', 'math'])) {
            return Factory::functions()->{$context}(...$params);
        }

        return null;
    }
}

if(!function_exists('kebab_case')){
   /**
	 * Convert a string to kebab case.
	 *
	 * @param string $input The input string to convert.
     * @param bool $lower Should convert to lower case (default: true).
	 * 
	 * @return string The kebab-cased string.
	*/
    function kebab_case(string $input, bool $lower = true): string 
    {
        $input = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $input);
        $input = trim(str_replace(' ', '-', $input), '-');

        if($lower){
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
    function locale(?string $locale = null): string|true 
    {
        if($locale === null){
            return env('app.locale', 'en');
        }

        setenv('app.locale', $locale, true);
        return true;
    }
}

if(!function_exists('escape')){
    /**
    * Escapes a string or array of strings based on the specified context.
    *
    * @param string|array $input The string or array of strings to be escaped.
    *   - @example @var array<string, string> - Use the key as the context.
    *   - @example @var array<int, string> Use the default context for all values.
    * @param string $context The context in which the escaping should be performed. Defaults to 'html'.
    *                        Possible values: 'html', 'js', 'css', 'url', 'attr', 'raw'.
    * @param string|null $encoding The character encoding to use. Defaults to null.
    * 
    * @return array|string The escaped string or array of strings.
    * @throws InvalidArgumentException When an invalid or blank encoding is provided.
    * @throws BadMethodCallException When an invalid context is called
    * @throws RuntimeException When the string is not valid UTF-8 or cannot be converted.
    */
    function escape(string|array $input, string $context = 'html', ?string $encoding = null): array|string 
    {
       return Functions::escape($input, $context, $encoding);
    }
}

if(!function_exists('strict')){
    /**
	 * Sanitize user input to protect against cross-site scripting attacks.
     * It removes unwanted characters from a given string and return only allowed characters.
	 *
	 * @param string $input The input string to be sanitized.
	 * @param string $type  The expected data type. 
     *      -   Filter Types: [int, digit, key, password, username, email, url, money, double, alphabet, phone, name, timezone, time, date, uuid, default]
	 * @param string $symbol The symbol to replace disallowed characters with (optional).
	 *
	 * @return string Return sanitized string.
	 */
    function strict(string $input, string $type = 'default', string $replacer = ''): string 
    {
       return Functions::strictInput($input, $type, $replacer);
    }
}

if(!function_exists('is_tor')){
    /**
    * Checks if the given IP address is a Tor exit node
    *
    * @param string|null $ip Ip address to check else use current ip address
    * 
    * @return bool Return true if ip address is a Tor exit node.
    */
    function is_tor(string|null $ip = null): bool
    {
        return Functions::ip()->isTor($ip);
    }
}

if(!function_exists('ip_address')){
    /**
    * Get user IP address or return ip address information
    *
    * @param bool $info If true return ip address information instead
    * @param array $options Pass additional options to return with IP information
    * 
    * @return string|object|null  Return ip info or ip address.
    */
    function ip_address(bool $info = false, array $options = []): string|object|null
    {
        if($info){
            return Functions::ip()->info(null, $options);
        }

       return Functions::ip()->get();
    }
}

if(!function_exists('is_empty')){
    /**
     * Check if values are empty.
     * This will treat 0 as none empty if you want any other thing use php empty function instead
     * 
     * @param mixed ...$values Arguments.
     * 
     * @return bool True if any of the values are empty, false otherwise.
    */
    function is_empty(mixed ...$values): bool 
    {
        foreach ($values as $value) {
            if (is_null($value) || (is_string($value) && trim($value) === '') || (is_numeric($value) && (int) $value !== 0 && empty($value)) || (is_object($value) && $value instanceof Countable && count($value) === 0)) {
                return true;
            }
        }
        return false;
    }
}

if(!function_exists('session')) {
    /**
     * Return session data if key is present else return session instance
     *
     * @param string $key Key to retrieve the data.
     * @param bool $shared Use shared instance (default: true).
     *
     * @return class-object<Session>|mixed Return session instance or value if key is present.
    */
    function session(?string $key = null, bool $shared = true): mixed
    {
        if (is_string($key) && $key !== '') {
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
     * @return class-object<Cookie> Return cookie instance.
    */
    function cookie(string $name, string $value = '', array $options = [], bool $shared = false): Cookie
    {
        return Factory::cookie($name, $value, $options, $shared);
    }
}

if(!function_exists('factory')) {
    /**
     * Returns a shared instance of a class in factory
     * Or factory instance if context is null
     *
     * Same as:
     * @example $config = factory('config')
     * @example $config = \Luminova\Application\Factory::config();
     * @example $config = new \Luminova\Config\Configuration();
     * 
     * @param string|null $context The factory name.
     * Factory Classes Alias: 
     * -   'task'      `Task`
     * -   'session'   `Session`
     * -   'cookie'    `Cookie`
     * -   'functions' `Functions`
     * -   'modules'   `Modules`
     * -   'language'  `Translator`
     * -   'logger'    `Logger`
     * -   'files'     `FileManager`
     * -   'validate'  `InputValidator`
     * -   'response'  `ViewResponse`
     * -   'request'   `Request`
     * -   'service'   `Services`
     * 
     * @param mixed ...$arguments The last bool argument indicate wether to return a shared instance.
     * @param bool $shared Allow shared instance creation (default: true).
     * 
     * @return class-object|class-object<Factory>|null Return instance of factory or instance of factory class, otherwise null.
    */
    function factory(string|null $context = null, mixed ...$arguments): ?object
    {
        if($context === null || $context === ''){
            return new Factory();
        }

        return Factory::$context(...$arguments);
    }
}

if(!function_exists('service')) {
    /**
     * Returns a shared instance of a class in services
     * Or service instance if context is null
     *
     * @example $config = service('Config')
     * @example $config = Services::Config();
     * 
     * Same as:
     * @example $config = new \Luminova\Config\Config();
     * 
     * @param class-string|string|null $service The service class name or alias.
     * @param mixed ...$arguments The last bool argument indicate wether to return a shared instance.
     * @param bool $serialize Allow object serialization (default: false).
     * @param bool $shared Allow shared instance creation (default: true).
     * 
     * @return class-object|class-object<Services>|null Return service class instance or instance of service class.
    */
    function service(?string $service = null, mixed ...$arguments): ?object
    {
        if($service === null || $service === ''){
            return Factory::service();
        }

        return Factory::service()->{$service}(...$arguments);
    }
}

if(!function_exists('remove_service')) {
    /**
     * Delete a service or clear all services
     * If NULL is passed all cached services instances will be cleared.
     * Else delete a specific services instance and clear it's cached instances
     * 
     * @param class-string|string $service The class name or alias, to delete and clear it cached
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
     * Tells what the user's browser is capable of
     * 
     * @param string|null $user_agent  The user agent string to analyze.
     * @param bool $return Set the return type, if `instance` return userAgent class object otherwise return array or json object.
     *      -   Return Types: [array, object, instance]
     * @param bool $shared Allow shared instance creation (default: true).
     * 
     * @return array<string,mixed>|object<string,mixed>|class-object<UserAgent>|false Return browser information.
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
     * Tells which operating system platform your application is running on
     * 
     * @param string $os Platform name 
     *      - [mac, windows, linux, freebsd, openbsd, solaris, aws, etc..]
     * 
     * @return bool Return true if the platform is matching, false otherwise.
    */
    function is_platform(string $os): bool
    { 
        $os = strtolower($os);

        return match($os) {
            'mac' => strpos(PHP_OS, 'Darwin') !== false,
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
     * @return string $text The processed text with HTML entities.
    */
    function text2html(?string $text): string
    { 
        if ($text === null ||  $text === '') {
            return '';
        }

        $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);

        return $text;
    }
}

if(!function_exists('nl2html')) {
    /**
     * Converts newline characters in a string to HTML entities. 
     * This is useful when you want to display text in an HTML textarea while preserving the original line breaks.
     * 
     * @param string $text A string containing the text to be processed.
     * 
     * @return string $text
    */
    function nl2html(?string $text): string
    { 
        if($text === null ||  $text === ''){
            return '';
        }

        $text = str_replace(["\n", "\r\n", '[br/]', '<br/>'], "&#13;&#10;", $text);
        $text = str_replace(["\t"], "&#09;", $text);

        return $text;
    }
}

if(!function_exists('import')) {
    /**
     * Import a custom library into your project 
     * You must place your external libraries in libraries/libs/ directory
     * 
     * @param string $library the name of the library
     * @example Foo/Bar/Baz
     * @example Foo/Bar/Baz.php
     * 
     * @return bool true if the library was successfully imported
     * @throws RuntimeException if library could not be found
    */
    function import(string $library): bool
    {
        return Factory::modules()->import($library);
    }
 }

 if(!function_exists('logger')) {
    /**
     * Log a message at the given level.
     *
     * @param string $level The log level.
     * - Log levels ['emergency, alert, critical, error, warning, notice, info, debug, exception, php_errors']
     * @param string $message The log message.
     * @param array $context Additional context data (optional).
     *
     * @return void
     * @throws InvalidArgumentException
    */
    function logger(string $level, string $message, array $context = []): void
    {
        Factory::logger()->log($level, $message, $context);
    }
 }

 if (!function_exists('lang')) {
    /**
     * Translate multiple languages it supports nested array
     *
     * @param string $lookup line to lookup.
     * @param string|null $default Fallback translation if not found.
     * @param string|null $locale The locale to use for translation (optional).
     * @param array $placeholders Matching placeholders for translation
     *    - @example array ['Peter', 'peter@foo.com] "Error name {0} and email {1}"
     *    - @example array ['name' => 'Peter', 'email' => 'peter@foo.com] "Error name {name} and email {email}"
     * 
     * 
     * @return string Return translated string.
     * @throws NotFoundException if translation is not found and default is not provided
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
     * - Context: [system, plugins, library, controllers, writeable, logs, caches, public, assets, views, routes, languages, services]
     * 
     * @return string Return directory path, windows, unix or windows style path. 
    */
    function path(string $file): string
    {
        return Factory::fileManager()->getCompatible($file);
    }
}

if (!function_exists('is_nested')) {
    /**
     * Check if array is a nested array
     * 
     * @param array $array Array to check.
     * 
     * @return bool Return true if array is a nested array
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
     * Check if array is associative
     * 
     * @param array $array Array to check
     * 
     * @return bool Return true if array is associative, false otherwise
    */
    function is_associative(array $array): bool 
    {
        if ($array === [] || isset($array[0])) {
            return false;
        }

        foreach ($array as $key => $value) {
            if (is_int($key)) return false;
        }
    
        return true;
    }
    
}

if (!function_exists('array_is_list')) {
    /**
     * Check if array is list
     * 
     * @param array $array Array to check
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
     * @return array $array Finalized array representation of the object
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
     * Convert an array or string list to json object
     *
     * @param array|string $input Array or String list to convert
     * 
     * @return object|false $object  Return JSON object, otherwise false.
    */
    function to_object(array|string $input): object|false
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
        }catch(\JsonException $e){
            return false;
        }

        return false;
    }
}

if (!function_exists('list_to_array')) {
    /**
     * Convert string list to array 
     * 
     * @example list_to_array('a,b,c') => ['a', 'b', 'c']
     * @example list_to_array('"a","b","c"') => ['a', 'b', 'c']
     * 
     * @param string $list string list
     * @return array|false Return array, otherwise false.
    */
    function list_to_array(string $list): array|false 
    {
        if ($list === '') {
            return false;
        }
    
        if (strpos($list, "'") !== false) {
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
     * Check if string list exist in array 
     * If any of the list doesn't exist in array it will return false
     * First it will have to convert the list to array using list_to_array()
     * 
     * @param string $list string list
     * @param array $array Array to map list to
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
     * Check if string is a valid list format
     * 
     * @param string $input string to check
     * @param bool $trim Trim whitespace around the values  
     * 
     * @return bool true or false on failure.
    */
    function is_list(string $input, bool $trim = false):bool 
    {

        if ($trim) {
            $input = preg_replace('/\s*,\s*/', ',', $input);
            $input = preg_replace_callback('/"([^"]+)"/', function($matches) {
                return '"' . trim($matches[1]) . '"';
            }, $input);
        }
    
        if ($input === '') {
            return false;
        }

        return preg_match('/^(\s*"?[^\s"]+"?\s*,)*\s*"?[^\s"]+"?\s*$/', $input);
    }
}

if (!function_exists('write_content')) {
    /**
     * Write, append contents to file.
     * 
     * @param string $filename — Path to the file where to write the data.
     * @param string|resource $content The contents to write to the file, either as a string or a stream resource.
     * @param int $flags [optional] The value of flags can be any combination of the following flags (with some restrictions), joined with the binary OR (|) operator.
     * @param resource $context [optional] A valid context resource created with stream_context_create.
     * 
     * @return bool Return true or false on failure.
     * @throws FileException If unable to write file.
    */
    function write_content(string $filename, mixed $content, int $flag = 0, $context = null): bool 
    {
        return Factory::fileManager()->write($filename, $content, $flag, $context);
    }
}

if (!function_exists('make_dir')) {
    /**
     * Attempts to create the directory specified by pathname if not exist.
     * 
     * @param string $path Directory path to create.
     * @param int $permissions Unix file permissions
     * @param bool $recursive Allows the creation of nested directories (default: true)
     * 
     * @return bool true if files existed or was created else false
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
     * Validate input fields or get validation instance 
     * Return true or false if input and rules are specified 
     * else return validation instance if NULL is passed on $inputs and $rules
     *
     * @param array $inputs Input fields to validate on 
     *      @example [$_POST, $_GET or $this->request->getBody()]
     * @param array $rules Validation filter rules to apply on each input field 
     *      @example ['email' => 'required|email|max|min|length']
     * @param array $messages Validation error messages to apply on each filter on input field
     *      @example [
     *          'email' => [
     *              'required' => 'email is required',
     *              'email' => 'Invalid [value] while validating [rule] on [field]'
     *          ]
     *        }
     * 
     * @return class-object<ValidationInterface> Return validation instance
    */
    function validate(?array $inputs, ?array $rules, array $messages = []): object 
    {
        if ($inputs === null || $rules === null) {
            return Factory::validate();
        }

        $instance = Factory::validate();
        $instance->setRules($rules, $messages);
        $instance->validate($inputs);
        
        return $instance;
    }
}

if (!function_exists('get_class_name')) {
    /**
     * Get class basename from namespace or object
     * 
     * @param string|class-object $from Class name or class object.
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
     * @return bool Return true if request is made in cli mode, false otherwise
    */
    function is_command(): bool
    {
        return defined('STDIN') ||
            (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0) ||
            php_sapi_name() === 'cli' || array_key_exists('SHELL', $_ENV) || !array_key_exists('REQUEST_METHOD', $_SERVER);
    }
}

if (!function_exists('is_dev_server')) {
    /**
     * Check if the application is running locally on development server
     *
     * @return bool Return true if is development server, false otherwise.
    */
    function is_dev_server(): bool
    {
        if(isset($_SERVER['NOVAKIT_EXECUTION_ENV'])){
            return true;
        }

        if($server = ($_SERVER['SERVER_NAME'] ?? false)){
            if ($server === '127.0.0.1' || $server === '::1' || $server === 'localhost') {
                return true;
            }
            
            if (strpos($server, 'localhost') !== false || strpos($server, '127.0.0.1') !== false) {
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
    * @param int $status int $status HTTP status code (default: 200 OK)
    * @param bool $encode Enable content encoding like gzip, deflate.
    *
    * @return class-object<ViewResponse> Return vew response object. 
    */
    function response(int $status = 200, bool $encode = true): ViewResponse
    {
        return Factory::response($status, true)->setStatus($status)->encode($encode);
    }
}

if (!function_exists('is_blob')) {
    /**
     * Find whether the type of a variable is blob
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
    
        if (isset($_SERVER['_']) && strpos($_SERVER['_'], 'php') !== false) {
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
     * @param mixed $result Response from the callback function (void|bool|null|int)
     * @param bool $return_int Return type (default: int)
     * 
     * @return int|bool Return boolean value.
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
     * @param string|null $view To view or file.
     * @param bool $absolute Should we use absolute url (default: false).
     * 
     * @return string Return hyperlink of view or base controller if blank string is passed.
    */
    function href(string|null $view = '', bool $absolute = false): string 
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
     * Create a link to assets folder file.
     * 
     * @param string|null $filename Filename or path.
     * @param bool $absolute Should we use absolute url (default: false).
     * 
     * @return string Return assets file or base asset folder if blank string is passed.
    */
    function asset(string|null $filename = '', bool $absolute = false): string 
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
     * @param string $input The string to convert
     * @return string The string converted to camel case
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
        $charset = $charset ?? env('app.charset', 'utf-8');
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
     * @example layout('foo') or layout('foo/bar/baz').
     * 
     * @return Layout Returns the layout class instance.
     * @throws RuntimeException Throws if layout file is not found.
     * 
     * > All layouts must be stored in `resources/views/layout/` directory.
    */
    function layout(string $file): Layout
    {
        return Layout::getInstance()->layout($file);
    }
}