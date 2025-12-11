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

use \Luminova\Boot;
use \Luminova\AI\AI;
use \App\Application;
use \App\Config\Files;
use \Luminova\Luminova;
use \Luminova\Template\View;
use \Luminova\Logger\Logger;
use \Luminova\Cookies\Cookie;
use \Luminova\Http\Network\IP;
use \Luminova\Security\Escaper;
use \Luminova\Sessions\Session;
use \Luminova\Template\Response;
use \Luminova\Storage\Filesystem;
use \Luminova\Utility\{MIME, Helpers};
use \Luminova\Promise\{Rejected, Fulfilled};
use \Luminova\Cache\{FileCache, MemoryCache};
use \Luminova\Components\{Async, String\Listifier};
use \Luminova\Foundation\Module\{Factory, Service};
use \Luminova\Http\{Request, HttpStatus, UserAgent};
use \Luminova\Interface\{
    Awaitable,
    AIClientInterface,
    LazyObjectInterface,
    InputValidationInterface,
    RequestInterface,
    ViewResponseInterface,
    SessionManagerInterface
};
use \Luminova\Exceptions\{
    LuminovaException,
    FileException,
    ClassException,
    RuntimeException,
    UnexpectedValueException,
    InvalidArgumentException,
    Http\ResponseException
};

/**
 * Build an absolute path from the application root directory.
 *
 * This helper returns a path relative to `APP_ROOT`, with optional
 * subdirectory and filename appended. The root directory is already
 * resolved during application bootstrap.
 *
 * @param string|null $path Optional subdirectory path from the root (e.g., 'writeable/logs').
 * @param string|null $filename Optional filename to append (e.g., 'debug.log').
 *
 * @return string Returns an absolute path based on `APP_ROOT`.
 *
 * @example Usage:
 *
 * ```php
 * $logPath = root('writeable/logs', 'debug.log');
 *
 * // Example outputs:
 * // Linux:  /var/www/app/writeable/logs/debug.log
 * // macOS:  /Applications/XAMPP/htdocs/app/writeable/logs/debug.log
 * // Windows: C:\wamp64\www\app\writeable\logs\debug.log
 * ```
 *
 * > **Note:**
 * > - Path separators are normalized automatically.
 * > - Avoid passing a filename as `$path`, as it will be treated as a directory.
 * > - This function does not resolve or validate the root directory.
 */
function root(?string $path = null, ?string $filename = null): string
{
    $path = ($path && $path  !== \DIRECTORY_SEPARATOR)
        ? \trim(\str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $path), \TRIM_DS) . \DIRECTORY_SEPARATOR
        : '';

    if ($filename) {
        $path .= \ltrim($filename, \TRIM_DS);
    }

    return APP_ROOT . $path;
}

/**
 * Get the application instance.
 *
 * Returns the current shared application instance or creates a new one.
 *
 * - When `$shared` is true (default), the shared application instance is returned.
 * - When `$shared` is false, a new application instance is created via the kernel.
 * - If `$new` is provided, it replaces the current shared application instance.
 *
 * This helper does not pass arguments to the application constructor.
 * Application creation and configuration are handled internally by the kernel.
 *
 * @param bool $shared Whether to return the shared instance (default: true).
 * @param Application|null $new Optional application instance to replace the shared one.
 *
 * @return Application Returns a shared or newly created application instance.
 *
 * @see https://luminova.ng/docs/0.0.0/foundation/application
 *
 * @example - Create a new application instance:
 * ```php
 * use function Luminova\Funcs\app;
 *
 * $app = app(shared: false);
 * ```
 *
 * @example - Replace the shared application instance:
 * ```php
 * app(new: $customApp);
 * ```
 */
function app(bool $shared = true, ?Application $new = null): Application
{
    if (!$shared) {
        return Luminova::kernel(false)->getApplication();
    }

    return ($new === null)
        ? Luminova::kernel(true)->getApplication()
        : Luminova::kernel(true)->getApplication()->setInstance($new);
}

/**
 * Retrieve an HTTP request object.
 * 
 * Returns a shared or new instance of the HTTP request handler for accessing
 * headers, query parameters, body data, and other request-specific information.
 *
 * @param bool $shared Whether to return a shared instance (default: true).
 * 
 * @return Request<RequestInterface,LazyObjectInterface> Returns instance of HTTP request class.
 * @see https://luminova.ng/docs/0.0.0/http/request
 */
function request(bool $shared = true): RequestInterface 
{
    if (!$shared) {
        return new Request();
    }

    return Request::getInstance();
}

/**
 * Ask a question using the configured AI model.
 *
 * This helper provides two behaviors:
 *
 * 1. If `$prompt` is provided, the message is sent immediately and the
 *    response is returned as an array.
 * 2. If `$prompt` is `null`, the configured AI instance is returned so
 *    additional methods can be chained before sending a request.
 *
 * The `$model` argument accepts either a predefined `Model` enum or a
 * model name string. Using the enum guarantees the model is valid.
 *
 * @param string|null $prompt Optional message to send to the AI model.
 * @param Luminova\AI\Model|BackedEnum|string $model Model enum or model name. Default: `gpt-4.1-mini`.
 * @param array<string,mixed> $options Optional request options passed to `AI::message()`.
 *
 * @return \Luminova\AI\Client\OpenAI|\Luminova\AI\Client\Anthropic|Luminova\AI\Client\Ollama|AIClientInterface|array Returns the response array when `$prompt` is provided,
 *          otherwise returns the configured AI client instance.
 * @throws AIException if error occur.
 *
 * @example Send a simple message
 * ```php
 * use function Luminova\Funcs\ask;
 *
 * ask('Hello!');
 * ```
 *
 * @example Use a specific model
 * ```php
 * use Luminova\AI\Model;
 * 
 * ask('Explain PHP enums', Model::O3);
 * ```
 *
 * @example Chain additional methods
 * ```php
 * use Luminova\AI\Model;
 * 
 * ask(null, Model::GPT_4_1)
 *     ->temperature(0.2)
 *     ->message('Explain dependency injection in PHP');
 * ```
 */
function ask(
    ?string $prompt = null, 
    object|string $model = 'gpt-4.1-mini', 
    array $options = []
): AIClientInterface|array
{
    $ai = AI::getInstance()->setModel($model);

    return ($prompt === null)
        ? $ai->getClient()
        : $ai->message($prompt, $options);
}

/**
 * Create a view response object.
 *
 * Returns a new or shared instance of the view response handler, used to send
 * JSON, HTML, Steam, Download or other content formats back to the client.
 *
 * @param int $status HTTP status code (default: 200).
 * @param array<string,mixed>|null $headers Optional response headers (default: null).
 * @param bool $shared Whether to return a shared instance (default: true).
 *
 * @return Response<ViewResponseInterface> Return instance of view response object.
 * @see https://luminova.ng/docs/0.0.0/templates/response
 *
 * @example Send a JSON response:
 * ```php
 * response()->json(['status' => 'OK', 'message' => 'Done!']);
 * ```
 */
function response(int $status = 200, ?array $headers = null,  bool $shared = true): ViewResponseInterface
{
    $headers ??= [];

    if (!$shared) {
        return new Response($status, $headers);
    }

    return Response::getInstance($status, $headers)
        ->setStatus($status)
        ->headers($headers);
}

/**
 * Immediately terminate the current request with an HTTP error response.
 *
 * This function halts further execution and returns a response based on the given HTTP status code and optional message.
 * 
 * If `$message` is:
 * - `string`: Treated as a plain message or rendered view (based on `$type`).
 * - `array|object`: Returned as a JSON response.
 * - `null`: Sends an empty response with the given status code and headers.
 * 
 * If `$type` is set, it forces the response format:
 * - `xml`: Abort with an XML response.
 * - `text`: Abort with a plain text response.
 * - `html`: Abort with a raw HTML content.
 * - `null` or unrecognized: Attempt to detect content from $header (`Content-Type`) or $message (`body`).
 * 
 * @param int $status HTTP status code (e.g., 404, 403, 500).
 * @param string|array|object|null $message Optional message string, array (JSON), or object to return.
 * @param array $headers Optional HTTP headers to include in the response.
 * @param string|null $type Optional forced response type: `json`, `xml`, `text`, or `html`.
 * 
 * @return never This function does not return; it ends the request.
 * @throws ResponseException If error occur while sending abort response.
 *
 * @example - Examples:
 * 
 * ```php
 * abort(404); // Sends 404 with no message.
 * abort(403, 'Access denied.'); // Sends plain text.
 * abort(422, ['error' => 'Validation failed']); // Sends JSON.
 * abort(500, '<h1>Server Error</h1>', [], 'html'); // Sends HTML.
 * ```
 */
