<?php
declare(strict_types=1);
/**
 * Luminova Framework global helper functions.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 * @see https://luminova.ng/docs/0.0.0/global/functions
 */
namespace Luminova\Funcs;

if (defined('APP_WARMED_UP')) {
    return;
}

use Luminova\Boot;
use Luminova\Components\String\Listifier;
use Luminova\Exceptions\UnexpectedValueException;
use Luminova\Exceptions\InvalidArgumentException;

/**
 * Load a configuration array from the `app/Config/` directory.
 *
 * The function loads a config file once, stores it in shared memory,
 * and returns the cached result on later calls. Use this only for
 * configuration files that return an array.
 *
 * @param string $filename The configuration name without extension (e.g. "Storage").
 * @param array|null $default The value returned when the file does not exist.
 *
 * @return array<mixed>|null  Returns the configuration array or the default value.
 * @throws UnexpectedValueException If loaded file does not return an array.
 *
 * @see import()  Load a PHP file with optional scoped variables.
 *
 * @example - Configuration File:
 * ```php
 *   // app/Config/SomeConfig.php
 *   <?php
 *   return ['foo', 'bar'];
 * ```
 *
 * @example - Loading Configuration:
 * ```php
 *   use function Luminova\Funcs\configs;
 *   $config = configs('SomeConfig');
 * ```
 *
 * > **Note** 
 * > The configuration file must return an array.
 */
function configs(string $filename, ?array $default = null): ?array 
{
    static $path = null;

    if (!\str_ends_with($filename, '.php')) {
        $filename = "{$filename}.php";
    }

    $key = "__APP_CONFIGS_{$filename}__";

    if (Boot::has($key)) {
        return (array) Boot::get($key);
    }

    if ($path === null) {
        $path = root('/app/Config/');
    }

    $file =  $path . $filename;

    if (\is_file($file)) {
        return Boot::set(
            $key, 
            (static function (string $__f): array {
                $data = require $__f;

                if(\is_array($data)){
                    return $data;
                }
                throw new UnexpectedValueException(
                    \sprintf(
                        'Configuration file "%s" must return an array, %s given.',
                        $__f,
                        \gettype($data)
                    )
                );
            })($file)
        );
    }

    return $default;
}

/**
 * Return duplicate values from an array.
 *
 * @param array $array Input array.
 * @param bool $withCounts Whether to return occurrence counts.
 * @param bool $preserveKeys Whether to preserve the original array keys.
 *
 * @return array Duplicate values, counts, or keyed values.
 */
function array_duplicates(
    array $array,
    bool $withCounts = false,
    bool $preserveKeys = false
): array
{
    $counts = array_count_values($array);

    $duplicates = array_filter(
        $counts,
        static fn(int $count): bool => $count > 1
    );

    if ($withCounts) {
        return $duplicates;
    }

    if ($preserveKeys) {
        return array_filter(
            $array,
            static fn(mixed $value): bool => isset($duplicates[$value])
        );
    }

    return array_keys($duplicates);
}

/**
 * Recursively escapes all string values in an array.
 *
 * **Supported contexts:**
 *
 * - `html` - Escape HTML content.
 * - `attr` - Escape HTML attribute values.
 * - `js`   - Escape JavaScript strings.
 * - `css`  - Escape CSS values.
 * - `url`  - Escape URLs.
 * - `raw`  - Leave the value unchanged.
 *
 * If an array key matches one of the supported context names, that context
 * is applied to the corresponding value. Otherwise, the specified default
 * context is used. Nested arrays are processed recursively.
 *
 * @param array<string|int,mixed> $input The array to escape.
 * @param string $context The default escape context (default: `'html'`).
 * @param string $encoding The character encoding to use (default: `'utf-8'`).
 *
 * @return array<string|int,mixed> Return the escaped array.
 *
 * @throws InvalidArgumentException If the encoding is empty, invalid, or unsupported.
 * @throws \Luminova\Exceptions\BadMethodCallException If an unsupported escape context is specified.
 *
 * @link https://luminova.ng/docs/0.0.0/functions/escaper
 *
 * @example - Example:
 * ```php
 * $value = array_escape([
 *     'html' => 'foo <script>alert("XSS")</script>',
 *     'url'  => 'https://example.com?foo=bar',
 * ]);
 * ```
 */
