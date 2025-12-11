<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova\Debugger; 

use \PhpToken;
use \ReflectionClass;
use Luminova\Runtime;
use Luminova\Config\Env;
use Luminova\Utility\Converter;
use Luminova\Storage\Filesystem;
use Luminova\Attributes\AllowIncludes;
use Luminova\Exceptions\RuntimeException;

final class Tracer
{
    /**
     * The point to trigger break.
     * 
     * @var int $breakAt
     */
    private static int $breakAt  = 0;

    /**
     * Registered breakpoints.
     * 
     * @var array<int,?string> $breakpoints
     */
    private static array $breakpoints = [];

    /**
     * Cache scanned includes.
     *
     * @var array<string,bool> $scanned
     */
    private static array $scanned = [];

     /**
     * Forbidden PHP language constructs.
     *
     * @var int[] FORBIDDEN_INCLUDE_TOKENS
     */
    private const FORBIDDEN_INCLUDE_TOKENS = [
        T_INCLUDE,
        T_INCLUDE_ONCE,
        T_REQUIRE,
        T_REQUIRE_ONCE,
    ];

    /**
     * Retrieves the file and line number from the call stack.
     *
     * - When `$depth === 0`, returns the location where this method was called.
     * - When `$depth > 0`, returns the location at the given stack depth.
     *
     * @param int $depth Call stack depth:
     *        - `0` = current method call frame.
     *        - `1` = immediate caller.
     *        - Higher values trace further up the call stack.
     *        Negative values are treated as `0`.
     *
     * @return array{0:?string,1:int} A two-element array containing:
     *         - `0`: File path of the resolved stack frame, or `null` if unavailable.
     *         - `1`: Line number of the resolved stack frame, or `0` if unavailable.
     */
    public static function trace(int $depth = 0): array
    {
        $depth = max(0, $depth);
        $limit = $depth + 1;

        $trace = debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS,
            $limit
        );

        $frame = $trace[$depth]
            ?? $trace[$limit]
            ?? [];

