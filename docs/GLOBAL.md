### Global Helper Functions

This function is a helper function to make easier to call in any context.

```php 
/**
 * Get function instance
 *
 * @param string|null $context If context is present return instance of specified context else return 
 *          BaseFunction instance or null ['files', 'ip', 'document', 'escape']
 * @param mixed       ...$params Additional parameters.
 */
func(?string $context = null, ...$params)
```

Escapes input strings or arrays based on the specified context.

```php 
/**
 *
 * @param string|array $input    The input to be escaped.
 * @param string       $context  The context for escaping (e.g., 'html').
 * @param string|null  $encoding The encoding to be used.
 *
 * @return string|array The escaped input.
 */
escape(string|array $input, string $context = 'html', ?string $encoding = null)
```

Retrieves or sets session values.

```php 
/**
 *
 * @param string|null $key The key of the session value to retrieve or set.
 *
 * @return mixed|null The session value or null if no key is provided.
 */
session(?string $key = null)
```

Sets or retrieves cookie values.

```php 
/**
 *
 * @param string $name    The name of the cookie.
 * @param string $value   The value of the cookie.
 * @param array  $options Additional options for the cookie.
 * 
 * @return Cookie
 */
cookie(string $name, string $value = '', array $options = []): Cookie
```

Provides access to various factories.

```php 
/**
 *
 * @param string $context The context of the factory to retrieve.
 * @param mixed  ...$params Additional parameters.
 */
factory(string $context, ...$params)
```

Converts plain text to HTML entities.

```php
/**
 *
 * @param string|null $text The text to be converted.
 *
 * @return string The HTML-escaped text.
 */
text2html(?string $text): string
```

 Converts newline characters to HTML line breaks.

```php 
/**
 *
 * @param string|null $text The text containing newline characters.
 *
 * @return string The text with HTML line breaks.
 */
nl2html(?string $text): string
```

Imports a library or module.

```php 
/**
 *
 * @param string $library The name of the library or module to import.
 *
 * @return bool True if the library was successfully imported; otherwise, false.
 */
import(string $library): bool
```

Logs a message with the specified log level.

```php 
/**
 *
 * @param string $level   The log level (e.g., 'info', 'warning', 'error').
 * @param string $message The log message to be recorded.
 * @param array  $context Additional context for the log message.
 */
logger(string $level, string $message, array $context = []): void
```

Logs a message with the specified log level.

```php
/**
 *
 * @param string $level   The log level (e.g., 'info', 'warning', 'error').
 * @param string $message The log message to be recorded.
 * @param array  $context Additional context for the log message.
 */
logger(string $level, string $message, array $context = []): void
```

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
 */
setenv(string $key, string $value): void
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

Retrieves a translated string from the language files.

```php
/**
 *
 * @param string      $lookup       The key to look up in the language files.
 * @param string      $default      The default value to return if the key is not found.
 * @param array       $placeholders An array of placeholders and their values for dynamic content.
 * @param string|null $locale       The locale to use for translation.
 *
 * @return string The translated string.
 */
lang(string $lookup, string $default = '', array $placeholders = [], ?string $locale = null): string
```

Returns the absolute path of a directory joined with a suffix.

```php
/**
 *
 * @param string $directory The directory path.
 * @param string $suffix    The suffix to append to the directory path.
 *
 * @return string The absolute path with the appended suffix.
 */
root(string $directory, string $suffix): string
```

## Application Routing & Controllers

Creating a router and application controllers

[Routing & Controllers](ROUTING.md)