function array_escape(array $input, string $context = 'html', string $encoding = 'utf-8'): array
{
    static $contexts = null;
    $contexts ??= [
        'html'  => true, 
        'js'    => true, 
        'css'   => true, 
        'url'   => true, 
        'attr'  => true, 
        'raw'   => true
    ];
    
    $context = \strtolower($context);

    foreach($input as $key => $value){
        if(!\is_string($value) && !\is_array($value)){
            continue;
        }

        $k = \strtolower($key);
        $ctx = isset($contexts[$k]) ? $k : $context;

        if ($ctx === 'raw') {
            continue;
        }
        
        $input[$key] = escape($value, $ctx, $encoding);
    }

    return $input;
}

/**
 * Extract values from a specific column of an object list.
 *
 * This method extracts a column from an object, optionally re-indexing by another key.
 * It works like `array_column()` but for an object.
 * If `$property` is `null`, the entire object or is returned.
 *
 * @param object $from The input collection of (objects or iterable object).
 * @param string|int|null $property The key or property to extract from each item.
 * @param string|int|null $index Optional. A key/property to use as the array index for returned values.
 *
 * @return object Returns an object of extracted values. 
 *          If `$index` is provided, it's used as the keys.
 * 
 * @see get_column()
 * 
 * @example - Example:
 * ```php
 * $objects = (object) [
 *     (object)['id' => 1, 'name' => 'Alice'],
 *     ((object)['id' => 2, 'name' => 'Bob']
 * ];
 * object_column($objects, 'name'); // (object)['Alice', 'Bob']
 * object_column($objects, 'name', 'id'); // (object)[1 => 'Alice', 2 => 'Bob']
 * ```
 */
function object_column(
    object $from, 
    string|int|null $property = null, 
    string|int|null $index = null
): object
{
    $from = (array) $from;

    if ($from === []) {
        return (object)[];
    }

    $columns = [];
    foreach ($from as $item) {
        $isObject = \is_object($item);
        $value = ($property === null)
            ? $item
            : ($isObject ? ($item->{$property} ?? null) : ($item[$property] ?? null));

        if ($index === null) {
            $columns[] = $value;
            continue;
        }

        $key = $isObject ? ($item->{$index} ?? null) : ($item[$index] ?? null);
        if ($key === null) {
            continue;
        }

        $columns[$key] = $value;
    }

    return (object) $columns;
}

/**
 * Extract values from a specific column of an array or object list.
 *
 * Uses PHP `array_column()` or Luminova `object_column()` to support both arrays and objects as well.
 * If `$property` is `null`, the entire object or subarray is returned.
 *
 * @param array|object $from The input collection (array of arrays/objects or iterable object).
 * @param string|int|null $property The key or property to extract from each item.
 * @param string|int|null $index Optional. A key/property to use as the array index for returned values.
 *
 * @return array|object Returns an array of extracted values. 
 *          If `$index` is provided, it's used as the keys.
 * @see object_column()
 *
 * @example - Array Example:
 * ```php
 * $arrays = [
 *     ['id' => 1, 'name' => 'Alice'],
 *     ['id' => 2, 'name' => 'Bob']
 * ];
 * get_column($arrays, 'name'); // ['Alice', 'Bob']
 * get_column($arrays, 'name', 'id'); // [1 => 'Alice', 2 => 'Bob']
 *```
 * @example - Object Example:
 * ```php
 * $objects = (object) [
 *     (object)['id' => 1, 'name' => 'Foo'],
 *     (object)['id' => 2, 'name' => 'Bar']
 * ];
 * get_column($objects, 'name'); // (object)['Foo', 'Bar']
 * get_column($objects, 'name', 'id'); // (object)[1 => 'Foo', 2 => 'Bar']
 * ```
 */
