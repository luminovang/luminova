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
use \Luminova\Http\Request;
use \Luminova\Cookies\Cookie;
use \Luminova\Functions\Functions;

if (!function_exists('func')) {
    /**
     * Return Functions instance or a specific context instance.
     *
     * If context is specified, return an instance of the specified context,
     * otherwise return a Functions instance or null.
     * Supported contexts: 'files', 'ip', 'document', 'escape', 'tor'.
     *
     * @param string|null $context The context to return instance for.
     * @param mixed ...$params Additional parameters based on context.
     *
     * @return Functions|object|null|string|bool Returns an instance of Functions, 
     *      object, string, or boolean value depending on the context.
     *
     * @throws Exception
     * @throws RuntimeException
     */
    function func(?string $context = null, ...$params): mixed 
    {
        if ($context === null) {
            return new Functions();
        }

        $context = strtolower($context);

        if (in_array($context, ['files', 'ip', 'document', 'escape', 'tor'], true)) {
            return Functions::{$context}(...$params);
        }

        return null;
    }
}

if(!function_exists('kebab_case')){
   /**
	 * Convert a string to kebab case.
	 *
	 * @param string $string The input string to convert.
	 * 
	 * @return string The kebab-cased string.
	 */
    function kebab_case(string $input): string 
    {
       return Functions::toKebabCase($input);
    }
}

if(!function_exists('locale')){
    /**
    * Set locale or return local 
    *
    * @param ?string $locale If locale is present it will set it else return default locale
    *
    * @return string|bool;
    */
    function locale(?string $locale = null): string|bool 
    {
        if($locale === null){
            $locale = env('app.locale', 'en');

            return $locale;
        }else{
            setenv('app.locale', $locale, true);
        }

        return true;
    }
}

if(!function_exists('is_feature')){
    /**
    * Check if feature is enabled in env file 
    *
    * @param string $key Feature key name
    * @param bool $default 
    *
    * @return bool
    */
    function is_feature(string $key, bool $default = false): bool 
    {
        return (bool) env($key, $default);
    }
}

if(!function_exists('escape')){
    /**
    * Escapes a string or array of strings based on the specified context.
    *
    * @param string|array $input The string or array of strings to be escaped.
    * @param string $context The context in which the escaping should be performed. Defaults to 'html'.
    *                        Possible values: 'html', 'js', 'css', 'url', 'attr', 'raw'.
    * @param string|null $encoding The character encoding to use. Defaults to null.
    * 
    * @return mixed The escaped string or array of strings.
    * @throws InvalidArgumentException When an invalid or blank encoding is provided.
    * @throws BadMethodCallException When an invalid context is called
    * @throws RuntimeException When the string is not valid UTF-8 or cannot be converted.
    */
    function escape(string|array $input, string $context = 'html', ?string $encoding = null): mixed 
    {
       return Functions::escape($input, $context, $encoding);
    }
}

if(!function_exists('is_tor')){
    /**
    * Checks if the given IP address is a Tor exit node
    *
    * @param string|null $ip Ip address to check else use current ip address
    * 
    * @return bool 
    */
    function is_tor(string|null $ip = null): bool 
    {
       if($ip === null){
            $ip = Functions::ip()->get();
       }

       return Functions::tor()->isTorExitNode($ip);
    }
}

