### Global Helper Functions

This function is a helper function to make it easier to call in any context.

Retrieves the value of an environment variable.

```php
/**
 *
 * @param string     $key     The name of the environment variable.
 * @param mixed|null $default The default value to return if the environment variable is not set.
 *
 * @return mixed The value of the environment variable, or the default value if not set.
 */
env(string $key, mixed $default = null): mixed
```

Sets an environment variable.
```php
/**
 *
 * @param string $key   The name of the environment variable.
 * @param string $value The value to set for the environment variable.
 * @param bool $add_to_env Save or update to .env file 
 */
setenv(string $key, string $value, bool $add_to_env = false): void
```

Gets or sets the current locale.

```php
/**
 *
 * @param string|null $locale The locale to set.
 *
 * @return string|bool The current locale, or false if setting the locale failed.
 */
locale(?string $locale = null): string|bool
```

Return Functions instance or a specific context instance if context name is specified.
Supported contexts: `'files', 'ip', 'document', 'escape', 'tor'`.

```php 
/**
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
 func(?string $context = null, ...$params): mixed 
```

Convert a string to kebab case.

```php
/**
 * @param string $string The input string to convert.
 * 
 * @return string The kebab-cased string.
 */
kebab_case(string $input): string 
```

Escapes a string or array of strings based on the specified context.
Supported context: `'html', 'js', 'css', 'url', 'attr', 'raw'`.

```php 
/**
* @param string|array $input The string or array of strings to be escaped.
* @param string $context The context in which the escaping should be performed. 
* @param string|null $encoding The character encoding to use. null will result to 'utf-8'.
* 
* @return mixed The escaped string or array of strings.
* @throws InvalidArgumentException When an invalid or blank encoding is provided.
* @throws BadMethodCallException When an invalid context is called
* @throws RuntimeException When the string is not valid UTF-8 or cannot be converted.
*/
escape(string|array $input, string $context = 'html', ?string $encoding = null): mixed 
```

Checks if the given IP address is a Tor exit node.
If you don't pass the IP address it will use the current IP address instead

```php
/**
* @param string|null $ip Ip address to check else use current ip address
* 
* @return bool 
*/
is_tor(string|null $ip = null): bool 
```

Get user IP address or return ip address information
To get ip address information third-party service is required.
Available IP API services are `[ipapu or iphub]`, API configuration can be done `app/Controllers/Config/IPConfig.php`.

```php 
/**
* @param bool $ip_info If true return ip address information instead
* @param array $options Pass additional options to return with IP information
* 
* @return string|object|null 
*/
function ip_address(bool $ip_info = false, array $options = []): string|object|null
```

Check if values are empty.

```php
/**
 * @param mixed ...$values Arguments.
 * 
 * @return bool True if any of the values are empty, false otherwise.
 */
is_empty(mixed ...$values): bool 
```

Retrieves session data if key is present else return session instance

```php 
/**
 *
 * @param string|null $key Key to retrieve the data
 *
 * @return mixed|Session The session value or null if no key is provided.
 */
session(?string $key = null): mixed|Session
```

Create and return cookie instance.

```php 
/**
 * @param string $name    The name of the cookie.
 * @param string $value   The value of the cookie.
 * @param array  $options Additional options for the cookie.
 * 
 * @return Cookie
 */
cookie(string $name, string $value = '', array $options = []): Cookie
```

Simplify way to provides access to various class factories.
Returns a shared instance of the class or factory instance if context is null

```php 
/**
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
factory(string|null $context, ...$params): object|null
```

Add a class to factory a shared instance, the identifier will be converted to lowercase

```php 
/**
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
add_factory(string $className, ?string $identifier = null): bool
```

Delete or clear factory instance, if no class name was passed it will clear all cached instances of factory classes.
Else delete a specific factory instance and clear its cached instances

```php 
/**
 * @param string $className The class name to delete and clear it cached
 * 
 * @return bool 
 */
remove_factory(?string $className = null): bool
```

Get browser information.
You can pass a user agent string as parameter to read.

```php 
/**
 * @param string|null $user_agent
 * @param bool $return_array If set to true, this function will return an array instead of an object.
 * 
 * @return array|object
 */
browser(?string $user_agent = null, bool $return_array = false): array|object
```

Converts plain text to HTML entities.

```php
/**
 * @param string $text A string containing the text to be processed.
 * 
 * @return string $text The processed text with HTML entities.
 */
text2html(?string $text): string
```