function abort(
    int $status = 500,
    string|array|object|null $message = null,
    array $headers = [],
    ?string $type = null
): never 
{
    $response = response($status, $headers);

    if (!$message) {
        $response->send();
        exit;
    }

    if (\is_array($message) || \is_object($message)) {
        $response->json($message);
        exit;
    }

    $message = (string) $message;

    match ($type) {
        'xml'   => $response->xml($message),
        'text'  => $response->text($message),
        'html'  => $response->html($message),
        default => $response->render($message),
    };

    exit;
}

/**
 * Redirect the client to a different URI.
 * 
 * This function supports both standard `Location` header and `Refresh` header-based redirection.
 *
 * @param string $uri Target URL to redirect to.
 * @param string|null $method Redirection method: 'refresh' or null for standard.
 * @param int $status HTTP redirect status code (default: 302).
 *
 * @return never
 * 
 * @example - Basic redirect (302 Found):
 * 
 * ```php
 * redirect('/home');
 * ```
 *
 * @example - Redirect with 301 (Moved Permanently):
 * 
 * ```php
 * redirect(uri: '/new-url', status: 301);
 * ```
 *
 * @example - Redirect using 'Refresh' header (useful for IIS):
 * 
 * ```php
 * redirect('/dashboard', 'refresh');
 * ```
 */
function redirect(string $uri, ?string $method = null, int $status = 302): never
{
    response($status)->redirect($uri, $method);
    exit;
}

/**
 * Redirect the user to the previous page.
 *
 * Attempts to use the `HTTP_REFERER` header to go back. If unavailable,
 * it falls back to the provided URI or `'/'` if none is given.
 *
 * @param string|null $fallback URI to redirect to if no referer is found.
 * @param string|null $method Redirection method (`refresh` or `null` for default).
 * @param int $status HTTP status code for the redirect (default: 302).
 *
 * @return never
 */
function back(?string $fallback = null, ?string $method = null, int $status = 302): never
{
    redirect($_SERVER['HTTP_REFERER'] ?? $fallback ?? '/', $method, $status);
    exit;
}

/**
 * Template view response helper.
 *
 * Creates or reuses a View instance and prepares it to render
 * the given template using the specified content type.
 *
 * This helper does not render immediately. 
 * It returns a View instance so you can chain rendering or return template content.
 *
 * @param string $template Template file name or identifier.
 * @param string $type Template content type (default: View::HTML).
 *
 * @return View Returns the prepared view instance for rendering or content output.
 *
 * @example - Render and send output:
 * ```php
 * use function Luminova\Funcs\view;
 *
 * return view('index')
 *     ->render(['name' => 'Peter'], 200);
 * ```
 *
 * @example - Return rendered contents:
 * ```php
 * use function Luminova\Funcs\view;
 *
 * return view('index')
 *     ->contents(['name' => 'Peter'], 200);
 * ```
 */
function view(string $template, string $type = View::HTML): View
{
    static $view = null;

    if (!$view instanceof View) {
        $view = new View(Boot::application());
    }

    return $view->view($template, $type);
}

/**
 * Generate a URL to a route or file within the application.
 *
 * Builds either an absolute URL using `APP_URL` or a relative URL
 * based on the current request depth.
 *
 * If `$view` is null or empty, the base path is returned.
 *
 * @param string|null $uri The route, view name, or file path.
 * @param bool $absolute Whether to return an absolute URL (default: false).
 * @param int $depth  Directory depth used for relative URLs (default: `0`).
 *
 * @return string Returns the generated URL.
 *
 * @example - Examples:
 * ```php
 * href();                        // "/"
 * href('about');                 // "/about"
 * href('admin/dashboard', true); // "https://example.com/admin/dashboard"
 * ```
 */
function href(?string $uri = null, bool $absolute = false, int $depth = 0): string 
{
    $uri = ($uri === null) ? '' : \ltrim($uri, '/');

    if($absolute){
        return \rtrim(\APP_URL, '/') . '/' . $uri;
    }

    static $relative = null;

    if($relative === null){
        $relative = View::fromRelativeRoot(depth: $depth);
    }

    return $relative . $uri;
}

/**
 * Generate a URL to a file in the public `assets/` directory.
 *
 * Builds a relative or absolute URL pointing to an asset such as
 * CSS, JavaScript, images, or other public files.
 *
 * If `$filename` is null or empty, the base `assets/` path is returned.
 *
 * @param string|null $filename Asset path or file name (e.g. "css/app.css").
 * @param bool $absolute Whether to return an absolute URL (default: false).
 * @param int $depth Directory depth for relative URLs (default: 0).
 *
 * @return string Return the generated URL to the assets file or base assets folder if no filename is provided.
 *
 * @example - Examples:
 * ```php
 * asset('css/style.css');   // "/assets/css/style.css"
 * asset();                  // "/assets/"
 * asset('css');             // "/assets/css/"
 * asset('js/app.js', true); // "https://example.com/assets/js/app.js"
 * ```
 */
function asset(?string $filename = null, bool $absolute = false, int $depth = 0): string
{
    $filename = ($filename === null) ? '' : \ltrim($filename, '/');

    return href("assets/{$filename}", $absolute, $depth);
}

/**
 * Safely call a PHP function with arguments.
 *
 * This helper:
 * - Detects functions disabled via PHP `disable_functions`.
 * - Resolves functions from the global namespace when needed.
 * - Uses `function_exists_cached()` to avoid repeated existence checks.
 *
 * @param callable-string $function Name of the function to call.
 * @param mixed ...$arguments Arguments to pass to the function.
 *
 * @return mixed Returns the function result.
 *
 * @throws RuntimeException If the function is disabled or does not exist.
 *
 * @see https://luminova.ng/docs/0.0.0/foundation/functions
 */
function func(string $function, mixed ...$arguments): mixed
{
    static $disables = null;

    $function = \ltrim($function, '\\');

    if ($disables === null) {
        $disables = \array_map(
            'trim', \explode(',', \ini_get('disable_functions') ?: '')
        );
    }

    $isDisabled = \in_array($function, $disables, true);

    if (!$isDisabled) {
        $global = "\\{$function}";

        if (function_exists_cached($global)) {
            return $global(...$arguments);
        }

        if (function_exists_cached($function)) {
            return $function(...$arguments);
        }
    }

    throw new RuntimeException(\sprintf($isDisabled 
        ? 'Function "%s" is disabled by PHP configuration.'
        : 'Function "%s" does not exist.',  
        $function
    ));
}

/**
 * Wrap a task in an Awaitable Future for asynchronous execution.
 *
 * This helper creates a Future without running the task immediately.
 * The returned Awaitable can later be passed to `await()` to run and
 * retrieve the result. Supports optional background detachment.
 *
 * @param callable $task The task to run asynchronously.
 * @param bool $detachProcess If true, the task runs in a detached background process.
 *
 * @return Awaitable An awaitable Future representing the task.
 * @throws InvalidArgumentException If the task type is invalid.
 *
 * @see Async::async() - Core async implementation.
 * @see Async::background() - Core async detached implementation.
 *
 * @example Fiber Async:
 * ```php
 * $future = async(fn() => doSomething());
 * echo $result;
 * ```
 *
 * @example Run in background:
 * ```php
 * $future = async(fn() => doWork(), detachProcess: true);
 * $result = await($future);
 * ```
 */
function async(callable $task, bool $detachProcess = false): Awaitable
{
    return Async::async($task, $detachProcess);
}