if(!function_exists('ip_address')){
    /**
    * Get user IP address or return ip address information
    *
    * @param bool $ip_info If true return ip address information instead
    * @param array $options Pass additional options to return with IP information
    * 
    * @return string|object|null 
    */
    function ip_address(bool $ip_info = false, array $options = []): string|object|null
    {
        $ip = Functions::ip();

        if($ip_info){
            $info = $ip->getInfo(null, $options);

            return $info;
        }

       return $ip->get();
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
     * @param string $key Key to retrieve the data
     *
     * @return mixed|Session|Factory::session
     */
    function session(?string $key = null): mixed
    {
        $session = Factory::session();

        if (is_string($key) && $key !== '') {
            return $session->get($key);
        }

        return $session;
    }
}

if (!function_exists('cookie')) {
    /**
     * Create and return cookie instance.
     *
     * @param string $name Name of the cookie
     * @param string $value Value of the cookie
     * @param array  $options Options to be passed to the cookie
     * 
     * @return Cookie
     */
    function cookie(string $name, string $value = '', array $options = []): Cookie
    {
        return new Cookie($name, $value, $options);
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
     * @param string|null $context The class name to load
     * @param mixed ...$arguments The last bool argument indicate wether to return a shared instance
     * 
     * @return Factory|mixed
     */
    function factory(string|null $context = null, ...$arguments): mixed
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
     * Same as:
     * @example $config = service('config')
     * @example $config = \Luminova\Application\Services::config();
     * @example $config = new \Luminova\Config\Configuration();
     * 
     * @param string|null $service The service context name
     * @param mixed ...$arguments The last bool argument indicate wether to return a shared instance
     * 
     * @return Services|mixed
     */
    function service(string|null $service = null, ...$arguments): mixed
    {
        $instance = Factory::services();

        if($service === null || $service === ''){
            return $instance;
        }

        return $instance::$service(...$arguments);
    }
}

if(!function_exists('remove_service')) {
    /**
     * Delete a service or clear all services
     * If no service name was passed clear all cached instances of services classes.
     * Else delete a specific services instance and clear it's cached instances
     * 
     * @param string $service The class name to delete and clear it cached
     * 
     * @return bool 
     */
    function remove_service(?string $service = null): bool
    {
        $instance = Factory::services();

        if($service === null){
            return $instance::clear();
        }

        return $instance::delete($service);
    }
}

if(!function_exists('browser')) {
    /**
     * Tells what the user's browser is capable of
     * 
     * @param string|null $user_agent
     * @param bool $return_array If set to true, this function will return an array instead of an object.
     * 
     * @return array|object
     */
    function browser(?string $user_agent = null, bool $return_array = false): array|object 
    { 
        if (ini_get('browscap')) {
            $browser = get_browser($user_agent, $return_array);
            
            if ($browser !== false) {
                return $browser;
            }
        }

        $browser =  Request::parseUserAgent($user_agent, $return_array);

        return $browser;
    }
}

if(!function_exists('is_platform')) {
    /**
     * Tells which platform your application is running on
     * 
     * @param string $os Platform name 
     *      - [mac, windows, linux, freebsd, openbsd, solaris, aws, etc..]
     * 
     * @return bool
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
        $instance = Factory::import();
        $import = $instance::import($library);
 
        return $import;
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
        $logger = Factory::logger();
        $logger->log($level, $message, $context);
    }
 }

 if (!function_exists('lang')) {
    /**
     * Translate multiple languages it supports nested array
     *
     * @param string $lookup line to lookup
     * @param string|null $default Fallback translation if not found
     * @param string|null $locale
     * @param array $placeholders Matching placeholders for translation
     *    - @example array ['Peter', 'peter@foo.com] "Error name {0} and email {1}"
     *    - @example array ['name' => 'Peter', 'email' => 'peter@foo.com] "Error name {name} and email {email}"
     * 
     * 
     * @return string $translation
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
        $language = Factory::language();

        $defaultLocal = $language->getLocale();

        if ($locale && $locale !== $defaultLocal) {
            $language->setLocale($locale);
        }

        $translation = $language->get($lookup, $default, $placeholders);

        if ($locale && $locale !== $defaultLocal) {
            $language->setLocale($defaultLocal);
        }

        return $translation;
    }
}

if (!function_exists('path')) {
    /**
    * Get directory if context name is null Paths instance will be returned
    * 
    * @param string|null $context Path context name to return [system, plugins, library, controllers, writeable, logs, caches,
    *          public, assets, views, routes, languages, services]
    * 
    * @return string|Paths|Factory::paths 
   */
   function path(null|string $context = null): string|object
   {
        $path = Factory::paths();
        if ($context === null) {
            return $path;
        }
        
        return $path->{$context} ?? ''; 
   }
}

if (!function_exists('is_nested')) {
     /**
     * Check if array is a nested array
     * 
     * @param array $array
     * 
     * @return bool 
    */
    function is_nested(array $array): bool 
    {
        if ($array === []) {
            return false;
        }

        foreach ($array as $value) {
            if (is_array($value)) {
                return true; 
            }
        }
        return false; 
    }

}

if (!function_exists('is_associative')) {
    /**
     * Check if array is associative
     * 
     * @param array $array
     * 
     * @return bool 
    */
    function is_associative(array $array): bool 
    {
        if ($array === []) {
            return false;
        }

        return !is_int(key($array));
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
     * @return object $object
     */
    function to_object(array|string $input): object 
    {
        if ($input === [] || $input === '') {
            return (object) [];
        }

        if (is_string($input)) {
            $input = list_to_array($input);
        }
    
        $object = json_decode(json_encode($input));

        return $object;
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
     * @return array|bool $matches
    */
    function list_to_array(string $list): array|bool 
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
     * @return bool exist or not
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

        $pattern = '/^(\s*"?[^\s"]+"?\s*,)*\s*"?[^\s"]+"?\s*$/';

        return preg_match($pattern, $input);
    }
}

if (!function_exists('write_content')) {
    /**
     * Write, append contents to file.
     * @param string $filename — Path to the file where to write the data.
     * @param mixed $content
     * @param int $flags [optional] The value of flags can be any combination of the following flags (with some restrictions), joined with the binary OR (|) operator.
     * @param resource $context [optional] A valid context resource created with stream_context_create.
     * 
     * @return bool true or false on failure.
    */
    function write_content(string $filename, mixed $content, int $flag = 0, $context = null): bool 
    {
        $handler = false;
        $lock = $flag & (LOCK_EX | LOCK_NB | LOCK_SH | LOCK_UN);
        if(!$lock){
            $include = $flag & FILE_USE_INCLUDE_PATH;
            $mode = $flag & FILE_APPEND ? 'a' : 'w';
            $handler = fopen($filename, $mode, $include, $context);
        }
        
        if ($handler === false) {
            return file_put_contents($filename, $content, $flag, $context) !== false;
        }

        $result = fwrite($handler, $content);

        fclose($handler);

        return $result !== false;
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
     * @return bool|Validator|Factory::validate Return true or false else return validation instance
     */
    function validate(?array $inputs, ?array $rules, array $messages = []): bool|object 
    {
        if ($inputs === []) {
            return false;
        }

        if ($rules === []) {
            return true;
        }

        $validate = Factory::validate();

        if ($inputs === null && $rules === null) {
            return $validate;
        }

        $validate->setMessages($messages);
        $check = $validate->validate($inputs, $rules);

        return $check->isPassed();
    }
}

if (!function_exists('get_class_name')) {
    /**
     * Get class name from namespace or object
     * 
     * @param string|object $content 
     * 
     * @return string
    */
   function get_class_name(string|object $from): string 
    {
        if (is_string($from)) {
            $pos = strrpos($from, '\\');
            $className = ($pos !== false) ? substr($from, $pos + 1) : $from;
        } else {
            $className = get_class_name(get_class($from));
        }

        return $className;
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
        $allowPreviews = ['system', 'app', 'resources', 'writable', 'libraries', 'routes', 'bootstrap', 'builds'];
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

if (!function_exists('is_command')) {
     /**
     * Is CLI?
     *
     * Test to see if a request was made from the command line.
     *
     * @return bool
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
     * @return bool
    */
    function is_dev_server(): bool
    {
        if(NOVAKIT_ENV !== null){
            return true;
        }

        $serverName = ($_SERVER['SERVER_NAME'] ?? '');

        if ($serverName === '127.0.0.1' || $serverName === '::1' || $serverName === 'localhost') {
            return true;
        }
        
        if (strpos($serverName, 'localhost') !== false || strpos($serverName, '127.0.0.1') !== false) {
            return true;
        }
        
        return false;
    }
}