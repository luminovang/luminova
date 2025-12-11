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

use Luminova\Exceptions\InvalidArgumentException;

/**
 * Convert a string to kebab-case format.
 *
 * Replaces all non-letter and non-digit characters with hyphens.
 * Optionally converts the entire string to lowercase.
 *
 * @param string $input The input string to convert.
 * @param bool $toLower Whether to convert the result to lowercase (default: true).
 *
 * @return string Return the kebab-cased version of the input string.
 * 
 * @example - Example:
 * ```php
 * echo kebab_case('hello world'); // hello-wold
 * echo kebab_case('HelLo-World'); // hello-wold
 * echo kebab_case('HelLo worlD'); // HelLo-worlD
 * ```
 */
function kebab_case(string $input, bool $toLower = true): string 
{
    if($input === ''){
        return '';
    }

    $input = \preg_replace('/[^\p{L}\p{N}]+/u', ' ', $input);
    $input = \trim(\str_replace(' ', '-', $input), '-');

    return $toLower ? \strtolower($input) : $input;
}

/**
 * Convert a string to camel case.
 *
 * @param string $input The string to convert.
 * 
 * @return string Return the string converted to camel case.
 * 
 * @example - Example:
 * ```php
 * echo camel_case('hello world'); // helloWold
 * echo camel_case('hello-world'); // helloWold
 * ```
 */
function camel_case(string $input): string
{
    $input = \str_replace(['-', ' '], '_', $input);
    $parts = \explode('_', $input);

    $camelCase = '';
    $firstPart = true;

    foreach ($parts as $part) {
        $camelCase .= $firstPart ? \strtolower($part) : \ucfirst($part);
        $firstPart = false;
    }
    
    return $camelCase;
}

/**
 * Convert a string to PascalCase format.
 *
 * Replaces spaces, underscores, and hyphens with word boundaries,
 * capitalizes each word, and removes all delimiters.
 *
 * @param string $input The input string to convert.
 *
 * @return string Return the PascalCase version of the input string.
 * 
 * @example - Example:
 * ```php
 * echo pascal_case('hello world'); // HelloWold
 * echo pascal_case('hello-world'); // HelloWold
 * ```
 */
function pascal_case(string $input): string
{
    if($input === ''){
        return '';
    }

    $input = \preg_replace('/[_\-\s]+/', ' ', \strtolower($input));
    return \str_replace(' ', '', \ucwords($input));
}

/**
 * Convert a string to snake_case format.
 *
 * Converts mixed-case or delimited text into lowercase words separated by underscores.
 * Handles transitions from uppercase to lowercase, as well as spaces, hyphens, and dots.
 *
 * @param string $input The input string to convert.
 *
 * @return string Returns the snake_case version of the input string.
 *
 * @example - Examples:
 * ```php
 * echo snake_case('HelloWorld');        // hello_world
 * echo snake_case('HTMLParser');        // html_parser
 * echo snake_case('getHTTPResponse');   // get_http_response
 * echo snake_case('hello world');       // hello_world
 * echo snake_case('hello-world');       // hello_world
 * ```
 */
function snake_case(string $input): string
{
    if($input === ''){
        return '';
    }

    $input = \preg_replace('/[\s\-\.\']+/', '_', $input);
    $input = \preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $input);
    $input = \preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $input);

    return \preg_replace('/_+/', '_', \strtolower(\trim($input, '_')));
}

/**
 * Capitalize the first letter of each word in a string.
 *
 * Preserves underscores, hyphens, and spaces as delimiters,
 * and capitalizes the letter that follows each one.
 *
 * @param string $input The input string to convert.
 *
 * @return string Return the input string with the first character of each word capitalized.
 * 
 * @example - Example:
 * ```php
 * echo uppercase_words('hello world'); // Hello Wold
 * echo uppercase_words('hello-world'); // Hello-Wold
 * ```
 */
function uppercase_words(string $input): string
{
    if($input === ''){
        return '';
    }

    $input = \strtolower($input);
    
    if (\strpbrk($input[0], '_- ') === false) {
        $input[0] = \strtoupper($input[0]);
    }

    return \preg_replace_callback(
        '/([-_ ])+(\w)/',
        fn($matches) => $matches[1] . \strtoupper($matches[2]),
        $input
    );
}

/**
 * Check whether any given values are considered empty.
 *
 * Unlike PHP's native empty(), this function treats `0` and `'0'`
 * as non-empty values.
 *
 * Considered empty:
 * - null
 * - negative numeric values
 * - empty strings after trimming
 * - empty arrays
 * - empty objects
 * - empty Countable objects
 *
 * @param mixed ...$values Values to evaluate.
 *
 * @return bool Returns true if any value is considered empty, false otherwise.
 * @see \empty()
 *
 * @example - Examples:
 * ```php
 * is_empty(0);              // false
 * is_empty('0');            // false
 * is_empty([1]);            // false
 * is_empty(-3);             // false
 * is_empty(null);           // true
 * is_empty('');             // true
 * is_empty('   ');          // true
 * is_empty([]);             // true
 * is_empty((object)[]);     // true
 * is_empty(new ArrayObject);// true
 * ```
 */