/**
 * Wait for an Awaitable task to finish and return its result.
 *
 * This helper strictly accepts an `Awaitable` object. It blocks execution
 * until the task completes, optionally allowing a delay between ticks and
 * enforcing a maximum timeout. If the task fails or times out, an exception is thrown.
 *
 * @param Awaitable $task The awaitable task to run.
 * @param int $timeout Maximum wait time in seconds (0 = unlimited).
 * @param float $delay Delay in seconds between execution checks (default 0.1).
 *
 * @return mixed Return the value returned by the task once it completes.
 *
 * @throws RuntimeException If the task does not complete within the specified timeout.
 * @throws Throwable If the task itself throws an exception during execution.
 *
 * @see Async::async() To create a Future or Awaitable task.
 *
 * @example Example:
 * ```php
 * $future = async(fn() => doSomething());
 * 
 * $result = await($future);
 * echo $result;
 * ```
 */
function await(Awaitable $task, int $timeout = 0, float $delay = 0.1): mixed
{
    return $task->await($timeout, $delay);
}

/**
 * Finish the HTTP response immediately while allowing PHP to continue running.
 *
 * This function attempts to terminate the client response as early as possible
 * while letting PHP execute remaining logic (logging, cache writes, async work).
 *
 * Behavior:
 * - Optionally closes the active session to prevent blocking.
 * - Optionally ignores client aborts.
 * - Uses server-specific request finish functions when available.
 * - Falls back to flushing output buffers safely.
 *
 * Notes:
 * - This is best-effort only. Web servers and proxies may still terminate execution.
 * - Do not rely on this for critical work; use queues or workers instead.
 *
 * @param bool $closeSession Whether to close the active session before finishing the response.
 * @param bool $ignoreAbort Whether to ignore client disconnects (non-CLI only).
 *
 * @return bool Returns true if a response flush attempt was made.
 */
function finish_response(bool $closeSession = true, bool $ignoreAbort = true): bool
{
    if ($closeSession && \session_status() === \PHP_SESSION_ACTIVE) {
        \session_write_close();
    }

    if($ignoreAbort && PHP_SAPI !== 'cli'){
        func('ignore_user_abort', true);
    }

    try {
        return (bool) func('fastcgi_finish_request');
    } catch (\Throwable) {
        try {
            return (bool) func('litespeed_finish_request');
        } catch (\Throwable) {
            while (\ob_get_level() > 0) {
                @\ob_end_flush();
            }

            @\flush();
            return true;
        }
    }
}

/**
 * Import (include or require) a file using either a full path or a virtual path scheme.
 *
 * This function makes it easier to load files from common project folders without typing full paths.
 * You can pass:
 * - A normal file path like `path/to/project/app/Config/main.php`, or
 * - A virtual path like `app:Config/main.php`, which automatically maps to the correct folder.
 *
 * By default, the file is loaded only once (`*_once`), you can change this behavior with the function options.
 *
 * You can also pass an associative array of variables to make available inside the imported file.
 * The array keys become variable names.
 *
 * **Supported Schemes:**
 * 
 * - `app`          Resolves to `root/app/*`
 * - `package`:      Resolves to `root/system/plugins/*`
 * - `system`:       Resolves to `root/system/*`
 * - `view`:         Resolves to `root/resources/Views/*`
 * - `public`:       Resolves to `root/public/*`
 * - `writeable`:    Resolves to `root/writeable/*`
 * - `libraries`:    Resolves to `root/libraries/*`
 * - `resources`:    Resolves to `root/resources/*`
 * - `routes`:       Resolves to `root/routes/*`
 * - `bootstrap`:    Resolves to `root/bootstrap/*`
 * - `bin`:          Resolves to `root/bin/*`
 * - `node`:         Resolves to `root/node/*`
 *
 * @param string $path File path or scheme-based path to import.
 * @param bool $throw If true, throw an exception when the file doesn't exist (default: false).
 * @param bool $once If true, load the file only once using `*_once` (default: true).
 * @param bool $require If true, use `require/require_once`, otherwise use `include/include_once` (default: `true`).
 * @param array<string,mixed> $scope Optional associative array of variables to extract into the file scope.
 *                                   (Keys become variable names.)
 * @param bool $promise Optionally use import with promise resolver (default: false).
 *
 * @return mixed|Luminova\Interface\PromiseInterface Returns the imported file’s return value, 
 *              or null when the file is missing and $throw is false.
 *
 * @throws RuntimeException If file doesn't exist and $throw is true.
 * @throws InvalidArgumentException If `$scope` is not empty and not an associative array (list array given).
 * 
 * @example Import using scheme (recommended):
 * ```php
 * // Resolves to: root('app', 'Config/settings.php')
 * import('app:Config/settings.php');
 * ```
 * 
 * @example Import packages (composer vendor):
 * ```php
 * import('package:brick/math/src/BigNumber.php');
 * ```
 * 
 * @example Import using direct file path:
 * ```php
 * import('app/Config/settings.php');
 * ```
 * 
 * @example Force exception when file is missing:
 * ```php
 * import('app:Config/missing.php', throw: true);
 * ```
 * 
 * @example Use include instead of require:
 * ```php
 * import('system:Bootstrap/init.php', require: false);
 * ```
 * 
 * @example Allow multiple imports of same file:
 * ```php
 * import('routes:api.php', once: false);
 * ```
 */
function import(
    string $path, 
    bool $throw = false, 
    bool $once = true, 
    bool $require = true,
    array $scope = [],
    bool $promise = false
): mixed
{
    if (\preg_match('/^([a-z_]+):(\/?.+)$/i', $path, $matches)) {
        [$_, $scheme, $subPath] = $matches;

        if ($scheme !== 'builds') {
            if ($scheme === 'package' || $scheme === 'view') {
                $path = root(($scheme === 'package') 
                    ? '/system/plugins/' 
                    : '/resources/Views/', 
                    $subPath
                );
            }elseif (\in_array($scheme, Luminova::$systemPaths, true)) {
                $path = root($scheme, $subPath);
            }
        }
    }

    if (!\is_file($path)) {
        if ($throw || $promise) {
            $err = new RuntimeException("Unable to import file: {$path} does not exist.");

            if($promise){
                return new Rejected($err);
            }

            throw $err;
        }

        return null;
    }

    return (static function($__path, $__require, $__once, $__scope, $__promise): mixed {
        if($__scope !== []){
            if (\array_is_list($__scope)) {
                $__err = new InvalidArgumentException(
                    "import() expects associative array for \$scope, list array given."
                );

                if($__promise){
                    return new Rejected($__err);
                }

                throw $__err;
            }

            \extract($__scope, EXTR_SKIP);
        }

        $result = $__require
            ? ($__once ? require_once $__path : require $__path)
            : ($__once ? include_once $__path : include $__path);

        return $__promise ? new Fulfilled($result) : $result;
    })($path, $require, $once, $scope, $promise);
}

/**
 * Logs a message to a specified target using the configured PSR-compatible logger.
 *
 * The target can be a log level (`info`, `error`, etc.), an email address, or a remote URL.
 * Delegates to the `Logger::dispatch()` method for synchronous or asynchronous handling.
 *
 * **Supported Log Levels:**
 * - `emergency` – System is unusable.
 * - `alert` – Immediate action required.
 * - `critical` – Critical conditions.
 * - `error` – Runtime errors.
 * - `warning` – Exceptional but non-critical conditions.
 * - `notice` – Normal but significant events.
 * - `info` – General operational entries.
 * - `debug` – Detailed debugging info.
 * - `exception` – Captures exception messages.
 * - `php_errors` – Logs native PHP errors.
 * - `metrics` – Performance logging (e.g., API timing).
 *
 * @param string $destination The log destination, (e.g, Log level, email, or remote endpoint URL).
 * @param string $message The message to log.
 * @param array $context Optional contextual data.
 *
 * @return void
 * @throws InvalidArgumentException If the target is invalid or logging fails.
 *
 * @see https://luminova.ng/docs/0.0.0/logging/logger
 * @see https://luminova.ng/docs/0.0.0/logging/nova-logger
 * @see https://luminova.ng/docs/0.0.0/logging/levels
 */
function logger(string $destination, string $message, array $context = []): void
{
    Logger::dispatch($destination, $message, $context);
}

/**
 * Set locale or return locale application string.
 *
 * @param string|null $locale If locale is present it will set it else return the locale in use.
 *
 * @return string|bool Return application locale if null was passed.
 *          Or return true if new locale was passed and was successfully, otherwise false.
 */