        return [
            $frame['file'] ?? null,
            (int) ($frame['line'] ?? 0),
        ];
    }

    /**
     * Set the active breakpoint identifier.
     *
     * When a matching breakpoint is triggered via {@see break()},
     * execution will stop and output debug data.
     *
     * @param int $at Breakpoint identifier to activate.
     *
     * @return void
     *
     * @example - Example:
     * ```php
     * Tracer::break(2);
     * // Only Tracer::breakpoint(2, ...) will trigger a stop
     * ```
     */
    public static function break(int $at): void
    {
        self::$breakAt = $at;
    }

    /**
     * Get all registered breakpoints.
     *
     * Returns a map of breakpoint identifiers to their labels.
     *
     * @return array<int,string|null> Return all registered breakpoints (index → label).
     *
     * @example - Example:
     * ```php
     * Tracer::breakpoint(1, label: 'Before parsing');
     * Tracer::breakpoint(2, label: 'After parsing');
     *
     * print_r(Tracer::getBreakpoints());
     * // [1 => 'Before parsing', 2 => 'After parsing']
     * ```
     */
    public static function getBreakpoints(): array
    {
        return self::$breakpoints;
    }

    /**
     * Retrieves the last debug backtrace from the shared error context.
     * 
     * This method accesses a shared memory `$trace` to retrieve
     * the stored debug backtrace. If the backtrace is not set, it returns an empty array.
     * 
     * @return array Return the debug backtrace or an empty array if not available.
     * @deprecated Use Runtime::lastErrorBacktrace() instead.
     */
    public static function getBacktrace(): array 
    {
        return Runtime::lastErrorBacktrace();
    }

    /**
     * Stores the last debug backtrace in the shared error context.
     * 
     * 
     * Can either replace the current backtrace or prepend to it.
     * 
     * @param array $backtrace Array of backtrace information.
     * @param bool $push If true (default), prepends to the existing backtrace.
     * 
     * @return void
     * @deprecated Use Runtime::setLastBacktrace() instead.
     */
    public static function setBacktrace(array $backtrace, bool $push = true): void 
    {
        Runtime::setLastBacktrace($backtrace, $push);
    }

    /**
     * Stores the last error code in the shared error context.
     * 
     * @param string|int $code The last error code value.
     * 
     * @return void
     * @deprecated Use Runtime::setLastErrorCode() instead.
     */
    public static function setLastErrorCode(string|int $code): void 
    {
        Runtime::setLastErrorCode($code);
    }

    /**
     * Retrieves the last stored error code or a default value.
     * 
     * @param string|int $default Default error code if none was stored (default: `E_ERROR`).
     * 
     * @return string|int Return the last stored error code, or the provided default.
     * 
     * @deprecated Use Runtime::LastErrorCode() instead.
     */
    public static function getLastErrorCode(string|int $default = E_ERROR): string|int
    {
        return Runtime::LastErrorCode($default);
    }

    /**
     * Clears the shared last error code and back tracer.
     * 
     * @return void
     * @internal
     * 
     * @deprecated Use Runtime::clearLastError() instead.
     */
    public static function resetLastError(): void 
    {
        Runtime::clearLastError();
    }

    /**
     * Register and optionally trigger a breakpoint.
     *
     * If the given identifier matches the active breakpoint set via {@see break()},
     * this method will:
     * - Execute the optional callback
     * - Capture file and line of invocation
     * - Return or output debug information
     * - Terminate execution (unless return mode is enabled)
     *
     * @param int  $identifier Unique breakpoint identifier.
     * @param mixed|null $data  Optional data to inspect.
     * @param string|null $label Optional label for easier identification.
     * @param (callable(array $debug, array $points):void)|null $onBreak Optional callback executed before stopping.
     * @param bool $return   If true, returns debug info instead of exiting.
     *
     * @return array<string,mixed>|null Returns debug info when triggered in return mode, otherwise null.
     *
     * @example - Basic usage:
     * ```php
     * Tracer::break(1);
     *
     * Tracer::breakpoint(1, $users, 'User list');
     * // Execution stops here and dumps data
     * ```
     *
     * @example - Multiple checkpoints:
     * ```php
     * Tracer::breakpoint(1, $users, 'Before processing');
     * $parsed = process($users);
     * Tracer::breakpoint(2, $parsed, 'After processing');
     * ```
     *
     * @example - Return instead of exit:
     * ```php
     * Tracer::break(1);
     * $point = Tracer::breakpoint(1, $data, 'Inspect', return: true);
     *
     * if ($point !== null) {
     *     print_r($point);
     * }
     * ```
     *
     * @example - Using callback:
     * ```php
     * Tracer::breakpoint(1, $data, 'DB State', function (array $debug, array $points): void {
     *     // e.g. rollback transaction or log state
     * });
     * ```
     */
    public static function breakpoint(
        int $identifier,
        mixed $data = null,
        ?string $label = null,
        ?callable $onBreak = null,
        bool $return = false
    ): ?array 
    {
        self::$breakpoints[$identifier] = $label;

        if (self::$breakAt !== $identifier) {
            return null;
        }

        $frame = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];
        $debug = [
            'identifier' => $identifier,
            'file'       => $frame['file'] ?? null,
            'line'       => $frame['line'] ?? null,
            'label'      => $label,
            'data'       => $data,
        ];

        if ($onBreak !== null) {
            $onBreak($debug, self::$breakpoints);
        }

        if ($return) {
            return $debug;
        }

        exit(var_export($debug, true));
    }

    /**
     * Creates a syntax-highlighted version of a PHP file.
     * 
     * @param string $file File to highlight.
     * @param int $line Line number.
     * @param int $lines Maximum number of lines.
     * 
     * @return string|bool Return html highlight of the passed file.
     */
    public static function highlight(string $file, int $line, int $lines = 15): string|bool
    {
        if ($file === '' || ! is_readable($file)) {
            return false;
        }

        // Set our highlight colors:
        self::highlightColor();
        $source = Filesystem::contents($file);

        if($source === false){
            return false;
        }
          
        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $source = explode("\n", highlight_string($source, true));
        $source = str_replace('<br />', "\n", $source[1]);
        $source = explode("\n", str_replace("\r\n", "\n", $source));

        // Get just the part to show
        $start = max($line - (int) round($lines / 2), 0);
        $source = array_splice($source, $start, $lines, true);
        $number = '% ' . strlen((string) ($start + $lines)) . 'd';

        $code = '';
        $spans = 0;

        foreach ($source as $index => $row) {
            $spans += substr_count($row, '<span') - substr_count($row, '</span');
            $row = str_replace(["\r", "\n"], ['', ''], $row);
            $lineNumber = ($index + $start + 1);

            if ($lineNumber === $line) {
                preg_match_all('#<[^>]+>#', $row, $tags);
                
                $editor = self::getIdeEditorUri($file, $lineNumber);
                $code .= sprintf(
                    "<span class=\"line highlight\"><span class=\"number\">{$number}</span>%s %s\n</span>%s",
                    $lineNumber,
                    "<a href=\"{$editor}\" class=\"line-editable\"></a>",
                    strip_tags($row),
                    implode('', $tags[0])
                );
            } else {
                $code .= sprintf(
                    "<span class=\"line\"><span class=\"number\">{$number}</span> %s\n",
                    $lineNumber, 
                    $row
                );
                $spans++;
            }
        }

        if ($spans > 0) {
            $code .= str_repeat('</span>', $spans);
        }

        return '<pre><code>' . $code . '</code></pre>';
    }

    /**
     * Dump one or more values for debugging purposes.
     *
     * Displays variables in a readable format without terminating execution.
     * Output is automatically formatted for both CLI and HTTP environments.
     *
     * The dump includes:
     * - Caller file and line number.
     * - Peak memory usage.
     * - All supplied values.
     *
     * For web requests, output is rendered inside a styled container for
     * improved readability. For CLI execution, output is displayed as plain text.
     *
     * The caller location is resolved automatically by skipping framework
     * debugging helpers from the call stack.
     * 
     * @param mixed ...$vars One or more values to dump.
     *
     * @return void
     * @see dd() global helper to dump values and terminate execution.
     */
    public static function dump(mixed ...$vars): void
    {
        $caller = self::getDumpCaller();

        $file = $caller['file'] ?? 'unknown';
        $line = $caller['line'] ?? 0;
        $time = date('Y-m-d H:i:s');

        $memory = Converter::toUnit(
            memory_get_peak_usage(true),
            withName: true
        );

        if (PHP_SAPI === 'cli') {
            $fileLine = "Dumped at: {$file}:{$line}";

            echo PHP_EOL;
            echo "Time: {$time}" . PHP_EOL;
            echo $fileLine . PHP_EOL;
            echo "Peak Memory: {$memory}" . PHP_EOL;
            echo str_repeat('-', strlen($fileLine) + 5) . PHP_EOL;

            foreach ($vars as $var) {
                var_dump($var);
                echo PHP_EOL;
            }

            return;
        }

        $file = htmlspecialchars(
            $file,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        ob_start();

        echo <<<HTML
        <style>
        .dd-container{font-family:monospace;padding:15px;margin:10px;border:1px solid #ddd;background:#fff;}
        .dd-meta{color:#666;margin-bottom:10px;}
        .dd-dump{background:#f7f7f7;padding:10px;overflow:auto;}
        .dd-pre{margin:0;white-space:pre-wrap;word-break:break-word;}
        </style>
        <div class="dd-container">
            <div class="dd-meta">
                <strong>Time:</strong> {$time}<br>
                <strong>Dumped at:</strong> {$file}:{$line}<br>
                <strong>Peak Memory:</strong> {$memory}
            </div>
        HTML;

        foreach ($vars as $var) {
            echo '<div class="dd-dump"><pre class="dd-pre">';
            var_dump($var);
            echo '</pre></div>';
        }

        echo '</div>';

        ob_end_flush();
    }

    /**
     * Asserts that the specified class source does not contain direct include or
     * require statements.
     *
     * This validation runs only in development mode. The source file is scanned
     * once per request and cached to avoid repeated tokenization.
     *
     * @param object|string $source Class instance, fully qualified class name or filename.
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     */
    public static function assertNoIncludes(object|string $source): void
    {
        $reflection = new ReflectionClass($source);
        $file = $reflection->getFileName();

        if ($reflection->getAttributes(AllowIncludes::class)) {
            self::$scanned[$file] = true;
            return;
        }

        $info = [];

        try{
            self::assertTokens($file, self::FORBIDDEN_INCLUDE_TOKENS, $info);
        } catch(RuntimeException $e){
            $e = new RuntimeException(sprintf(
                'Direct include/require statements are not allowed in "%s". 
                Use \\Luminova\\Funcs\\import() instead.',
                $info['file']
            ));

            [$file, $line] = [$info['file'], $info['line']] + self::trace(1);

            throw $e->setFile($file)
                ->setLine($line);
        }
    }

    /**
     * Asserts that the specified PHP source does not contain any of the given
     * forbidden tokens.
     *
     * Each source file is scanned only once per request and cached after a
     * successful validation to avoid repeated tokenization.
     *
     * @param object|string $class Class instance, fully qualified class name,
     *                             or source filename.
     * @param int[] $tokens Forbidden PHP token IDs.
     * @param array{file:string,line:int,name:string}|null &$info Details of the
     *        detected token when validation fails.
     *
     * @throws \ReflectionException If the class cannot be reflected.
     * @throws RuntimeException If a forbidden token is found.
     */
    public static function assertTokens(
        object|string $class,
        array $tokens,
        ?array &$info = null
    ): void 
    {
        $info = null;
        $file = (is_string($class) && is_file($class))
            ? $class
            : (new ReflectionClass($class))->getFileName();

        if ($file === false || $file === null || isset(self::$scanned[$file])) {
            return;
        }

        $source = file_get_contents($file);

        if ($source === false) {
            return;
        }

        foreach (PhpToken::tokenize($source) as $token) {
            if ($token->isIgnorable() || !$token->is($tokens)) {
                continue;
            }

            $name = $token->getTokenName();

            $info = [
                'file' => $file,
                'line' => $token->line,
                'name' => $name,
            ];

            throw new RuntimeException(sprintf(
                'Forbidden token "%s" detected in "%s" on line %d.',
                $name,
                $file,
                $token->line
            ));
        }

        self::$scanned[$file] = true;
    }

    /**
     * Build an IDE deep-link for opening a file at a specific line.
     *
     * Detects the configured IDE from the debug environment and returns the
     * correct URI scheme used by editors like VS Code, PhpStorm, Sublime,
     * Atom, and others.
     *
     * If a file path is provided, the returned link opens that file at the
     * given line number. If no file is provided, only the IDE scheme is
     * returned.
     *
     * @param string|null $file Optional absolute file path to open.
     * @param int|string  $line Line number to focus on (defaults to 1).
     *
     * @return string IDE URI scheme or full deep-link.
     */
    public static function getIdeEditorUri(?string $file = null, string|int $line = 1): string 
    {
        $ide = defined('APP_BOOTED') ? strtolower(Env::get('debug.coding.ide', 'vscode')) : 'vscode';
        $scheme = match ($ide) {
            'phpstorm'  => 'phpstorm://open?file=',
            'sublime'   => 'sublimetext://open?url=file:',
            'vscode'    => 'vscode://file',
            'idea'      => 'idea//open?file=',
            'mvim'      => 'mvim://open/?url=',
            'atom'      => 'atom://core/open/file?filename=',
            'txmt'      => 'txmt://open?url=file://',
            'vscode-remote' => 'vscode://vscode-remote/',
            default     => "{$ide}://open?=file",
        };

        return $file ? $scheme . urlencode($file) . ":{$line}" : $scheme;
    }

    /**
     * Resolves the actual caller of the dump operation.
     *
     * Framework debugging helpers are skipped so that the returned frame
     * points to the application code that invoked `dump()`, `dd()`, or `ddd()`.
     *
     * @return array{file?: string, line?: int, function?: string, class?: string}
     */
    private static function getDumpCaller(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $previous = [];

        foreach ($trace as $frame) {
            $function = $frame['function'] ?? '';
            $class = $frame['class'] ?? '';

            if ($class === self::class && $function === 'getDumpCaller') {
                continue;
            }

            if (
                ($class === self::class && $function === 'dump') ||
                ($class === '' && in_array($function, ['dump', 'dd', 'ddd'], true))
            ) {
                $previous = $frame;
                continue;
            }

             return $previous;
        }

        return [];
    }

    /**
     * Initialize ini_set highlight.
     * 
     * @param bool $forDarkTheme Wether for dark theme.
     * 
     * @return void
     */
    private static function highlightColor(bool $forDarkTheme = true): void
    {
        if (!function_exists('ini_set')) {
            return;
        }

        if ($forDarkTheme) {
            ini_set('highlight.comment', '#767a7e; font-style: italic');
            ini_set('highlight.default', '#c7c7c7');
            ini_set('highlight.html', '#06B');
            ini_set('highlight.keyword', '#f1ce61;');
            ini_set('highlight.string', '#869d6a');
        } else {
            ini_set('highlight.comment', '#6a737d; font-style: italic');
            ini_set('highlight.default', '#24292e');
            ini_set('highlight.html',    '#0550ae');
            ini_set('highlight.keyword', '#d73a49');
            ini_set('highlight.string',  '#032f62');
        }
    }
}