function get_column(array|object $from, string|int|null $property, string|int|null $index = null): array|object 
{
    return \is_array($from) 
        ? \array_column($from, $property, $index)
        : object_column($from, $property, $index);
}

/**
 * Determine if an array is nested (contains arrays as values).
 *
 * If `$recursive` is true, it checks all levels deeply; otherwise, it checks only one level.
 *
 * @param array $array The array to check.
 * @param bool $recursive Whether to check recursively (default: false).
 * @param bool $strict Whether to require all values to be arrays (default: false).
 *
 * @return bool Return true if a nested array is found, false otherwise.
 *
 * @example - Examples:
 * ```php
 * is_nested([1, 2, 3]); // false
 * is_nested([1, [2], 3]); // true
 * is_nested(array: [1, [2], 3], strict: true); // false
 * is_nested([[1], [2, [3]]], true); // true
 * ```
 */
function is_nested(array $array, bool $recursive = false, bool $strict = false): bool 
{
    if ($array === []) {
        return false;
    }

    foreach ($array as $value) {
        if (!\is_array($value)) {
            if($strict){
                return false;
            }

            continue;
        }

        if ($recursive && !empty($value) && !is_nested($value, true, $strict)) {
            return false;
        }

        if (!$strict && !$recursive) {
            return true;
        }
    }

    return $strict || $recursive;
}

/**
 * Check if an array is associative (has non-integer or non-sequential keys).
 *
 * @param array $array The array to check.
 *
 * @return bool Return true if associative, false if indexed or empty.
 *
 * @example - Example:
 * ```php
 * is_associative(['a' => 1, 'b' => 2]); // true
 * is_associative([0 => 'a', 1 => 'b']); // false
 * is_associative([]); // false
 * ```
 */
function is_associative(array $array): bool
{
    if ($array === [] || isset($array[0])) {
        return false;
    }

    return \array_keys($array) !== \range(0, \count($array) - 1);
}

/**
 * Determine whether a value is a serialized string.
 *
 * When $testOnly is true, the function performs a lightweight
 * pattern check without calling unserialize().
 *
 * When $testOnly is false, the value is validated using
 * unserialize() with controlled class handling.
 *
 * @param mixed $value        The value to inspect.
 * @param bool  $testOnly     Whether to check format only.
 * @param bool  $allowClass   Whether object instantiation is allowed.
 *
 * @return bool Return true if the value appears to be serialized PHP string.
 */
function is_serialized(mixed $value, bool $testOnly = false, bool $allowClass = false): bool
{
    if (!\is_string($value)) {
        return false;
    }

    $value = \trim($value);

    if ($value === '') {
        return false;
    }
    
    if ($value === 'b:0;') {
        return true;
    }
    
    if ($testOnly) {
        return \preg_match(
            '/^(?:N;|b:[01];|i:-?\d+;|d:-?\d+(?:\.\d+)?;|s:\d+:"|a:\d+:{|O:\d+:"[^"]+":\d+:{)/',
            $value
        ) === 1;
    }

    try {
        return @\unserialize(
            $value, 
            ['allowed_classes' => $allowClass]
        ) !== false;
    } catch (\Throwable) {
        return false;
    }
}

/**
 * Convert an object, array, scalar, or string-list value to an array representation.
 *
 * @param mixed $input The input to convert (object, array, or scalar).
 *
 * @return array Return the array representation.
 * @see Listifier
 *
 * @example - Example:
 * ```php
 * to_array((object)['a' => 1, 'b' => (object)['c' => 2]]);
 * // ['a' => 1, 'b' => ['c' => 2]]
 * ```
 */
function to_array(mixed $input): array
{
    if (\is_string($input)) {
        return list_to_array($input) ?: [$input];
    }

    if (\is_array($input)) {
        return \array_map('to_array', $input);
    }

    if (!\is_object($input)) {
        return (array) $input;
    }

    $array = [];

    foreach ($input as $key => $value) {
        $array[$key] = (\is_object($value) || \is_array($value))
            ? to_array($value)
            : $value;
    }

    return $array;
}

