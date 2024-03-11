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
use \Countable;

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
        if (getenv($key) !== false) {
            $env = getenv($key);
        }elseif (isset($_ENV[$key])) {
            $env = $_ENV[$key];
        }elseif (isset($_SERVER[$key])) {
            $env = $_SERVER[$key];
        }

        return $env ?? $default;
    }
}

if(!function_exists('setenv')){
    /**
     * Set an environment variable if it doesn't already exist.
     *
     * @param string $key The key of the environment variable.
     * @param string $value The value of the environment variable.
     * @param bool $add_to_env Save or update to .env file 
     * 
     * @return void
     */
    function setenv(string $key, string $value, bool $add_to_env = false): void
    {
        if (!getenv($key, true)) {
            putenv("{$key}={$value}");
        }
    
        if (empty($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    
        if (empty($_SERVER[$key])) {
            $_SERVER[$key] = $value;
        }
    
        if ($add_to_env) {
            $envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
            $envContents = @file_get_contents($envFile);
            if($envContents === false){
                return;
            }
            $keyExists = (strpos($envContents, "$key=") !== false || strpos($envContents, "$key =") !== false);
            //$keyValueExists = preg_match('/^' . preg_quote($key, '/') . '\s*=\s*.*$/m', $envContents);
    
            if (!$keyExists) {
                @file_put_contents($envFile, "\n$key=$value", FILE_APPEND);
            } else {
                $newContents = preg_replace_callback('/(' . preg_quote($key, '/') . ')\s*=\s*(.*)/',
                    function($match) use ($key, $value) {
                        return $match[1] . '=' . $value;
                    },
                    $envContents
                );
                @file_put_contents($envFile, $newContents);
            }
        }
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
        $ip = Functions::ip()->get();

        if($ip_info){
            $info = Functions::ip()->getInfo($ip, $options);

            return $info;
        }

       return $ip;
    }
}

if(!function_exists('is_empty')){
    /**
     * Check if values are empty.
     * 
     * @param mixed ...$values Arguments.
     * 
     * @return bool True if any of the values are empty, false otherwise.
     */
    function is_empty(mixed ...$values): bool 
    {
        foreach ($values as $value) {
            if (is_null($value) || (is_string($value) && trim($value) === '') || empty($value) || (is_object($value) && $value instanceof Countable && count($value) === 0)) {
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
     * Returns a shared instance of the class
     * Or factory instance if context is null
     *
     * Same as:
     * @example $config = factory('config')
     * @example $config = \Luminova\Application\Factory::config();
     * @example $config = new \Luminova\Config\Configuration();
     * 
     * @param string|null $context The class name to load
     * @param mixed ...$params
     * 
     * @return Factory|object|null
     */
    function factory(string|null $context, ...$params): object|null
    {
        if($context === null){
            return new Factory();
        }

        return Factory::$context(...$params);
    }
}

if(!function_exists('add_factory')) {
    /**
     * Add a class to factory a shared instance
     * The identifier will be converted to lower case
     *
     * Usages:
     * @example add_factory(Configuration::class, 'config) as $config = factory('config)
     * @example add_factory('\Luminova\Config\Configuration', 'config) as $config = factory('config)
     * @example add_factory(Configuration::class) as $config = factory('configuration)
     * 
     * @param string $className The class name to add
     * @param string|null $identifier The identifier for the class 
     * 
     * @return bool If class was added 
     * @throws RuntimeException If class already exists
     */
    function add_factory(string $className, ?string $identifier = null): bool
    {
        return Factory::add($className, $identifier);
    }
}

if(!function_exists('remove_factory')) {
    /**
     * Delete or clear factory
     * If no class was passed clear all cached instances of factory classes.
     * Else delete a specific factory instance and clear its cached instances
     * 
     * @param string $className The class name to delete and clear it cached
     * 
     * @return bool 
     */
    function remove_factory(?string $className = null): bool
    {
        if($className === null){
            return Factory::clear();
        }

        return Factory::delete($className);
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

if (!function_exists('root')) {
    /**
     * Return to the root directory of your project.
     *
     * @param string $directory The directory to start searching for .env
     * @param string $suffix Prepend a path to root directory.
     * 
     * @return string $path + $suffix
     */
    function root(string $directory = __DIR__, string $suffix = ''): string
    {
        $path = realpath($directory);

        if ($path === false) {
            return $suffix; 
        }

        do {
            if (file_exists($path . DIRECTORY_SEPARATOR . '.env')) {
                if(str_starts_with($suffix, DIRECTORY_SEPARATOR)){
                    return $path . $suffix;
                }

                return $path . DIRECTORY_SEPARATOR . $suffix;
            }
            
            $parent = dirname($path);
            if ($parent === $path) {
                return $suffix;
            }

            $path = $parent;
        } while (true);
    }
}

if (!function_exists('path')) {
    /**
    * Get directory if context name is null Paths instance will be returned
    * 
    * @param string|null $context Path context name to return [system, plugins, library, controllers, writeable, logs, caches,
    *          public, assets, views, routes, languages]
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