function locale(?string $locale = null): string|bool 
{
    if(!$locale){
        return \env('app.locale', 'en');
    }

    return \setenv('app.locale', $locale, true);
}

/**
 * Get the start URL with an optional route path.
 * 
 * Automatically builds a full or relative URL depending on environment and the `$relative` flag.
 * Includes host and port for development servers when needed.
 * 
 * @param string $route Optional route URI path to append to the start URL (default: null).
 * @param bool $relative If true, returns a relative URL instead of absolute.
 * 
 * @return string Return the constructed URL (absolute or relative), with optionally pointing to a route or path.
 * 
 * > This function will include hostname port (e.g, `example.com:8080`) if port available.
 * > And ensure URL always start from front controller.
 *
 * @example - Example:
 * 
 * Assuming your application path is like: `/Some/Path/To/htdocs/my-project-path/public/`.
 * 
 * ```php
 * echo base_url('about');
 * ```
 * 
 * It returns depending on your development environment:
 * 
 * **On Development:**
 * - http://localhost:8080/about
 * - http://localhost/my-project-path/public/about
 * - http://localhost/public/about
 * 
 * **In Production:**
 * - http://example.com:8080/about
 * - http://example.com/about
 * 
 * @example - Relative URL Example:
 * 
 * ```php
 * echo base_url('about', true); 
 * // /my-project-path/public/about
 * // /about
 * ```
 */
function base_url(?string $route = null, bool $relative = false): string
{
    return Luminova::toBaseUrl($route, $relative);
}

/**
 * Undocumented function
 *
 * @param string|null $route
 * @param boolean $relative
 * 
 * @return string
 * @deprecated Use base_url
 */
function start_url(?string $route = null, bool $relative = false): string
{
    return Luminova::toBaseUrl($route, $relative);
}

/**
 * Convert an application-relative path to an absolute URL.
 * 
 * @param string $path The path to create absolute URL from.
 * 
 * @return string Return the absolute URL of the specified path.
 * @see base_url()
 * 
 * @example - Example:
 * 
 * Assuming your project path is: `/Path/To/htdocs/project-path/public/`.
 * And assets path like: `assets/files/foo.text`.
 * 
 * ```php
 * echo absolute_url('/Path/To/htdocs/project-path/public/assets/files/foo.text');
 * ```
 * 
 * It returns: 
 * 
 * **On Development:**
 * http://localhost/project-path/public/assets/files/foo.text
 * 
 * **In Production:**
 * http://example.com/assets/files/foo.text
 * 
 */
function absolute_url(string $path): string
{
    return Luminova::toAbsoluteUrl($path);
}

/**
 * @deprecated Use display_path()
 */
function filter_paths(string $path): string 
{
    return Luminova::toDisplayPath($path);
}

/**
 * Convert an absolute file path to a safe display path.
 *
 * Removes leading server-specific path segments and returns a cleaner
 * path starting from the first known system directory.
 *
 * Useful for error messages, logs, and debug output where full server
 * paths should not be exposed.
 *
 * @param string $path The full file path to convert.
 *
 * @return string Return the cleaned path safe for display.
 *
 * @example - Convert an absolute path for display.
 * ```php
 * display_path('/var/www/html/example.com/writeable/storage/uploads/file.jpg');
 * // Returns: writeable/storage/uploads/file.jpg
 * ```
 */
function display_path(string $path): string
{
    return Luminova::toDisplayPath($path);
}

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
 * Generate a random UUID string.
 * 
 * Generates a UUID string of the specified version such as `1, 2, 3, 4, or 5`.
 *
 * @param int $version The version of the UUID to generate (default: 4).
 * @param string|null $namespace The namespace for versions 3 and 5.
 * @param string|null $name The name for versions 3 and 5.
 * 
 * @return string Return the generated UUID string.
 * @throws InvalidArgumentException If the namespace or name is not provided for versions 3 or 5.
 * @see https://luminova.ng/docs/0.0.0/utility/helpers
 * @see Helpers
 * 
 * @example - Example:
 * ```php
 * $version = 4;
 * 
 * $uuid = uuid($version); // uuid-string
 * ```
 */
function uuid(int $version = 4, ?string $namespace = null, ?string $name = null): string 
{
    return Helpers::uuid($version, $namespace, $name);
}

/**
 * Escapes a user input string or array of strings based on the specified context.
 * 
 * **Supported Context Values:**
 *
 * - html - Escape general HTML content. 
 * - js -   Escape JavaScript code. 
 * - css -  Escape CSS styles. 
 * - url -  Escape URL, 
 * - attr - Escape HTML attributes.
 * - raw -  Raw output no escaping apply.
 *
 * @param string|array $input The string or array of strings to be escaped.
 *           For array, you can optionally use the keys of the array to specify the escape context for each value.
 * @param string $context The escaper context in which the escaping should be performed (default:'html').
 * @param string $encoding The escape character encoding to use (default: 'utf-8').
 * 
 * @return array|string Return the escaped string or array of strings.
 * @throws InvalidArgumentException If an unsupported, invalid or blank encoding is provided.
 * @throws BadMethodCallException When an invalid context is called.
 * 
 * @see https://luminova.ng/docs/0.0.0/functions/escaper
 */
function escape(string|array $input, string $context = 'html', string $encoding = 'utf-8'): array|string
{
    if (\is_array($input)) {
        \array_walk_recursive(
            $input, 
            fn(&$value, $key) => $value = escape($value, \is_string($key) ? $key : $context, $encoding)
        );

        return $input;
    }

    $context = \strtolower($context);

    if ($context === 'raw') {
        return $input;
    }

    if($context === 'html' || $context === 'attr'){
        return \htmlspecialchars($input, \ENT_QUOTES | \ENT_SUBSTITUTE, $encoding);
    }

    return Escaper::escape($input, $context, $encoding);
}

/**
 * Strictly sanitizes or validates user input based on the specified type.
 * 
 * This function sanitizes user input by strictly removing unsafe characters 
 * to protect against invalid characters and ensure it conforms to the expected type.
 * 
 * **Available types:**
 * 
 * Supports various types to enforce allowed characters or formats.
 * 
 * - 'int'       : Only numeric characters (0-9) are allowed.
 * - 'numeric'   : Numeric characters, including negative numbers and decimals.
 * - 'key'       : Alphanumeric characters, underscores, and hyphens.
 * - 'password'  : Alphanumeric characters, and special characters (@, *, !, _, -).
 * - 'username'  : Alphanumeric characters, hyphen, underscore, and dot.
 * - 'email'     : Alphanumeric characters and characters allowed in email addresses.
 * - 'url'       : Valid URL characters (alphanumeric, ?, #, &, +, =, . , : , /, -).
 * - 'money'     : Numeric characters, including decimal and negative values.
 * - 'double'    : Floating point numbers (numeric and decimal points).
 * - 'alphabet'  : Only alphabetic characters (a-z, A-Z).
 * - 'phone'     : Numeric characters, plus sign, and hyphen (e.g., phone numbers).
 * - 'name'      : Unicode characters, spaces, and common name symbols (e.g., apostrophe).
 * - 'timezone'  : Alphanumeric characters, hyphen, slash, and colon (e.g., timezone names).
 * - 'time'      : Alphanumeric characters and colon (e.g., time format).
 * - 'date'      : Alphanumeric characters, hyphen, slash, comma, and space (e.g., date format).
 * - 'uuid'      : A valid UUID format (e.g., 8-4-4-4-12 hexadecimal characters).
 * - 'default'   : Removes HTML tags.
 *
 * @param string $string The input string to be sanitized.
 * @param string $type The expected data type (e.g., `Helpers::SANITIZE_EMAIL`, `Helpers::SANITIZE_*`).
 * @param string|null $replacer Replacement for disallowed characters (default: `''` blank).
 *                  If `null`, input is validated only and exception is thrown when failed.
 *
 * @return string|null Return sanitized string, or null if input cannot be sanitized.
 * @throws InvalidArgumentException If input contains disallowed characters and `$replacement` is set to `null`.
 * 
 * @see strict()
 * @link https://luminova.ng/docs/0.0.0/foundation/functions
 * 
 * > **Note:** 
 * > For 'default', HTML tags (including content) are fully removed.
 * > This method ensures secure input handling to prevent unsafe content.
 */