function is_empty(mixed ...$values): bool
{
    if ($values === []) {
        return true;
    }

    foreach ($values as $value) {
        if ($value === null || $value === [] || $value === (object)[]) {
            return true;
        }

        if (\is_string($value) && \trim($value) === '') {
            return true;
        }

        if (\is_array($value) && $value === []) {
            return true;
        }

        if (\is_object($value) && \get_object_vars($value) === []) {
            return true;
        }

        if ($value instanceof \Countable && \count($value) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Converts text characters in a string to HTML entities. 
 * 
 * This is useful for safely displaying user input in HTML without risking XSS attacks.
 * 
 * @param string $text A string containing the text to be processed.
 * 
 * @return string Return the processed text with HTML entities.
 */
function text2html(?string $text): string
{ 
    if($text === '' || $text === null || \trim($text) === ''){
        return '';
    }

    return \htmlspecialchars($text, \ENT_QUOTES|\ENT_HTML5);
}

/**
 * Convert newlines in a string to HTML line breaks.
 * 
 * This is useful for displaying text in an HTML context/textarea while preserving the original line breaks.
 * 
 * @param string|null $text A string containing the text to be processed.
 * 
 * @return string Return the processed text with newlines converted to HTML line breaks.
 */
function nl2html(?string $text): string
{
    $text ??= '';

    return (\trim($text) === '') 
        ? '' 
        : \str_replace(
            ["\n", "\r\n", '[br/]', '<br/>', "\t"], 
            ["&#13;&#10;", "&#13;&#10;", "&#13;&#10;", "&#13;&#10;", "&#09;"], 
            $text
        );
}

/**
 * Checks if a given string is valid UTF-8.
 *
 * @param string $input The string to check for UTF-8 encoding.
 * 
 * @return bool Returns true if the string is UTF-8, false otherwise.
 */
function is_utf8(string $input): bool 
{
    if($input === ''){
        return true;
    }

    static $mbstring = null;
    $mbstring ??= \function_exists('mb_check_encoding');

    if($mbstring){
        return \mb_check_encoding($input, 'UTF-8');
    }

    return \preg_match('//u', $input) === 1;
}

/**
 * Checks if a given string contains an uppercase letter.
 *
 * @param string $string The string to check uppercase.
 * 
 * @return bool Returns true if the string has uppercase, false otherwise.
 */
function has_uppercase(string $string): bool 
{
    for ($i = 0; $i < \strlen($string); $i++) {
        if (\ctype_upper($string[$i])) {
            return true;
        }
    }

    return false;
}

/**
 * Find the matching closing delimiter position in a string.
 *
 * This method scans a string from the given offset and returns the position
 * of the matching closing character for the first opening character found.
 * Nested blocks are handled correctly using depth tracking.
 *
 * @param string $input The string to scan.
 * @param string $start The opening delimiter to match (default: `(`).
 * @param string $end The closing delimiter to match (default: `)`).
 * @param int $offset Optional position to start searching from.
 *                         If null, search starts from the beginning.
 *
 * @return int|false Returns the matching closing delimiter position, or false if not found.
 * @throws InvalidArgumentException If the start or end delimiter is not a single character.
 *
 * @example - Find SQL block end:
 * ```php
 * $sql = 'WITH users AS (SELECT * FROM users WHERE id IN (1,2,3))';
 *
 * $end  = str_matching_delimiter($sql, '(', ')');
 * ```
 *
 * @example - Find JSON-like object block:
 * ```php
 * $text = '{ "user": { "name": "John" } }';
 *
 * $end = str_matching_delimiter($text, '{', '}');
 * ```
 */
function str_matching_delimiter(
    string $input,
    string $start,
    string $end,
    int $offset = 0,
): int|bool 
{
    if (strlen($start) !== 1 || strlen($end) !== 1) {
        throw new InvalidArgumentException(
            'Start and end delimiters must be single characters.'
        );
    }

    $open = strpos($input, $start, $offset);

    if ($open === false) {
        return false;
    }

    $depth  = 0;
    $length = strlen($input);

    for ($i = $open; $i < $length; $i++) {
        $char = $input[$i];

        if ($char === $start) {
            $depth++;
            continue;
        }

        if ($char === $end) {
            $depth--;

            if ($depth === 0) {
                return $i;
            }
        }
    }

    return false;
}

/**
 * Get the length of a string in characters, considering multibyte encodings.
 *
 * @param string $content The string to measure.
 * @param string|null $charset Character encoding (default: app.mb.encoding or 'UTF-8').
 *
 * @return int Return the number of characters in the string.
 */
function string_length(string $content, ?string $charset = null): int
{
    if ($content === '') {
        return 0;
    }

    $charset ??= \env('app.mb.encoding', 'utf-8');
    $charset = \strtolower(\trim($charset));
    
    return match ($charset) {
        'utf-8', 'utf8' => \mb_strlen($content, 'UTF-8'),
        'iso-8859-1', 'latin1', 'windows-1252' => \strlen($content),
        default => \mb_strlen($content, $charset) ?: \strlen($content)
    };
}