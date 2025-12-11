<?php 
declare(strict_types=1);
/**
 * Luminova Framework PHP polyfill functions.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng/docs/0.0.0/global/constants
 */

use Luminova\Config\Env;
use Luminova\Debugger\Tracer;

if (!function_exists('json_validate')) {
    /**
     * Check if the input is a valid JSON object.
     *
     * @param mixed $input The input to check.
     * @param int $depth Maximum nesting depth of the structure being decoded (default: 512).
     * @param int $flags Optional flags (default: 0).
     *
     * @return bool Returns true if the input is valid JSON; false otherwise.
     */
    function json_validate(mixed $input, int $depth = 512, int $flags = 0): bool
    {
       if (!is_string($input)) {
            return false;
        }
        
        json_decode($input, null, $depth, $flags);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('array_is_list')) {
    /**
     * Check if array is list.
     * 
     * @param array $array The array to check.
     * 
     * @return bool Return true if array is sequential, false otherwise.
     */
    function array_is_list(array $array): bool
    {
        if ($array === [] || $array === array_values($array)) {
            return true;
        }

        $position = 0;

        foreach ($array as $key => $_) {
            if ($key !== $position++) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('array_first')) {
    /**
     * Get the first element of an array.
     *
     * Returns null if the array is empty. Works for both indexed and associative arrays.
     *
     * @param array $array The array to get the first element from.
     * 
     * @return mixed|null Return the first element of the array, or null if empty.
     */
    function array_first(array $array): mixed
    {
        return ($array === []) ? null : $array[array_key_first($array)];
    }
}

if (!function_exists('array_last')) {
    /**
     * Get the last element of an array.
     *
     * Returns null if the array is empty. Works for both indexed and associative arrays.
     *
     * @param array $array The array to get the last element from.
     * 
     * @return mixed|null Return the last element of the array, or null if empty.
     */
    function array_last(array $array): mixed
    {
        return ($array === []) ? null : $array[array_key_last($array)];
    }
}

/**
 * Retrieve an environment variable value.
 *
 * The value is automatically normalized to its corresponding PHP type,
 * including booleans, numbers, null, and arrays.
 *
 * @param string $name The environment variable name.
 * @param mixed $default The value to return when the key is not found.
 *
 * @return mixed The resolved environment value, or `$default` if not found.
 *
 * @see \Luminova\Config\Env::get()
 * @see \Luminova\Config\Env::value() to retrieve raw value
 */
function env(string $name, mixed $default = null): mixed
{
    $name = trim($name);

    if ($name === '') {
        return $default;
    }

    return Env::get($name, $default);
}

/**
 * Sets an environment variable, optionally saving it to the `.env` file.
 *
 * @param string $name The environment variable name/key.
 * @param array|string|float|bool|int|null $value The value to set for the environment variable.
 * @param bool $persist Whether to store/update the variable in the `.env` file (default: false).
 * 
 * @return bool Returns true on success, false on failure.
 * @see \Luminova\Config\Env::set() for more details on how the variable is stored and persisted.
 *
 * @example Temporarily set an environment variable for the current runtime:
 * ```php
 * setenv('FOO_KEY', 'foo value');
 * ```
 *
 * @example Set an environment variable and persist it in the `.env` file:
 * ```php
 * setenv('FOO_KEY', 'foo value', true);
 * ```
 *
 * @example Add or update an environment variable as a disabled entry:
 * ```php
 * setenv(';FOO_KEY', 'foo value', true);
 * ```
 */
function setenv(string $name, array|string|float|bool|int|null $value, bool $persist = false): bool
{
    $name = trim($name);

    if ($name === '') {
        return false;
    }

   return Env::set($name, $value, $persist);
}

/**
 * Convert a numeric string into its appropriate numeric type.
 * 
 * This method detects whether the given string represents
 * a floating-point number or an integer. If the string
 * contains a decimal point (.) or scientific notation (e),
 * it is converted to a float; otherwise, it is converted to an int.
 * 
 * @param string $value The numeric string to convert.
 * @param bool $toLowercase Whether to convert the string to lowercase before processing.
 *                    Useful when checking for scientific notation (e.g., "1E3").
 * 
 * @return float|int Returns the numeric value as int or float depending on the input.
 */
function to_numeric(string $value, bool $toLowercase = false): float|int
{
    $value = trim($value);

    if ($value === '') {
        return 0;
    }

    if ($toLowercase) {
        $value = strtolower($value);
    }

    if (ctype_digit($value)) {
        return (int) $value;
    }

    if (!is_numeric($value)) {
        return 0;
    }

    return (str_contains($value, '.') || str_contains($value, 'e'))
        ? (float) $value
        : (int) $value;
}

/**
 * Execute a callback with the given value and return the original value.
 *
 * This is useful when an object needs to be modified or inspected before
 * being returned, without changing the value returned by the operation.
 *
 * @template T
 *
 * @param T $value The value to pass to the callback.
 * @param callable(T):void $callback The callback to execute.
 *
 * @return T The original value.
 * @example - Example:
 * ```php
 * $user = tap(new User(), function (User $user): void {
 *      $user->setName('Peter');
 *      $user->setActive(true);
 * });
 * ```
 */
function tap(mixed $value, callable $callback): mixed
{
    $callback($value);

    return $value;
}

/**
 * Pause execution for a specified duration with microsecond precision.
 *
 * Supports fractional seconds by converting the duration to microseconds
 * before calling {@see usleep()}.
 *
 * @param float|int $seconds Duration to wait in seconds.
 *                           Supports fractions, e.g. `0.5` for 500 milliseconds.
 * @param float|int|null $max Maximum wait duration in seconds, or null for no limit.
 *
 * @return void
 * @example - Examples:
 * ```php
 * uwait(1);     // 1 second
 * uwait(2.4);   // 2 seconds (rounded)
 * uwait(2.6);   // 3 seconds (rounded)
 * 
 * uwait(0.5);    // 500 milliseconds
 * uwait(0.25);   // 250 milliseconds
 * uwait(1.75);   // 1.75 seconds
 * 
 * uwait(10_000);    // 10,000 seconds
 * uwait(0.01);      // 10 milliseconds
 * uwait(0.00001);   // 10 microseconds
 * ```
 */
function uwait(float|int $seconds, float|int|null $max = null): void
{
    $seconds = ($max === null) 
        ? (float) $seconds 
        : min((float) $seconds, (float) $max);

    if ($seconds <= 0.0) {
        return;
    }

    usleep((int) round($seconds * 1_000_000));
}

/**
 * Dumps one or more values for debugging.
 *
 * Uses `var_dump()` to output each value. Execution continues
 * after the values are dumped.
 *
 * @param mixed ...$vars Values to dump.
 *
 * @return void
 *
 * @see dd()  - Dump Die
 * @see ddd() - Discard Dump Die
 */
function dump(mixed ...$vars): void
{
    $isCli = PHP_SAPI === 'cli';
    $separator = $isCli ? PHP_EOL : '<br/>';

    if (!$isCli) {
        ob_start();
    }

    foreach ($vars as $var) {
        var_dump($var);
        echo $separator;
    }

    if (!$isCli) {
        ob_end_flush();
    }
}

/**
 * Dumps one or more values for debugging and terminates execution.
 *
 * Uses the tracer when available; otherwise, falls back to {@see dump()}.
 * Existing output buffers are preserved.
 *
 * @param mixed ...$vars Values to dump.
 *
 * @return never Always terminates execution with exit status 1.
 *
 * @see ddd()  - Discard Dump Die
 * @see dump() - Dump
 */
function dd(mixed ...$vars): never
{
    static $hasTracer = null;
    $hasTracer ??=class_exists(Tracer::class);

    if ($hasTracer === true) {
        Tracer::dump(...$vars);
    } else {
        dump(...$vars);
    }

    exit(1);
}

/**
 * Discards all active output buffers, dumps one or more values,
 * and terminates execution.
 *
 * Unlike {@see dd()}, this function removes all previously buffered
 * output before displaying the debug output. It uses the tracer when
 * available; otherwise, it falls back to {@see dump()}.
 *
 * @param mixed ...$vars Values to dump.
 *
 * @return never Always terminates execution with exit status 1.
 *
 * @see dd()   - Dump Die
 * @see dump() - Dump
 */
function ddd(mixed ...$vars): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    dd(...$vars);
}