function sanitize(string $input, string $type = Helpers::SANITIZE_DEFAULT, ?string $replacer = ''): ?string 
{
    return Helpers::sanitize($input, $type, $replacer);
}

/**
 * Strictly sanitizes input according to the specified type.
 *
 * Unlike `sanitize()`, `strict()` will **always throw an exception** if input contains invalid characters
 * instead of replacing them. This ensures the input fully conforms to the expected format.
 *
 * @param string $input The string to validate strictly.
 * @param string $type The expected type (default: `Helpers::SANITIZE_DEFAULT`).
 *
 * @return string Return sanitized string.
 * @throws InvalidArgumentException If the input contains invalid characters for the specified type.
 *
 * > **Use Case:** Use `strict()` when you require **full compliance** with input rules and want to prevent
 * > any implicit replacements. This is ideal for sensitive fields like email, usernames, passwords, or UUIDs.
 * 
 * @see sanitize()
 * @link https://luminova.ng/docs/0.0.0/foundation/functions
 */
function strict(string $input, string $type = Helpers::SANITIZE_DEFAULT): string 
{
    $result = Helpers::sanitize($input, $type, null);

    if ($result === null) {
        throw new InvalidArgumentException("Input contains invalid characters for type '{$type}'");
    }

    return $result;
}

/**
 * Checks if the given IP address is a Tor exit node.
 *
 * @param string|null $ip The ip address to check, if NULL get current ip address.
 * @param int $expiration The expiration time to request for new exit nodes from tor api (default: 2592000 30 days).
 * 
 * @return bool Return true if ip address is a Tor exit node, otherwise false.
 * @throws FileException Throws if error occurs or unable to read or write to directory.
 * @see https://luminova.ng/docs/0.0.0/functions/ip
 */
function is_tor(?string $ip = null, int $expiration = 2592000): bool
{
    return IP::isTor($ip, $expiration);
}

/**
 * Get the client's IP address or detailed IP information.
 *
 * If `$ipInfo` is false, this returns the client IP as a string.  
 * If `$ipInfo` is true, it returns detailed IP lookup data as an object (or null if unavailable).  
 *
 * @param bool  $ipInfo Set to true to fetch detailed IP information instead of just the IP (default: false).
 * @param array $metadata Optional metadata to include with the IP information result.
 *
 * @return string|object|null Return client IP as a string, IP information as an object, or null if no data is available.
 * @see https://luminova.ng/docs/0.0.0/functions/ip
 */
function ip_address(bool $ipInfo = false, array $metadata = []): string|object|null
{
    return $ipInfo ? IP::info(metadata: $metadata) : IP::get();
}

/**
 * Check if any given values are considered `empty`.
 *
 * Unlike PHP's native `empty()`, this treats `0` and `'0'` as **not empty**,
 * but treats negative numbers, null, empty strings, arrays, or objects as empty.
 * 
 * This is a custom emptiness check that:
 * - Treats `0` and `'0'` as **not empty**
 * - Considers null, empty arrays, empty objects, empty strings, and negative numbers as empty
 * - Handles Countable objects (e.g., ArrayObject)
 *
 * @param mixed ...$values One or more values to evaluate.
 *
 * @return bool Returns true if any of the values are considered empty; false otherwise.
 *
 * @example - Example:
 * ```php
 * is_empty(0);            // false
 * is_empty([1]);          // false
 * is_empty(-3);           // true
 * is_empty(null);         // true
 * is_empty('');           // true
 * is_empty([]);           // true
 * is_empty((object)[]);   // true
 * ```
 */
function is_empty(mixed ...$values): bool
{
    if($values === []){
        return true;
    }
    
    foreach ($values as $value) {
        if (
            $value === null ||
            $value === [] ||
            $value === (object)[] ||
            (\is_string($value) && \trim($value) === '') ||
            (\is_numeric($value) && ($value + 0) < 0) ||
            (\is_object($value) && ($value instanceof \Countable) && \count($value) === 0)
        ) {
            return true;
        }
    }

    return false;
}

/**
 * Return session data if key is present else return session instance.
 *
 * @param string $key Optional key to retrieve the data (default: null).
 * @param bool $shared Whether to use shared instance (default: true).
 * @param object<SessionManagerInterface> $manager The session manager interface to use (default: SessionManager).
 *
 * @return Session<LazyObjectInterface>|mixed Return session instance or value if key is present.
 * @see https://luminova.ng/docs/0.0.0/sessions/session
 */
function session(?string $key = null, bool $shared = true, ?SessionManagerInterface $manager = null): mixed
{
    return ($key !== null && $key !== '') 
        ? Factory::session($manager, $shared)->get($key) 
        : Factory::session($manager, $shared);
}

/**
 * Create and return cookie instance.
 *
 * @param string $name Name of the cookie.
 * @param string $value Value of the cookie.
 * @param array  $options Options to be passed to the cookie.
 * @param bool $shared Use shared instance (default: false).
 * 
 * @return Cookie<Luminova\Interface\CookieInterface,LazyObjectInterface> Return cookie instance.
 * @see https://luminova.ng/docs/0.0.0/cookies/cookie
 */
function cookie(string $name, string $value = '', array $options = [], bool $shared = false): Cookie
{
    return Factory::cookie($name, $value, $options, $shared);
}

/**
 * Returns a shared instance of a class in factory or factory instance if context is null.
 * 
 * @param string|null $context The factory context name. (default: null).
 * @param bool $shared Allow shared instance creation (default: true).
 * @param mixed $arguments [, mixed $... ] Optional class constructor initialization arguments.
 * 
 * **Factory Context Names:**
 * 
 * -   'task'           `\Luminova\Time\Task`
 * -   'session'        `\Luminova\Sessions\Session`
 * -   'cookie'         `\Luminova\Cookies\Cookie`
 * -   'functions'      `\Luminova\Foundation\Core\Functions`
 * -   'modules'        `\Luminova\Library\Modules`
 * -   'language'       `\Luminova\Components\Languages\Translator`
 * -   'escaper'        `\Luminova\Security\Escaper`
 * -   'network'        `\Luminova\Http\Network`
 * -   'filesystem'     `\Luminova\Storage\Filesystem`
 * -   'validate'       `\Luminova\Security\Validation`
 * -   'response'       `\Luminova\Template\Response`
 * -   'request'        `\Luminova\Http\Request`
 * -   'service'        `\Luminova\Foundation\Module\Service`
 * -   'notification'   `\Luminova\Notifications\Firebase\Notification`,
 * -   'caller'         `\Luminova\Foundation\Module\Caller`
 * 
 * @return object<\T>|Factory|null Return instance of factory or instance of factory class, otherwise null.
 * @throws LuminovaException Throws an exception if factory context does not exist or error occurs.
 * @example - using factory to load class like: `$config = factory('config');`.
 * 
 * Is same as:
 * 
 * ```php
 * $config = \Luminova\Foundation\Module\Factory::config();
 * // Or
 * $config = new \Luminova\Config\Configuration();
 * ```
 * @see https://luminova.ng/docs/0.0.0/boot/factory
 */
function factory(?string $context = null, bool $shared = true, mixed ...$arguments): ?object
{
    if($context === null || $context === ''){
        return new Factory();
    }

    $arguments[] = $shared;

    return Factory::$context(...$arguments);
}

/**
 * Returns a shared instance of a class in services or service instance if context is null.
 * 
 * @param class-string<\T>|string|null $service The service class name or alias.
 * @param bool $shared Allow shared instance creation (default: true).
 * @param bool $serialize Allow object serialization (default: false).
 * @param mixed $arguments [, mixed $... ] Service initialization arguments.
 * 
 * @return object<\T>|Service|null Return service class instance or instance of service class.
 * @throws LuminovaException Throws an exception if service does not exist or error occurs.
 * 
 * @example - Get config:
 * 
 * ```php
 * $config = service('Config');
 * // OR
 * $config = Service::Config();
 * ```
 * 
 * Both are Same as:
 * ```php
 * $config = new \Foo\Bar\Config();
 * ```
 * @see https://luminova.ng/docs/0.0.0/boot/service
 */