Converts newline characters to HTML entities line breaks.
> This is useful when you want to display text in an HTML textarea while preserving the original line breaks.

```php 
/**
 * @param string $text A string containing the text to be processed.
 * 
 * @return string $text
 */
nl2html(?string $text): string
```

Import a custom library or module into your project.
You must place your external libraries or module in `libraries/libs/` directory

```php 
/**
 * @param string $library the name of the library
 * @example Foo/Bar/Baz
 * @example Foo/Bar/Baz.php
 * 
 * @return bool true if the library was successfully imported
 * @throws RuntimeException if library could not be found
 */
import(string $library): bool
```

Logs a message with the specified log level.
Log levels `['emergency, alert, critical, error, warning, notice, info, debug, exception, php_errors']`

```php 
/**
 * @param string $level The log level.
 * @param string $message The log message.
 * @param array $context Additional context data (optional).
 *
 * @return void
 * @throws InvalidArgumentException
 */
logger(string $level, string $message, array $context = []): void
```


Retrieves a translated string from the language files.
You must create your language translations in `app/Controllers/Languages`, `Context.local.php` example `Users.ru.php`.
It also supports nested array and placeholder replacer example `Error name {name} and email {email}`.

```php
/**
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
lang(string $lookup, string $default = '', ?string $locale = null,  array $placeholders = []): string
```

Returns the absolute path of a directory joined with a suffix.

```php
/**
 * @param string $directory The directory to start searching for .env
 * @param string $suffix Prepend a path to root directory.
 * 
 * @return string $path + $suffix
 */
root(string $directory, string $suffix): string
```

Get directory if the context name is null Paths instance will be returned
Available Context: `[system, plugins, library, controllers, writeable, logs, caches, public, assets, views, routes, languages]`

```php 
/**
* @param string|null $context Path context name to return 
* 
* @return string|Paths
*/
path(null|string $context = null): string|object
```

Check if array is a nested array or not 

```php
/**
* @param array $array
* 
* @return bool 
*/
is_nested(array $array): bool 
```

Check if array is associative array or not

```php
/**
 * @param array $array
 * 
 * @return bool 
*/
is_associative(array $array): bool 
```

Convert an object to array.

```php
/**
 * @param mixed $object The object to convert to an array.
 * 
 * @return array $array Finalized array representation of the object
 */
 to_array(mixed $object): array 
```

Convert an array or string list to json object

```php 
/**
 * @param array|string $input Array or String list to convert
 * 
 * @return object $object
 */
to_object(array|string $input): object 
```

Convert a string list to an array. 
> Example of string list `a,b,c`, `"'a', 'b', 'c'"`.

```php
/**
 * @example list_to_array('a,b,c') => ['a', 'b', 'c']
 * @example list_to_array('"a","b","c"') => ['a', 'b', 'c']
 * 
 * @param string $list string list
 * @return array|bool $matches
*/
list_to_array(string $list): array|bool 
```

Check if a string list has same values with an array values.
If any of the list doesn't exist in array it will return false

```php 
/**
 * @param string $list string list
 * @param array $array Array to map list to
 * 
 * @return bool exist or not
*/
list_in_array(string $list, array $array = []): bool 
```

Check if string is a valid list format.
Specify second argument `$trim` to trim the list value if it contains a whitespace else any value with white space will result as false.

```php 
/**
 * @param string $input string to check
 * @param bool $trim Trim whitespace around the values  
 * 
 * @return bool true or false on failure.
*/
is_list(string $input, bool $trim = false):bool 
```

Write, append contents to file.
It also supports default PHP file system flags and bitwise OR operator (|), for combination of flags.

```php
/**
 * @param string $filename — Path to the file where to write the data.
 * @param mixed $content
 * @param int $flags [optional] The value of flags can be any combination of the following flags (with some restrictions), joined with the binary OR (|) operator.
 * @param resource $context [optional] A valid context resource created with stream_context_create.
 * 
 * @return bool true or false on failure.
*/
write_content(string $filename, mixed $content, int $flag = 0, $context = null): bool
```

Validate input fields and return bool `true or false`, or return validation instance if `$input` and `$rules` arguments are specified `NULL`.

```php 
/**
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
 * @return bool|Validator Return true or false else return validation instance
 */
validate(?array $inputs, ?array $rules, array $messages = []): bool|object
```

## Application Routing & Controllers

Creating a router and application controllers

[Routing & Controllers](ROUTING.md)