/**
 * Convert an object, array, or string-list value to a JSON representation.
 *
 * @param array|string $input Input array or string-list(e.g, `foo=bar,bar=2,baz=[1;2;3]`).
 *
 * @return object|false JSON object if successful, false on failure.
 * @see Listifier
 * 
 * @example - Example:
 * ```php
 * to_object(['a' => 1, 'b' => 2]);
 * // (object)['a' => 1, 'b' => 2]
 *
 * String Listification
 * 
 * to_object('foo=bar,bar=2,baz=[1;2;3]');
 * // (object)[
 *       'foo' => 'bar', 
 *       'bar' => 2, 
 *       'baz' => [1, 2, 3]
 *   ]
 * ```
 */
function to_object(array|string $input): object|bool
{
    if ($input === [] || $input === '') {
        return (object)[];
    }

    if (\is_string($input)) {
        $input = \trim($input);

        if($input === ''){
            return false;
        }

        $input = list_to_array($input);

        if ($input === false) {
            return false;
        }
    }

    try {
        return \json_decode(\json_encode($input, \JSON_THROW_ON_ERROR));
    } catch (\JsonException) {
        return false;
    }
}

/**
 * Convert a valid string list to an array.
 *
 * The function uses `Luminova\Components\String\Listifier` to convert a string list format into an array.
 *
 * @param string $list The string to convert.
 *
 * @return array|false Returns the parsed array, or false on failure.
 * 
 * @see Listifier
 * @link https://luminova.ng/docs/0.0.0/utilities/string-listification
 * 
 *
 * @example - Example:
 * ```php
 * list_to_array('a,b,c')          // ['a', 'b', 'c']
 * list_to_array('"a","b","c"')    // ['a', 'b', 'c']
 * ```
 */
function list_to_array(string $list): array|bool 
{
    if (!$list) {
        return false;
    }
    
    try{
        return Listifier::toArray($list);
    }catch(\Throwable){
        return false;
    }
}

/**
 * Check if all values in a string list exist in a given array.
 *
 * This function converts the list using `list_to_array()` and verifies all items exist in the array.
 *
 * @param string $list The string list to check.
 * @param array $array The array to search for list-string values in.
 *
 * @return bool Returns true if all list items exist in the array; false otherwise.
 * 
 * @see Listifier
 * @link https://luminova.ng/docs/0.0.0/utilities/string-listification
 */
function list_in_array(string $list, array $array = []): bool 
{
    if(!$array && $list === ''){
        return true;
    }

    if(!$array || $list === ''){
        return false;
    }
    
    $map = is_list($list) ? list_to_array($list) : [$list];

    if($map === false){
        return false;
    }

    foreach ($map as $item) {
        if (!\in_array($item, $array)) {
            return false;
        }
    }

    return true;
}

/**
 * Check if a string is a valid Luminova listify-formatted string.
 *
 * Validates that the string matches a recognized list format used by listifier.
 *
 * @param string $input The string to validate.
 *
 * @return bool Returns true if valid; false otherwise.
 * 
 * @see Listifier
 * @link https://luminova.ng/docs/0.0.0/utilities/string-listification
 */
function is_list(string $input): bool 
{
    return $input && Listifier::isList($input);
}

/**
 * Get the item with the lowest value for a given key, or the minimum value from a flat numeric list.
 *
 * For multi-dimensional arrays, it compares values using the specified key and returns the full item
 * with the smallest value.
 *
 * If no key is provided, the function treats the input as a flat numeric list and returns the minimum value.
 * Non-numeric values are ignored in this mode.
 *
 * @param array $items List of values or array of associative arrays.
 * @param string|null $key Key used for comparison in structured arrays.
 *
 * @return mixed|null Returns the lowest item/value, or null if no valid comparison can be made.
 * 
 * @example - Example:
 * ```php
 * $data = [
 *     ['id' => 1, 'value' => 10],
 *     ['id' => 2, 'value' => 5],
 *     ['id' => 3, 'value' => 15],
 * ];
 * $minItem = array_min($data, 'value'); // ['id' => 2, 'value' => 5]
 * ```
 */