function service(?string $service = null, bool $shared = true, bool $serialize = false, mixed ...$arguments): ?object
{
    if($service === null || $service === ''){
        return Factory::service();
    }

    $arguments[] = $serialize;
    $arguments[] = $shared;

    return Factory::service()->{$service}(...$arguments);
}

/**
 * Delete a service or clear all services
 * If NULL is passed all cached services instances will be cleared.
 * Else delete a specific services instance and clear it's cached instances.
 * 
 * @param class-string<\T>|string $service The class name or alias, to delete and clear it cached.
 * 
 * @return bool Return true if the service was removed or cleared, false otherwise.
 * @see https://luminova.ng/docs/0.0.0/boot/service
 */
function remove_service(?string $service = null): bool
{
    if($service === null){
        return Factory::service()->clear();
    }

    return Factory::service()->delete($service);
}

/**
 * Get detailed information about the user's browser and device capabilities.
 * 
 * This function inspects the user agent string and returns browser information 
 * in the format you choose.
 * 
 * Return formats:
 * - `'array'` → associative array of browser info.
 * - `'object'` → stdClass object with browser info.
 * - `'instance'` → the `UserAgent` instance itself.
 * 
 * @param string|null $userAgent The user agent string to analyze (defaults to current UA).
 * @param string $return Desired return type: `'array'`, `'object'`, or `'instance'`. Default `'object'`.
 * @param bool $shared If true, reuse a static `UserAgent` instance (default: true).
 * 
 * @return array<string,mixed>|object{string,mixed}|UserAgent|false Return parsed browser information, 
 *         `UserAgent` instance, or `false` if detection fails.
 * 
 * @see https://luminova.ng/docs/0.0.0/http/user-agent
 */
function browser(?string $userAgent = null, string $return = 'object', bool $shared = true): mixed
{
    if ($return !== 'instance' && \ini_get('browscap')) {
        $asArray = ($return === 'array');
        $browser = \get_browser($userAgent, $asArray);

        if ($browser !== false) {
            return $browser;
        }

        return UserAgent::parse($userAgent, $asArray);
    }

    if ($shared) {
        static $ua = null;
        if (!$ua instanceof UserAgent) {
            $ua = new UserAgent($userAgent);
        }

        return $ua;
    }

    return new UserAgent($userAgent);
}

/**
 * Tells which operating system platform your application is running on.
 * 
 * **Predefine OS Values:**
 * 
 * - mac - For macOS.
 * - windows - For Windows OS.
 * - linux - For Linux OS.
 * - freebsd - For FreeBSD OS.
 * - openbsd - For OpenBSD OS.
 * - bsd - For BSD OS.
 * - solaris - For Solaris OS.
 * - aws - For AWS OpsWorks.
 * - azure - For Azure environment.
 * - etc.
 * 
 * @param string $os The platform name to check.
 * 
 * @return bool Return true if the platform is matching, false otherwise.
 * @example - Usage:
 * ```php
 * is_platform('windows') // Return true on window
 * ```
 */
function is_platform(string $os): bool
{
    $os = \strtolower($os);
    return match ($os) {
        'mac'      => \PHP_OS_FAMILY === 'Darwin',
        'windows'  => \PHP_OS_FAMILY === 'Windows',
        'freebsd'  => \PHP_OS === 'FreeBSD',
        'openbsd'  => \PHP_OS === 'OpenBSD',
        'bsd'      => \PHP_OS_FAMILY === 'BSD',
        'solaris'  => \PHP_OS_FAMILY === 'Solaris',
        'linux'    => \PHP_OS_FAMILY === 'Linux',
        'aws' => \getenv('AWS_EXECUTION_ENV') !== false
            || \getenv('AWS_REGION') !== false,
        'azure' => \getenv('WEBSITE_INSTANCE_ID') !== false
            || \getenv('AZURE_FUNCTIONS_ENVIRONMENT') !== false,
        default => \str_contains(\strtolower(\php_uname('s')), $os),
    };
}

/**
 * Converts text characters in a string to HTML entities. 
 * 
 * @param string $text A string containing the text to be processed.
 * 
 * @return string Return the processed text with HTML entities.
 */
function text2html(?string $text): string
{ 
    $text ??= '';
    return (\trim($text) === '') ? '' : \htmlspecialchars($text, \ENT_QUOTES|\ENT_HTML5);
}

/**
 * Converts newline characters in a string to HTML entities. 
 * This is useful when you want to display text in an HTML textarea while preserving the original line breaks.
 * 
 * @param string|null $text A string containing the text to be processed.
 * 
 * @return string Return formatted string.
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
 * Import a custom library from the libraries/libs directory.
 *
 * This function simplifies loading external libraries stored under `/libraries/libs/`.
 *
 * @param string $library The library path or name (e.g., 'Foo/Bar/Baz' or 'Foo/Bar/Baz.php').
 * @param bool   $throw  If true, throws an exception when the library file is not found.
 *
 * @example - Example:
 * ```php
 * import_lib('Foo/Bar/Baz');
 * // Or
 * import_lib('Foo/Bar/Baz.php');
 * ```
 *
 * @return bool Returns true if the library was successfully loaded, false otherwise.
 * @throws RuntimeException If the file is missing and $throw is true.
 */
function import_lib(string $library, bool $throw = false): bool
{
    $library = \trim($library);
    if (!\str_ends_with($library, '.php')) {
        $library = "{$library}.php";
    }

    $path = root('/libraries/libs/', \trim($library, \TRIM_DS));

    try {
        import($path, true);
        return true;
    } catch (\Throwable $e) {
        if ($throw) {
            throw new RuntimeException(
                \sprintf("Failed to import library: %s from path: %s", $library, $path), 
                previous: $e
            );
        }

        return false;
    }
}

/**
 * Translate multiple languages it supports nested array.
 * 
 * Placeholder Pattern:
 * 
 * - sing index: "Error name {0} and email {1}"
 * - Using keys: "Error name {name} and email {email}"
 *
 * @param string $lookup The language context annotation line to lookup (e.g, `App.error.foo.bar`).
 * @param string|null $default Optional fallback message or translation if not found.
 * @param string|null $locale OPtion translation locale to use. If null the default application will be used.
 * @param array<string|int,string|int> $placeholders Optional replaceable placeholders key-pir to translate in message.
 * 
 * 
 * @return string Return translated message.
 * @throws NotFoundException if translation is not found and default is not provided.
 * @see https://luminova.ng/docs/0.0.0/languages/translate
 * 
 * @example - Using index:
 * 
 * ```php 
 * echo lang('User.error.all', null, 'en', ['Peter', 'peter@foo.com]);
 * ```
 * @example - Using keys:
 * 
 * ```php
 * echo lang('User.error.all', null, 'en', [
 *      'name' => 'Peter', 
 *      'email' => 'peter@foo.com
 * ]);
 * ```
 */
function lang(string $lookup, ?string $default = null, ?string $locale = null, array $placeholders = []): string
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

/**
 * Resolves system and application path.
 * 
 * This function resolves application paths based on names 
 * and ensure separator are normalized to based on environment `unix` or `windows` specific style.
 * 
 * **Available Paths:**
 * 
 * - `app` - Application root directory.
 * - `system` - Luminova Framework and third-party plugins root directory.
 * - `plugins` - Third-party plugins root directory.
 * - `library` - Custom libraries root directory.
 * - `controllers` - Application controllers directory.
 * - `writable` - Application writable directory.
 * - `logs` - Application logs directory.
 * - `caches` - Application cache directory.
 * - `public` - Application public directory (front controller).
 * - `assets` - Application public assets directory.
 * - `views` - Application template views directory.
 * - `routes` - Application method-based routes directory.
 * - `languages` - Application language pack directory.
 * - `service` - Application cached services directory.
 * 
 * @param string $file The path name to resolves.
 * 
 * @return string Return directory path, windows, unix or windows style path. 
 * 
 * @see Filesystem::path()
 */
function system_path(string $name): string
{
    return Filesystem::path($name);
}

/**
 * Undocumented function
 *
 * @param string $name
 * 
 * @return string
 * @deprecated Use system_path
 */