function array_min(array $items, ?string $key = null): mixed
{
    if ($key === null) {
        $items = array_filter($items, 'is_numeric');

        return ($items === []) ? null : min($items);
    }

    $result = null;
    $min = null;

    foreach ($items as $item) {
        if (!is_array($item) || !isset($item[$key])) {
            continue;
        }

        if ($min === null || $item[$key] < $min) {
            $min = $item[$key];
            $result = $item;
        }
    }

    return $result;
}

/**
 * Get the item with the highest value for a given key, or the maximum value from a flat numeric list.
 *
 * For multi-dimensional arrays, it compares values using the specified key and returns the full item
 * with the largest value.
 *
 * If no key is provided, the function treats the input as a flat numeric list and returns the maximum value.
 * Non-numeric values are ignored in this mode.
 *
 * @param array $items List of values or array of associative arrays.
 * @param string|null $key Key used for comparison in structured arrays.
 *
 * @return mixed|null Returns the highest item/value, or null if no valid comparison can be made.
 * 
 * @example - Example:
 * ```php
 * $data = [
 *     ['id' => 1, 'value' => 10],
 *     ['id' => 2, 'value' => 5],
 *     ['id' => 3, 'value' => 15],
 * ];
 * $maxItem = array_max($data, 'value'); // ['id' => 3, 'value' => 15]
 * ```
 */
function array_max(array $items, ?string $key = null): mixed
{
    if ($key === null) {
        $items = array_filter($items, 'is_numeric');

        return ($items === []) ? null : max($items);
    }

    $result = null;
    $max = null;

    foreach ($items as $item) {
        if (!is_array($item) || !isset($item[$key])) {
            continue;
        }

        if ($max === null || $item[$key] > $max) {
            $max = $item[$key];
            $result = $item;
        }
    }

    return $result;
}

/**
 * Merges arrays recursively ensuring unique values in nested arrays. 
 * 
 * Unlike traditional recursive merging, it replaces duplicate values rather than appending them. 
 * When two arrays contain the same key, the value in the second array replaces the one in the first array.
 *
 * @param array $array The array to merge into.
 * @param array ...$arrays The arrays to merge.
 * 
 * @return array Return the merged array with unique values.
 * 
 * @example - Example:
 * ```php
 * $array1 = ['a' => 1, 'b' => ['x' => 10, 'y' => 20]];
 * $array2 = ['b' => ['y' => 30, 'z' => 40], 'c' => 3];
 * 
 * $result = array_merge_recursive_distinct($array1, $array2);
 * // Result: ['a' => 1, 'b' => ['x' => 10, 'y' => 30, 'z' => 40], 'c' => 3]
 * ```
 */
function array_merge_recursive_distinct(array $array, array ...$arrays): array
{
    foreach ($arrays as $values) {
        foreach ($values as $key => $value) {
            $array[$key] = (\is_array($value) && isset($array[$key]) && \is_array($array[$key])) 
                ? array_merge_recursive_distinct($array[$key], $value)
                : $value;
        }
    }

    return $array;
}

/**
 * Merges multiple arrays recursively. 
 * 
 * When two arrays share the same key, values from the second array overwrite those from the first. 
 * Numeric keys are appended only if the value doesn't already exist in the array.
 *
 * @param array ...$array The arrays to be merged.
 * 
 * @return array Return the merged result array.
 * @example - Example:
 * ```php
 * $array1 = ['a' => 1, 'b' => ['x' => 10, 'y' => 20]];
 * $array2 = ['b' => ['y' => 30, 'z' => 40], 'c' => 3];
 * 
 * $result = array_merge_recursive_replace($array1, $array2);
 * // Result: ['a' => 1, 'b' => ['x' => 10, 'y' => 30, 'z' => 40], 'c' => 3]
 * ```
 */
function array_merge_recursive_replace(array ...$array): array 
{
    $merged = \array_shift($array);
    
    foreach ($array as $params) {
        foreach ($params as $key => $value) {
            if (\is_numeric($key) && !\in_array($value, $merged, true)) {
                $merged[] = \is_array($value) 
                    ? array_merge_recursive_replace($merged[$key] ?? [], $value) 
                    : $value;
            } else {
                $merged[$key] = (isset($merged[$key]) && \is_array($value) && \is_array($merged[$key])) 
                    ? array_merge_recursive_replace($merged[$key], $value) 
                    : $value;
            }
        }
    }

    return $merged;
}

/**
 * Merges two arrays, treating the first array as the default configuration and the second as new or override values.
 * 
 * If both arrays contain nested arrays, they are merged recursively, 
 * ensuring that default values are preserved and new values are added where applicable.
 *
 * @param array $default The default options array.
 * @param array $new The new options array to merge.
 * 
 * @return array Return the merged options array with defaults preserved.
 * 
 * @example - Example:
 * ```php
 * $default = ['foo' => 1, 'bar' => 2, 'baz' => ['x' => 10, 'y' => 20]];
 * $new = ['bar' => 3, 'baz' => ['y' => 30, 'z' => 40], 'qux' => 4];    
 * 
 * $result = array_extend_default($default, $new);
 * // Result: ['foo' => 1, 'bar' => 2, 'baz' => ['x' => 10, 'y' => 20, 'z' => 40], 'qux' => 4]
 * ```
 */
function array_extend_default(array $default, array $new): array 
{
    $result = $default; 

    foreach ($new as $key => $value) {
        // If the key does not exist in the default, add it
        if (!\array_key_exists($key, $result)) {
            $result[$key] = $value;
        } elseif (\is_array($result[$key]) && \is_array($value)) {
            // If both values are arrays, merge them recursively
            $result[$key] = array_extend_default($result[$key], $value);
        }
    }

    return $result;
}

/**
 * Merges a response into the provided results variable.
 * 
 * This function merges a response into the provided results variable 
 * while optionally preserving the structure of nested arrays.
 * 
 * @param mixed &$results The results variable to which the response will be merged or appended.
 *                       This variable is passed by reference and may be modified.
 * @param mixed $response The response variable to merge with results. It can be an array, string, 
 *                       or other types.
 * @param bool $preserveNested Optional. Determines whether to preserve the nested structure 
 *                               of arrays when merging (default: true).
 *
 * @return void
 * @since 3.3.4
 * @see https://luminova.ng/docs/3.3.0/global/functions#lmv-docs-array-merge-result
 * 
 * @example - Example:
 * ```php
 * $results = ['a' => 1, 'b' => 2];
 * $response = ['b' => 3, 'c' => 4];
 * 
 * array_merge_result($results, $response);
 * // Result: ['a' => 1, 'b' => 2, 'c' => 4]
 * 
 * array_merge_result($results, $response, false);
 * // Result: ['a' => 1, 'b' => 3, 'c' => 4]
 * ```
 * 
 * @example - In a loop:
 * ```php
 * $results = [];
 * foreach ($responses as $response) {
 *     array_merge_result($results, $response);
 * }
 * 
 * // Result: $results contains all responses merged together.
 */
function array_merge_result(mixed &$results, mixed $response, bool $preserveNested = true): void
{
    if ($results === null || $results === []) {
        $results = $response;
        return;
    }
    
    if (\is_array($results)) {
        if (!$preserveNested && \is_array($response)) {
            $results = \array_merge($results, $response);
            return;
        }

        $results[] = $response;
        return;
    } 
    
    if (\is_string($results)) {
        $results = \is_array($response) 
            ? \array_merge([$results], $preserveNested ? [$response] : $response) 
            : [$results, $response];

        return;
    }

    $results = [$results];

    if (!$preserveNested && \is_array($response)) {
        $results = \array_merge($results, $response);
        return;
    }

    $results[] = $response;
}