function path(string $name): string
{
    return Filesystem::path($name);
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
function object_column(object $from, string|int|null $property = null, string|int|null $index = null): object
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
 * Recursively convert an object (or mixed type) to an array.
 *
 * @param mixed $input The input to convert (object, array, or scalar).
 *
 * @return array Return the array representation.
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
 * Convert an array or string delimited string list to a standard JSON object.
 *
 * @param array|string $input Input array or listified string 
 *                  from `Luminova\Components\String\Listifier` (e.g, `foo=bar,bar=2,baz=[1;2;3]`).
 *
 * @return object|false JSON object if successful, false on failure.
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
 * @see https://luminova.ng/docs/0.0.0/utilities/string-listification
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
 * @param array $array The array to search for Listified values in.
 *
 * @return bool Returns true if all list items exist in the array; false otherwise.
 * @see https://luminova.ng/docs/0.0.0/utilities/string-listification
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
 * @see https://luminova.ng/docs/0.0.0/utilities/string-listification
 */
function is_list(string $input): bool 
{
    return $input && Listifier::isList($input);
}

/**
 * Write or append data to a file.
 *
 * Lightweight wrapper around `Filesystem::write()`, intended as a safer and
 * more flexible alternative to `file_put_contents()`.
 *
 * Supports:
 * - Writing strings or stream resources
 * - FILE_APPEND for appending
 * - LOCK_EX for atomic writes
 * - Optional stream context
 *
 * @param string $filename Path to the file.
 * @param string|resource $content Data to write.
 * @param int $flags Bitwise flags (e.g. FILE_APPEND | LOCK_EX).
 * @param resource|null $context Optional stream context.
 *
 * @return bool Returns true on success, false on failure.
 * @throws FileException If the file cannot be written.
 * 
 * @see Filesystem::write() For advance options.
 */
function write_content(string $filename, mixed $content, int $flags = 0, mixed $context = null): bool 
{
    return Filesystem::write($filename, $content, $flags, context: $context);
}

/**
 * Read file contents with optional offset, length limit, and throttling.
 *
 * This is a lightweight wrapper around `Filesystem::contents()`, providing
 * a drop-in alternative to `file_get_contents()` with finer control.
 *
 * - Reads from a byte offset
 * - Limits total bytes read (0 = read all)
 * - Reads in chunks to reduce memory usage
 * - Optional delay between chunks for throttling
 *
 * @param string $filename Path to the file.
 * @param int $length Maximum number of bytes to read (0 = read all).
 * @param int $offset Byte offset to start reading from.
 * @param bool $useInclude Whether to search the include path.
 * @param resource|null $context Optional stream context.
 * @param int $delay Delay in microseconds between chunk reads.
 *
 * @return string|false File contents on success, false on failure.
 * @throws FileException If the file cannot be opened or read.
 * 
 * @see Filesystem::contents() For advance options.
 */
function get_content(
    string $filename, 
    int $length = 0, 
    int $offset = 0, 
    bool $useInclude = false, 
    mixed $context = null,
    int $delay = 0
): string|bool 
{
    return Filesystem::contents(
        $filename, 
        $length, 
        $offset, 
        $useInclude, 
        $context, 
        $delay
    );
}

/**
 * Create a directory if it does not already exist.
 * 
 * If the directory exists, the function returns true without error.
 * Supports recursive creation of nested directories.
 * 
 * @param string $path Directory path to create.
 * @param int|null $permissions Unix permission mode. 
 *      If null, uses (default: `App\Config\Files::$dirPermissions`).
 * @param bool $recursive Whether to create parent directories if they do not exist (default: true).
 * 
 * @return bool Return true if the directory exists or was created successfully.
 * @throws RuntimeException If the parent path is not readable.
 * @throws FileException If the directory cannot be created.
 */
function make_dir(string $path, ?int $permissions = null, bool $recursive = true): bool 
{
    return Filesystem::mkdir(
        $path, 
        $permissions ?? Files::$dirPermissions, 
        $recursive
    );
}

/**
 * Get a temporary directory path or generate a unique temporary file.
 *
 * Behavior:
 * - With no arguments, returns the system temp directory (or local temp if enabled).
 * - With `$prefix` only, creates (or returns) a subdirectory inside the temp directory.
 * - With `$extension`, generates a unique temporary filename with that extension.
 *
 * @param string|null $prefix Optional filename prefix or subdirectory name.
 * @param string|null $extension Optional file extension. When provided, a unique temporary file is generated.
 * @param bool $local Whether to use the application's local writable temp directory instead of the system temp.
 *
 * @return string|false Returns a temp directory path or a full temporary file path or false on failure.
 */
function get_temp_file(?string $prefix = null, ?string $extension = null, bool $local = false): string|bool
{
    $dir = ($local 
        ? root('/writeable/temp/') 
        : \sys_get_temp_dir() . \DIRECTORY_SEPARATOR
    );

    if($local && !make_dir($dir, 0755)){
        return false;
    }

    if($extension){
        $prefix ??= 'tmp_';
        $extension = '.' . \ltrim($extension, '.');
        $file = \tempnam($dir, $prefix);
        static $ids = [];

        if($file === false){
            $id = $prefix . $extension;
            $ids[$id] ??= \uniqid($prefix, true);
            $file = $dir . $ids[$id] . $extension;

            return (!\file_exists($file) && (!\is_writable($dir) || !\touch($file)))
                ? false
                : $file;
        }

        return \rename($file, $file . $extension) 
            ? $file . $extension
            : $file;
    }

    if ($prefix) {
        $newDir = $dir . $prefix . \DIRECTORY_SEPARATOR;
        return (\is_dir($newDir) || make_dir($newDir, 0755))
            ? $newDir
            : $dir;
    }

    return $dir;
}

/**
 * Validate input fields or return validation instance.
 * 
 * If input and rules are specified, it will do the validation and return instance which you can then called method `$validation->isPassed()`
 * To check if passed or failed, or get the error information.
 *
 * @param array $inputs Input fields to validate on (e.g, `$_POST`, `$_GET` or `$this->request->getBody()`).
 * @param array $rules Validation filter rules to apply on each input field.
 * @param array $messages Validation error messages to apply on each filter on input field.
 * 
 * @return InputValidationInterface Return instance of input validation object.
 * @see https://luminova.ng/docs/0.0.0/security/validation
 * 
 * @example - Validation example:
 * ```php
 * $rules = ['email' => 'required|email'];
 * $messages = [
 *   'email' => [
 *        'required' => 'email is required',
 *        'email' => 'Invalid [value] while validating [rule] on [field]'
 *    ]
 * ];
 * 
 * $input = [
 *      'email' => 'peter@example.com'
 * ];
 * 
 * $validate = validate($input, $rules, $messages);
 * if($validate->isPassed()){
 *      echo 'Success';
 * }else{
 *      $error $validate->getError();
 *      $errors $validate->getErrors();
 *      echo $error;
 *      var_dump($errors);
 * }
 * ```
 */
function validate(?array $inputs = null, ?array $rules = null, array $messages = []): InputValidationInterface 
{
    $instance = Factory::validate();

    if ($inputs && $rules) {
        $instance->setRules($rules, $messages);
        $instance->validate($inputs);
    }
    
    return $instance;
}

/**
 * Get class basename from namespace or object.
 * 
 * @param string|object<\T> $from Class namespace or class object.
 * 
 * @return string Return the class basename.
 */
function get_class_name(string|object $from): string 
{
    return Luminova::getClassBaseName(\is_string($from) ? $from : \get_class($from));
}

/**
 * Find whether application is running in cli mode.
 *
 * @return bool Return true if request is made in cli mode, false otherwise.
 */
function is_command(): bool
{
    return Luminova::isCommand();
}

/**
 * Check if the application is running locally on development server.
 *
 * @return bool Return true if is development server, false otherwise.
 */
function is_dev_server(): bool
{
    return \IS_LOCAL;
}

/**
 * Find whether the type of a variable is blob.
 *
 * @param mixed $value Value to check.
 * 
 * @return bool Return true if the value is a blob, false otherwise.
 */
function is_blob(mixed $value): bool 
{
    return Filesystem::isResource($value, 'stream');
}

/**
 * Get the PHP script executable path.
 *
 * @return string|null Return PHP executable path or null.
 */
function which_php(): ?string
{
    if (\defined('PHP_BINARY')) {
        return \PHP_BINARY;
    }

    if (isset($_SERVER['_']) && \str_contains($_SERVER['_'], 'php')) {
        return $_SERVER['_'];
    }

    return null;
}

/**
 * Convert status to int, return run status based on result.
 * In CLI, 0 is considered success while 1 is failure.
 * In some occasions, void or null may be returned, treating it as success.
 * 
 * @param mixed $result The response from the callback function or method to check (e.g, `void`, `bool`, `null`, `int`).
 * @param bool $returnInt Whether to return int or bool (default: int).
 * 
 * @return int|bool Return status response as boolean or integer value.
 */
function status_code(mixed $result = null, bool $returnInt = true): int|bool
{
    if ($result === false || (\is_int($result) && $result == 1)) {
        return $returnInt ? 1 : false;
    }

    return $returnInt ? (int) $result : true;
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
 * Get the length of a string in characters, safely handling multibyte encodings.
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

/**
 * Detect the MIME type of a file or raw data.
 *
 * If the input string is a path to an existing file, it uses `\finfo->file()`,
 * otherwise it treats the input as raw binary and uses `\finfo->buffer()`.
 *
 * @param string $input File path or raw binary string to extract mime from.
 * @param string|null $magicDatabase Optional MIME magic database file (e.g, `\path\custom.mgc`).
 * @param string|null $customDatabase Optional MIME custom database file (e.g, `\path\custom.json`).
 * 
 * @return string Return the detected MIME type (e.g. "image/jpeg"), or false if detection fails.
 * @throws RuntimeException If database file is not readable or if there is an error reading the file.
 * @throws InvalidArgumentException If the extension or MIME type is invalid.
 */
function get_mime(string $input, ?string $magicDatabase = null, ?string $customDatabase = null): string|bool
{
    if($input === ''){
        return 'text/plain';
    }

    if($customDatabase !== null){
        MIME::database($customDatabase);
    }

    return MIME::guess($input, $magicDatabase);
}

/**
 * Temporarily stores and retrieves values within the same scope.
 *
 * @param string $key The key to identify the value.
 * @param mixed $value The value to store (optional).
 * @param mixed $default The default value return if key not found (default: NULL).
 * 
 * @return mixed Returns the value associated with the key, or default value if the key does not exist.
 * @see Boot
 */
function shared(string $key, mixed $value = null, mixed $default = null): mixed 
{
    return ($value === null) 
        ? (Boot::get($key) ?? $default)
        : Boot::set($key, $value);
}

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
 * @example Configuration File:
 * ```php
 *   // app/Config/SomeConfig.php
 *   <?php
 *   return ['foo', 'bar'];
 * ```
 *
 * @example Loading Configuration:
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
 * Initialize or retrieve a new instance of the cache class.
 * 
 * @param string $driver The cache driver to return instance of [filesystem or memcached](default: `filesystem`).
 * @param string|null $storage The name of the cache storage. If null, you must call the `setStorage` method later (default: null).
 * @param string|null $persistentIdOrSubfolder Optional persistent id or subfolder for storage (default: null):
 *  - For Memcached: A unique persistent connection ID. If null, the default ID from environment variables is used, or "default" if not set.
 *  - For Filesystem Cache: A subdirectory within the cache directory. If null, defaults to the base cache directory.
 * 
 * @return FileCache|MemoryCache Return new instance of instance of cache class based on specified driver.
 * @throws ClassException If unsupported driver is specified.
 * @throws CacheException If there is an issue initializing the cache.
 * @throws InvalidArgumentException If an invalid subdirectory is provided for the filesystem cache.
 */
function cache(
    string $driver = 'filesystem', 
    ?string $storage = null, 
    ?string $persistentIdOrSubfolder = null
): FileCache|MemoryCache {
    /**
     * @var array<string,FileCache|MemoryCache> $instances
     */
    static $instances = [];
    $instances[$driver] ??= match ($driver) {
        'memcached' => new MemoryCache($storage, $persistentIdOrSubfolder),
        'filesystem' => new FileCache($storage, $persistentIdOrSubfolder),
        default => throw new ClassException(
            'Invalid cache driver type specified. Supported drivers: memcached, filesystem.'
        ),
    };

    $cache = $instances[$driver];

    if ($storage !== null && $cache->getStorage() !== $storage) {
        $cache->setStorage($storage);
    }

    if ($persistentIdOrSubfolder !== null) {
        if ($driver === 'memcached' && $cache->getId() !== $persistentIdOrSubfolder) {
            $cache->setId($persistentIdOrSubfolder);
        }

        if ($driver === 'filesystem') {
            $subfolder = \trim($persistentIdOrSubfolder, \TRIM_DS) . \DIRECTORY_SEPARATOR;
            if ($cache->getFolder() !== $subfolder) {
                $cache->setFolder($subfolder);
            }
        }
    }

    return $cache;
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
 * When two arrays share the same key, values from the second array overwrite those from the first. 
 * Numeric keys are appended only if the value doesn't already exist in the array.
 *
 * @param array ...$array The arrays to be merged.
 * 
 * @return array Return the merged result array.
 */
function array_merge_recursive_replace(array ...$array): array {
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
 * Merges a response into the provided results variable while optionally preserving the structure of nested arrays.
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

/**
 * Sets the HTTP response status code and sends appropriate headers.
 * 
 * This function sets the HTTP status code and sends the corresponding status message header.
 * If the status code is not predefined, it returns `false`. For predefined status codes, it sends headers including the status
 * message. The function determines the HTTP protocol version based on the server's protocol.
 *
 * @param int $status The HTTP status code to set (e.g., 200, 404).
 * 
 * @return bool Returns true if the status code is found in the predefined list and headers are set, otherwise false.
 */
function http_status_header(int $status): bool
{
    $message = HttpStatus::phrase($status, null);

    // Check if the status code is in the predefined list
    if ($message === '') {
        return false;
    }

    // Determine the protocol version (1.0 or 1.1) based on the server's protocol
    $protocol = ($_SERVER['SERVER_PROTOCOL'] ?? '1.0');
    $protocol = ($protocol !== '1.0')
        ? (\strcasecmp($protocol, 'HTTP/1.0') ? '1.1' : '1.0') 
        : $protocol;

    // Send the HTTP header with the specified status and message
    @\header("HTTP/$protocol $status {$message}");

    // Send the 'Status' header, which is often used for compatibility with older clients
    @\header("Status: $status {$message}", true, $status);

    // Set the status code as redirect status
    $_SERVER["REDIRECT_STATUS"] = $status;
    
    return true;
}

/**
 * Checks if a function exists and caches the result to avoid repeated checks.
 * 
 * This function uses a static cache to store whether a function exists or not.
 * If the function's existence has been checked before, the cached result is returned.
 * Otherwise, it checks the function's existence using `function_exists()` and caches the result,
 * improving performance by avoiding repeated function existence checks.
 *
 * @param string $function The name of the function to check for existence.
 * 
 * @return bool Returns true if the function exists, false otherwise.
 */
function function_exists_cached(string $function): bool
{
    static $functions = [];
    $isFunc = $functions[$function] ?? null;

    if($isFunc === null){
        return $functions[$function] = \function_exists($function);
    }

    return $isFunc;
}

/**
 * Checks if a class exists and caches the result for improved performance.
 * 
 * This function maintains a static cache to remember whether a class has been previously checked.
 * It first checks the cache to see if the class's existence was determined before. If not, it uses
 * `class_exists()` to perform the check and then stores the result in the cache. This avoids redundant
 * checks and speeds up subsequent requests.
 *
 * @param string $class The name of the class to check for existence.
 * @param bool $autoload Optional. Whether to check for class existence with autoload (default: true).
 * 
 * @return bool Returns true if the class exists, false otherwise.
 */
function class_exists_cached(string $class, bool $autoload = true): bool
{
    static $classes = [];
    $isClass = $classes[$class] ?? null;

    if($isClass === null){
        return $classes[$class] = \class_exists($class, $autoload);
    }

    return $isClass;
}