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

if (defined('BOOT_WARMUP_STARTED')) {
    return;
}

use \Throwable;
use App\Kernel;
use Luminova\Boot;
use Luminova\Runtime;
use Luminova\Luminova;
use Luminova\AI\Agent;
use Luminova\Config\Env;
use App\Config\Security;
use Luminova\Template\View;
use Luminova\Logger\Logger;
use Luminova\Cookies\Cookie;
use Luminova\Http\Network\IP;
use Luminova\Security\Escaper;
use Luminova\Components\Async;
use Luminova\Template\Response;
use Luminova\Storage\Filesystem;
use App\Application as MainApplication;
use Luminova\Foundation\Core\Application;
use Luminova\Utility\{Uuid, Ulid, Util, Mime};
use Luminova\Foundation\Module\{Factory, Service};
use Luminova\Http\{Request, HttpStatus, UserAgent};
use Luminova\AI\Client\{Ollama, OpenAI, Anthropic};
use Luminova\Cache\{FileCache, RedisCache, MemoryCache};
use Luminova\Interface\{
    Awaitable,
    AIAgentInterface,
    LazyObjectInterface,
    InputValidationInterface,
    RequestInterface,
    ContentResponseInterface,
    SessionManagerInterface
};
use Luminova\Exceptions\{
    LuminovaException,
    FileException,
    ClassException,
    RuntimeException,
    InvalidArgumentException,
    Http\ResponseException
};

/**
 * Build an absolute path from the application root directory.
 * 
 * Generates a normalized path based on `APP_ROOT`, with optional
 * subdirectory and filename appended. All input paths are sanitized
 * to ensure consistent separators.
 * 
 * When `$normalize` is enabled, the final path is converted to the
 * operating system directory separator for filesystem usage.
 *
 * @param string|null $path Optional subdirectory relative to the application root (e.g., 'writeable/logs').
 * @param string|null $filename Optional filename to append to the path (e.g., 'debug.log').
 * @param bool $normalize When true, the final path is normalized to OS-specific separators
 *        for filesystem operations (default: `false` uses `/`).
 *
 * @return string Returns a normalized absolute path based on `APP_ROOT`.
 * 
 * @see temp_dir()
 * @see get_temp_file()
 *
 * @example - Usage:
 *
 * ```php
 * $file = root('writeable/logs', 'debug.log');
 *
 * // Output:
 * /var/www/app/writeable/logs/debug.log
 * ```
 * 
 * @example - With OS Compatible:
 *
 * ```php
 * $file = root('writeable/logs', 'debug.log', true);
 *
 * // Example outputs:
 * // Linux:  /var/www/app/writeable/logs/debug.log
 * // macOS:  /Applications/XAMPP/htdocs/app/writeable/logs/debug.log
 * // Windows: C:\wamp64\www\app\writeable\logs\debug.log
 * ```
 *
 * > **Note:**
 * >
 * > - Input separators (`\` and `/`) are automatically normalized.
 * > - The function does not validate whether the path exists.
 * > - Use `$normalize = true` only for direct filesystem access.
 */
function root(
    ?string $path = null, 
    ?string $filename = null, 
    bool $normalize = false
): string
{
    return Luminova::root($path, $filename, $normalize);
}

/**
 * Resolve a service through the application kernel or return the kernel instance.
 *
 * When `$service` is `null`, the kernel instance is returned. Otherwise, the
 * service is resolved using the application kernel.
 *
 * **Service Name {@see Kernel::SERVICE_*}:**
 *
 * - `http.client`  — HTTP client service.
 * - `logger`       — Logger service.
 * - `mailer`       — Mailer service.
 * - `session`      — Session client service.
 * - `routing`      — Routing system service.
 * - `application`  — Application service.
 * - `memcached`    — Memcached service.
 * - `redis`        — Redis service.
 * - `cache`        — Cache service.
 *
 * @param Application::CoreKernelServices|string|null $service The service identifier,
 *        abstract-class/interface name, or `null` to return the kernel instance.
 * @param bool $shared Whether to reuse a shared instance when supported.
 * @param mixed ...$arguments Arguments passed to the service resolver.
 *
 * @return Kernel|mixed The resolved service or kernel result.
 *
 * @throws RuntimeException If the requested service cannot be resolved.
 * @throws ClassException If the service/abstract is not available.
 *
 * @see Kernel::create() To create or retrieve the kernel instance.
 * @see Kernel::resolve() To resolve a service through the kernel.
 * @see Kernel::has() To check whether a service is registered.
 * @see Kernel::get() To retrieve a registered service.
 *
 * @example - Get the HTTP client:
 * ```php
 * $http = kernel('http.client');
 * ```
 *
 * @example - Get the logger:
 * ```php
 * $logger = kernel('logger');
 * ```
 *
 * @example - Get the kernel:
 * ```php
 * $kernel = kernel();
 * ```
 *
 * @link https://luminova.ng/docs/0.0.0/foundation/kernel
 * @see Application::make()
 */
function kernel(
    ?string $service = null,
    bool $shared = true,
    mixed ...$arguments
): mixed 
{
    if ($service === null) {
        return Kernel::create($shared);
    }

    return Kernel::resolve(
        $service,
        $shared,
        ...$arguments
    );
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
 * @template T of Application
 *
 * @param bool $shared Whether to return the shared instance (default: true).
 * @param T|Application|null $new Optional application instance to replace the shared one.
 *
 * @return T|MainApplication Returns a shared or newly created application instance.
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
    if ($new === null) {
        return kernel(Kernel::SERVICE_APPLICATION, $shared);
    }

    return kernel(Kernel::SERVICE_APPLICATION, $shared)
        ->setInstance($new);
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
    return Request::capture($shared);
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
 * @return Response<ContentResponseInterface> Return instance of view response object.
 * @see https://luminova.ng/docs/0.0.0/templates/response
 *
 * @example Send a JSON response:
 * ```php
 * response()->json(['status' => 'OK', 'message' => 'Done!']);
 * ```
 * @example Send compressed JSON response:
 * ```php
 * response()
 *      ->compress()
 *      ->json(['status' => 'OK', 'message' => 'Done!']);
 * ```
 * 
 * @example Send minified HTML response:
 * ```php
 * response()
 *      ->minify()
 *      ->json('<body...');
 * ```
 */
function response(
    int $status = 200, 
    ?array $headers = null,  
    bool $shared = true
): ContentResponseInterface
{
    $headers ??= [];

    if (!$shared) {
        return new Response(status: $status, headers: $headers);
    }

    return Response::getInstance(status: $status, headers: $headers)
        ->setStatus($status)
        ->headers($headers);
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
 * @see View::setModule() - To set HMVC module name.
 * @see View::setFolder() - To set template sub-directory.
 * @see View::setController() - To set controller class name.
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
        $view = new View(Kernel::resolve(Kernel::SERVICE_APPLICATION, true));
    }

    return $view->view($template, $type);
}

/**
 * Generate a URL for an application route or file.
 *
 * Returns an absolute URL using `APP_URL` when requested, or a relative
 * URL based on the current request path.
 *
 * @param string|null $uri The route, file path, or URI to append.
 * @param bool $absolute Whether to return an absolute URL.
 * @param int|null $depth The directory depth for relative URLs. If null,
 *                        the depth is derived from the current request URI.
 *
 * @return string The generated URL.
 * 
 * @see asset()
 *
 * @example - Example:
 * ```php
 * use function Luminova\Funcs\href;
 * 
 * href();                        // "/"
 * href('about');                 // "/about"
 * href('admin/dashboard', true); // "https://example.com/admin/dashboard"
 * ```
 */
function href(?string $uri = null, bool $absolute = false, ?int $depth = null): string
{
    $uri = \ltrim($uri ?? '', '/');

    if ($absolute) {
        return \rtrim(APP_URL, '/') . ($uri === '' ? '/' : "/{$uri}");
    }

    return View::relativePath($uri, $depth);
}

/**
 * Generate a URL for a file in the public `assets/` directory.
 *
 * Returns an absolute or relative URL for public assets such as CSS,
 * JavaScript, images, and other files.
 *
 * @param string|null $filename The asset path or file name.
 * @param bool $absolute Whether to return an absolute URL.
 * @param int|null $depth The directory depth for relative URLs. If null,
 *                        the depth is derived from the current request URI.
 *
 * @return string The generated asset URL.
 * 
 * @see href()
 *
 * @example - Example:
 * ```php
 * use function Luminova\Funcs\asset;
 * 
 * asset('css/style.css');   // "/assets/css/style.css"
 * asset();                  // "/assets/"
 * asset('css');             // "/assets/css/"
 * asset('js/app.js', true); // "https://example.com/assets/js/app.js"
 * ```
 */
function asset(?string $filename = null, bool $absolute = false, ?int $depth = null): string
{
    return href(
        'assets/' . \ltrim($filename ?? '', '/'),
        $absolute,
        $depth
    );
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
 * @see \Luminova\Exceptions\HttpException - For throwing HTTP exceptions
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
 * @param \Luminova\AI\Model|\BackedEnum|string $model Model enum or model name. Default: `gpt-4.1-mini`.
 * @param array<string,mixed> $options Optional request options passed to `Agent::message()`.
 *
 * @return AIAgentInterface|Anthropic|OpenAI|Ollama|array Returns the response array when `$prompt` is provided,
 *                   otherwise returns the configured AI client instance.
 * @throws \Luminova\Exceptions\AIAgentException if error occur.
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
): AIAgentInterface|array
{
    $ai = Agent::create()->setModel($model);

    return ($prompt === null)
        ? $ai->getAgent()
        : $ai->message($prompt, $options);
}

/**
 * Wrap a task in an Awaitable Future for asynchronous execution.
 *
 * This helper creates a Future without running the task immediately.
 * The returned Awaitable can later be passed to `await()` to run and
 * retrieve the result. Supports optional background detachment.
 *
 * @param (callable():mixed) $task The task to run asynchronously.
 * @param bool $detach If true, the task runs in a detached background process.
 *
 * @return Awaitable An awaitable Future representing the task.
 * @throws InvalidArgumentException If the task type is invalid.
 *
 * @see Async::async() - Core async implementation.
 * @see Async::background() - Core async detached implementation.
 *
 * @example - Fiber Async:
 * ```php
 * $future = async(fn() => doSomething());
 * echo $result;
 * ```
 *
 * @example - Run in background:
 * ```php
 * $future = async(fn() => doWork(), detach: true);
 * $result = await($future);
 * ```
 */
function async(callable $task, bool $detach = false): Awaitable
{
    return Async::async($task, $detach);
}

/**
 * Execute a callable asynchronously in a detached background worker.
 *
 * The task is started immediately and does not block the current execution.
 * Any exception thrown while preparing or starting the worker is logged and
 * `null` is returned.
 *
 * @param (callable(array $args):mixed) $task The task to execute.
 * @param array<string,mixed> $arguments Optional arguments passed to the worker.
 *
 * @return int|null Return the worker process ID (PID), or `null` if the task could
 *                  not be started.
 */
function background(callable $task, array $arguments = []): ?int
{
    try {
        $job = Async::background($task, $arguments);

        $pid = $job->getPid();
        $job->flush();

        return $pid;
    } catch (Throwable $e) {
        Logger::exception($e);

        return null;
    }
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
 * @example - Example:
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
 * Logs a message to a specified target using the configured PSR-compatible logger.
 *
 * The target can be a log level (`info`, `error`, etc.), an email address, or a remote URL.
 * Delegates to the `Logger::dispatch()` method for synchronous or asynchronous handling.
 *
 * **Supported Log Levels:**
 * - `emergency` – System is unusable.
 * - `alert`     – Immediate action required.
 * - `critical`  – Critical conditions.
 * - `error`     – Runtime errors.
 * - `warning`   – Exceptional but non-critical conditions.
 * - `notice`    – Normal but significant events.
 * - `info`      – General operational entries.
 * - `debug`     – Detailed debugging info.
 * - `exception` – Captures exception messages.
 * - `php`       – Logs native PHP errors.
 *
 * @param string $destination The log destination, (e.g, Log level, email, or remote endpoint URL).
 * @param string $message The message to log.
 * @param array $context Optional contextual data.
 *
 * @return void
 * @throws InvalidArgumentException If the target is invalid or logging fails.
 *
 * @link https://luminova.ng/docs/0.0.0/logging/logger
 * @link https://luminova.ng/docs/0.0.0/logging/nova-logger
 * @link https://luminova.ng/docs/0.0.0/logging/levels
 */
function logger(string $destination, string $message, array $context = []): void
{
    Logger::dispatch($destination, $message, $context);
}

/**
 * Get or set the application env locale.
 * 
 * This function retrieves the current locale setting from the environment or sets a new locale.
 * If a new locale is provided, it can optionally be persisted to the `.env` file.
 *
 * @param string|null $locale If locale is present it will set it else return the locale in use.
 * @param bool $persist Whether to persist the locale setting to `.env` file (default: false)..
 *
 * @return string|bool Return application locale if null was passed.
 *          Or return true if new locale was passed and was successfully, otherwise false.
 * 
 * > **Note:**
 * > This function is not same as `setlocale()` which is a PHP built-in function for system locale settings. 
 * > This function is for application-level locale management.
 * 
 * @see \Luminova\Runtime::setLocale() For convenient `setlocale` wrapper.
 */
function locale(?string $locale = null, bool $persist = false): string|bool 
{
    if(!$locale){
        return Env::get('app.locale', 'en');
    }

    return Env::set('app.locale', $locale, $persist);
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
 * @param bool $ignoreUserAbort Whether to ignore client disconnects (non-CLI only).
 *
 * @return bool Returns true if a response flush attempt was made.
 */
function finish_response(bool $closeSession = true, bool $ignoreUserAbort = true): bool
{
    if ($closeSession && \session_status() === \PHP_SESSION_ACTIVE) {
        \session_write_close();
    }

    if($ignoreUserAbort && PHP_SAPI !== 'cli'){
        \ignore_user_abort(true);
    }

    try {
        return (bool) func('fastcgi_finish_request');
    } catch (Throwable) {
        try {
            return (bool) func('litespeed_finish_request');
        } catch (Throwable) {
            while (\ob_get_level() > 0) {
                @\ob_end_flush();
            }

            @\flush();
            return true;
        }
    }
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
 * @throws RuntimeException If the function is disabled or does not exist.
 *
 * @see https://luminova.ng/docs/0.0.0/foundation/functions
 */
function func(string $function, mixed ...$arguments): mixed
{
    $function = \ltrim($function, '\\');
    $global = "\\{$function}";

    if (function_exists_cached($global)) {
        return $global(...$arguments);
    }

    if (function_exists_cached($function)) {
        return $function(...$arguments);
    }

    throw new RuntimeException(\sprintf(Runtime::isFunctionDisabled($function) 
        ? 'Function "%s" is disabled by PHP configuration.'
        : 'Function "%s" does not exist.',  
        $function
    ));
}

/**
 * Import a PHP file using a physical path or virtual path scheme.
 *
 * Supports framework virtual path aliases and direct paths. Imported files are loaded once
 * by default using require_once.
 *
 * Available schemes:
 *
 * - `app`        → `root/app/*`
 * - `package`    → `root/system/plugins/*`
 * - `system`     → `root/system/*`
 * - `view`       → `root/resources/Views/*`
 * - `public`     → `root/public/*`
 * - `writeable`  → `root/writeable/*`
 * - `libraries`  → `root/libraries/*`
 * - `resources`  → `root/resources/*`
 * - `routes`     → `root/routes/*`
 * - `bootstrap`  → `root/bootstrap/*`
 * - `bin`        → `root/bin/*`
 * - `node`       → `root/node/*`
 *
 * The imported file may receive variables through `$scope`. Array keys are
 * extracted as local variables inside the imported file.
 *
 * @param string $path File path or scheme path to import.
 * @param bool $throw Throw an exception when the file does not exist (default: `true`).
 * @param bool $once Load the file only once using `*_once` (default: `true`).
 * @param bool $useRequire Use `require` instead of `include` (default: `false`).
 * @param array<string,mixed> $scope Variables to expose inside the imported file.
 * @param bool $promise Return a promise result instead of the raw value (default: `false`).
 *
 * @return \Luminova\Interface\PromiseInterface|mixed|null Imported file return value, promise result,
 *                                    or null when file is missing.
 *
 * @throws RuntimeException If file does not exist and `$throw` is enabled.
 * @throws InvalidArgumentException If `$scope` is a list array.
 *
 * @example - Examples:
 * ```php
 * import('app:Config/settings.php');
 * import(__DIR__ . '/app/Config/settings.php');
 * 
 * import('package:brick/math/src/BigNumber.php');
 * import('routes:api.php', once: false);
 * import('system:Bootstrap/init.php', require: false);
 * ```
 * 
 * @example - Promise Example:
 * ```php
 * import('app:Config/settings.php', promise: true)
 *      ->then(function(mixed $settings){
 *          echo $settings['name'];
 *      })->catch(function(Throwable $e){
 *          echo $e->getMessage()
 *      });
 * ```
 */
function import(
    string $path,
    bool $throw = true,
    bool $once = true,
    bool $useRequire = false,
    array $scope = [],
    bool $promise = false
): mixed
{
    return Boot::import(
        $path,
        $throw, 
        $once,
        $useRequire,
        $scope,
        $promise
    );
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
 * @example - Example:
 * ```php
 * // Convert an absolute path for display.
 * 
 * display_path('/var/www/html/example.com/writeable/storage/uploads/file.jpg');
 * // Returns: writeable/storage/uploads/file.jpg
 * ```
 */
function display_path(string $path): string
{
    return Luminova::toDisplayPath($path);
}

/**
 * Generate a UUID (Universally Unique Identifier) string of the specified version.
 * 
 * Supported UUID version includes:
 * 
 * - `1` - Time-based UUID
 * - `3` - Name-based UUID using MD5 hashing
 * - `4` - Randomly generated UUID
 * - `5` - Name-based UUID using SHA-1 hashing
 * - `7` - Unix Epoch time-based UUID (RFC 4122)
 * 
 * V3/V5 namespace aliases:
 * - `dns`  - Domain names
 * - `url`  - URLs
 * - `oid`  - Object identifiers
 * - `x500` - X.500 distinguished names
 *
 * @param int $version The version of the UUID to generate (default: 4).
 * @param string|null $namespace The namespace versions (`v3` and `v5`) (e.g, `dns`, `url`,  `xyz...`).
 * @param string|null $name The name for versions (`v3` and `v5`).
 * @param int|null $time Optional timestamp: 
 *              - For v1: 100-nanosecond intervals since UUID epoch. 
 *              - For v7: milliseconds since Unix epoch.
 * 
 * @return string Return the generated UUID string.
 * @throws InvalidArgumentException If:
 * - the version is unsupported,
 * - namespace or name is missing for v3/v5,
 * - namespace is invalid,
 * - or timestamp is invalid for v1/v7.
 * 
 * @link https://luminova.ng/docs/0.0.0/utility/helpers
 * @see Uuid::generate()
 * @see Uuid::isValid()
 * @see ulid()
 * 
 * @example - Example:
 * ```php
 * $version = 4;
 * 
 * $uuid = uuid($version); 
 * echo $uuid; // uuid-string
 * ```
 */
function uuid(
    int $version = 4, 
    ?string $namespace = null, 
    ?string $name = null,
    ?int $time = null
): string 
{
    return Uuid::generate($version, $namespace, $name, $time);
}

/**
 * Generate a ULID (Universally Lexicographically Sortable Identifier).
 *
 * ULID is composed of:
 * - 48-bit timestamp (milliseconds since Unix epoch)
 * - 80-bit cryptographically secure randomness
 *
 * Encoding:
 * - Crockford Base32 (default, URL-safe, sortable)
 *
 * @return string Return 26-character ULID string using Crockford Base32.
 * 
 * @link https://luminova.ng/docs/0.0.0/utility/helpers
 * @see Ulid::generate()
 * @see uuid()
 */
function ulid(): string
{
    return Ulid::generate();
}

/**
 * Escapes a string or an array of values for the specified output context.
 *
 * **Supported contexts:**
 *
 * - `html` - Escape HTML content.
 * - `attr` - Escape HTML attribute values.
 * - `js`   - Escape JavaScript strings.
 * - `css`  - Escape CSS values.
 * - `url`  - Escape URLs.
 * - `raw`  - Return the value without escaping.
 *
 * When an array is provided, all string values are escaped recursively.
 * If an array key matches one of the supported context names, that context
 * is used for the corresponding value. Otherwise, the default context is used.
 *
 * @param array<string|int,mixed>|string $input The string or array to escape.
 * @param string $context The default escape context (default: `'html'`).
 * @param string $encoding The character encoding to use (default: `'utf-8'`).
 *
 * @return string|array<string|int,mixed> The escaped string or array.
 *
 * @throws InvalidArgumentException If the encoding is empty, invalid, or unsupported.
 * @throws \Luminova\Exceptions\BadMethodCallException If an unsupported escape context is specified.
 *
 * @link https://luminova.ng/docs/0.0.0/functions/escaper
 *
 * @example Escape a string:
 * ```php
 * $value = escape('foo <script>alert("XSS")</script>');
 * ```
 *
 * @example Escape an array with per-value contexts:
 * ```php
 * $value = escape([
 *     'html' => 'foo <script>alert("XSS")</script>',
 *     'url'  => 'https://example.com?foo=bar',
 * ]);
 * ```
 */
function escape(array|string $input, string $context = 'html', string $encoding = 'utf-8'): array|string
{
    if (\is_array($input)) {
        return array_escape($input, $context, $encoding);
    }

    $context = \strtolower($context);

    if ($context === 'raw') {
        return $input;
    }

    if($context === 'html' || $context === 'attr'){
        return \htmlspecialchars(
            $input, 
            \ENT_QUOTES | \ENT_SUBSTITUTE, 
            $encoding
        );
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
 * @param string $input The input string to be sanitized.
 * @param string $type The expected data type (e.g., `Util::SANITIZE_EMAIL`, `Util::SANITIZE_*`).
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
function sanitize(
    string $input, 
    string $type = Util::SANITIZE_DEFAULT, 
    ?string $replacer = ''
): ?string 
{
    return Util::sanitize($input, $type, $replacer);
}

/**
 * Strictly sanitizes input according to the specified type.
 *
 * Unlike `sanitize()`, `strict()` will **always throw an exception** if input contains invalid characters
 * instead of replacing them. This ensures the input fully conforms to the expected format.
 *
 * @param string $input The string to validate strictly.
 * @param string $type The expected type (default: `Util::SANITIZE_DEFAULT`).
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
function strict(string $input, string $type = Util::SANITIZE_DEFAULT): string 
{
    $result = Util::sanitize($input, $type, null);

    if ($result === null) {
        throw new InvalidArgumentException(
            "Input contains invalid characters for type '{$type}'"
        );
    }

    return $result;
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
 * @param string|'object'|'array'|'instance' $return Desired return type (default: `object`).
 * @param bool $shared If true, reuse a static `UserAgent` instance (default: true).
 * 
 * @return array<string,mixed>|object{string,mixed}|UserAgent|false Return parsed browser information, 
 *         `UserAgent` instance, or `false` if detection fails.
 * 
 * @link https://luminova.ng/docs/0.0.0/http/user-agent
 */
function browser(?string $userAgent = null, string $return = 'object', bool $shared = true): mixed
{
    if ($return !== 'instance') {
        $asArray = ($return === 'array');

        if(\ini_get('browscap')){
            $browser = \get_browser($userAgent, $asArray);

            if ($browser !== false) {
                return $browser;
            }
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
 * Set the HTTP response status code.
 *
 * Validates the given status code and updates the current HTTP response status.
 * A status code of `0` is treated as the default `200 OK` response.
 *
 * Also updates `$_SERVER['REDIRECT_STATUS']` for compatibility with environments
 * or applications that rely on this server variable.
 *
 * @param int $status HTTP response status code (default: 200).
 *
 * @return bool Returns `true` if the status code was set successfully, or
 *              `false` if the status code is invalid or cannot be applied.
 */
function http_status_header(int $status = 200): bool
{
    if ($status === 0) {
        $status = 200;
    }

    if (!HttpStatus::isValid($status)) {
        return false;
    }

    if (\http_response_code($status) === false) {
        return false;
    }

    $_SERVER['REDIRECT_STATUS'] = (string) $status;

    return true;
}

/**
 * Determine whether the application is running on a specific platform.
 *
 * This is a convenience wrapper around {@see Runtime::isPlatform()}.
 * It supports predefined operating system, BSD, Solaris, AWS, and Azure
 * platform identifiers, as well as custom platform names.
 *
 * **Common Platform Values:**
 *
 * - `mac` — macOS.
 * - `windows` — Windows.
 * - `linux` — Linux.
 * - `freebsd` — FreeBSD.
 * - `openbsd` — OpenBSD.
 * - `bsd` — Any BSD-based operating system.
 * - `solaris` — Solaris.
 * - `aws` — AWS runtime environment.
 * - `azure` — Azure runtime environment.
 *
 * @param string $os Platform name to check, such as `windows`, `linux`,
 *        `mac`, `aws`, or `azure`.
 *
 * @return bool `true` if the current platform matches the specified name,
 *              otherwise `false`.
 *
 * @example - Examples:
 * ```php
 * is_platform('windows');
 * is_platform('linux');
 * is_platform('mac');
 * is_platform('aws');
 * is_platform('azure');
 * ```
 *
 * @see Runtime::isPlatform()
 */
function is_platform(string $os): bool
{
    return Runtime::isPlatform($os);
}

/**
 * Determine whether the application is running in CLI mode.
 *
 * This is a convenience wrapper around {@see Runtime::isCommand()}.
 *
 * @return bool `true` if the application is running in CLI mode, otherwise `false`.
 *
 * @see Runtime::isCommand()
 */
function is_command(): bool
{
    return Runtime::isCommand();
}

/**
 * Check if a given value is a blob (stream resource).
 *
 * @param mixed $value Value to check.
 * 
 * @return bool Return true if the value is a stream resource, false otherwise.
 * @see Filesystem::isResource()
 */
function is_blob(mixed $value): bool 
{
    return Filesystem::isResource($value, 'stream');
}

/**
 * Checks if the given IP address is a Tor exit node.
 *
 * @param string|null $ip The ip address to check, if NULL get current ip address.
 * @param int $expiration The expiration time to request for new exit nodes from tor api (default: 2592000 30 days).
 * 
 * @return bool Return true if ip address is a Tor exit node, otherwise false.
 * @throws FileException Throws if error occurs or unable to read or write to directory.
 * @link https://luminova.ng/docs/0.0.0/functions/ip
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
 * @link https://luminova.ng/docs/0.0.0/functions/ip
 */
function ip_address(bool $ipInfo = false, array $metadata = []): string|object|null
{
    return $ipInfo 
        ? IP::info(metadata: $metadata) 
        : IP::get();
}

/**
 * Get the PHP script executable path.
 * 
 * This function attempts to determine the path to the PHP executable.
 * 
 * @param string|null $version Optional PHP version to check for (e.g., '8.0.0').
 *
 * @return string|null Return PHP executable path or null.
 * @see \Luminova\Command\Terminal::whichPhp()
 */
function which_php(?string $version = null): ?string
{
    return \Luminova\Command\Terminal::whichPhp($version);
}

/**
 * Translate a message based on the current language context.
 * 
 * This function retrieves a translation string based on the provided lookup key, 
 * with optional placeholders for dynamic content.
 * 
 * Placeholder Pattern:
 * 
 * - sing index: "Error name {0} and email {1}"
 * - Using keys: "Error name {name} and email {email}"
 *
 * @param string $lookup The language context annotation line to lookup (e.g, `App.error.foo.bar`).
 * @param string|null $default Optional fallback message or translation if not found.
 * @param string|null $locale Optional translation locale to use. 
 *              If null the default application will be used.
 * @param array<string|int,string|int> $placeholders Optional replaceable placeholders key-pir to translate in message.
 * 
 * 
 * @return string Return translated message.
 * @throws \Luminova\Exceptions\NotFoundException if translation is not found and no default provided.
 * 
 * @link https://luminova.ng/docs/0.0.0/languages/translate
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

/**
 * Resolves system and application path.
 * 
 * This function resolves application paths based on names 
 * and ensure separator are normalized to based on environment `unix` or `windows` specific style.
 * 
 * **Available Paths:**
 * 
 * - `app`          - Application root directory.
 * - `system`       - Luminova Framework and third-party plugins root directory.
 * - `plugins`      - Third-party plugins root directory.
 * - `library`      - Custom libraries root directory.
 * - `controllers`  - Application controllers directory.
 * - `writable`     - Application writable directory.
 * - `logs`         - Application logs directory.
 * - `caches`       - Application cache directory.
 * - `public`       - Application public directory (front controller).
 * - `assets`       - Application public assets directory.
 * - `views`        - Application template views directory.
 * - `routes`       - Application method-based routes directory.
 * - `languages`    - Application language pack directory.
 * - `service`      - Application cached services directory.
 * 
 * @param string $name The path name to resolves.
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
 * Resolve system and application path.
 *
 * @param string $name The path name to resolves.
 * 
 * @return string Return directory path, windows, unix or windows style path.
 * 
 * @deprecated Use `system_path()` instead.
 * > This function is retained for backward compatibility.
 */
function path(string $name): string
{
    return Filesystem::path($name);
}

/**
 * Create a directory if it does not already exist.
 * 
 * If the directory exists, the function returns true without error.
 * Supports recursive creation of nested directories.
 * 
 * @param string $path Directory path to create.
 * @param int|null $permissions Unix permission mode. 
 *      If null, uses (default: `App\Config\Security::$dirPermissions`).
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
        $permissions ?? Security::$dirPermissions, 
        $recursive
    );
}

/**
 * Atomically write data to a file.
 *
 * Writes string data or a readable stream to a temporary file in the target
 * directory, then atomically replaces the target file using rename().
 *
 * The temporary file is created in the same directory to ensure the rename
 * operation remains atomic on the same filesystem.
 *
 * @param string          $path       Target file path.
 * @param string|resource $data       String data or a readable stream resource.
 * @param int|null        $mode       File permissions. Null preserves existing permissions.
 * @param int             $chunkSize  Stream read chunk size.
 * @param bool            $exclusiveLock Whether to acquire an exclusive file lock.
 *
 * @return bool True on success.
 *
 * @throws RuntimeException If the file cannot be written.
 */
function atomic_write(
    string $path,
    mixed $data,
    ?int $mode = null,
    int $chunkSize = 1024 * 1024,
    bool $exclusiveLock = false
): bool 
{
    return Filesystem::atomicWrite(
        $path,
        $data,
        $mode,
        $chunkSize,
        $exclusiveLock
    );
}

/**
 * Create a writable temporary directory.
 *
 * Attempts to create a unique temporary directory using an available writable
 * storage location. Unless local storage is explicitly requested, the system
 * temporary directory is preferred before falling back to application storage.
 *
 * When the system temporary directory is used and no prefix is provided, a
 * sanitized application name is used as the directory prefix.
 *
 * @param string|null $prefix Optional directory prefix.
 * @param bool        $localOnly Whether to use only application-local storage.
 *
 * @return string|null The created temporary directory path, or `null` on failure.
 *
 * @see get_temp_file()
 */
function temp_dir(?string $prefix = null, bool $localOnly = false): ?string
{
    return Filesystem::createTempDir($prefix, $localOnly);
}

/**
 * Creates a writable temporary file.
 *
 * Creates a temporary file inside the application temporary directory or the
 * system temporary directory when available. The file name can be customized
 * using a prefix and extension.
 *
 * @param string|null $filename Optional filename prefix.
 * @param string|null $extension Optional file extension without a leading dot.
 * @param bool $localOnly Whether to use only the application writable temp
 *                        directory instead of the system temporary directory.
 *
 * @return string|null Returns the created temporary file path, or `null` if the
 *                     temporary directory or file cannot be created.
 * 
 * @see temp_dir()
 */
function get_temp_file(
    ?string $filename = null,
    ?string $extension = null,
    bool $localOnly = false
): ?string
{
    return Filesystem::createTempFile(
        $filename,
        $extension,
        $localOnly
    );
}

/**
 * Create a unique temporary file name in the specified directory.
 *
 * Attempts to create a temporary file using PHP's native `tempnam()` function.
 * If the native temporary file creation fails, a unique file name is generated
 * manually and created using `touch()`.
 *
 * The generated file is created immediately to reserve the name and reduce the
 * possibility of another process using the same filename.
 *
 * @param string $directory Directory where the temporary file should be created.
 * @param string $prefix    Prefix to prepend to the generated filename.
 *
 * @return string|null The path to the created temporary file, or `null` if the
 *                     directory is not writable or the file cannot be created.
 */
function create_temp_name(
    string $directory,
    string $prefix = 'tmp_'
): ?string
{
    return Filesystem::createTempName(
        $directory,
        $prefix
    );
}

/**
 * Get class basename from namespace or object.
 * 
 * This function extracts the class name without the namespace 
 * from a fully qualified class name or an object instance.
 * 
 * @param string|object $from Class namespace or class object.
 * @param bool $validate Whether to validate that each class is already loaded.
 * 
 * @return string Return the class basename.
 * @see Luminova::getClassBasename()
 */
function get_class_name(string|object $from, bool $validate = false): string 
{
    return Luminova::getClassBasename(
        \is_string($from) ? $from : \get_class($from),
        $validate
    );
}


/**
 * Determine whether a class has already been loaded.
 *
 * Unlike {@see class_exists()}, this function does not trigger the autoloader.
 *
 * @param string $class Fully qualified class name.
 *
 * @return bool `true` if the class is already loaded, otherwise `false`.
 */
function class_loaded(string $class): bool
{
    return class_exists($class, false);
}

/**
 * Detect the mime type of a file or raw data.
 *
 * If the input string is a path to an existing file, it uses `\finfo->file()`,
 * otherwise it treats the input as raw binary and uses `\finfo->buffer()`.
 *
 * @param string $input File path or raw binary string to extract mime from.
 * @param string|null $magicDatabase Optional mime magic database file (e.g, `\path\custom.mgc`).
 * @param string|null $customDatabase Optional mime custom database file (e.g, `\path\custom.json`).
 * 
 * @return string Return the detected mime type (e.g. "image/jpeg"), or false if detection fails.
 * @throws RuntimeException If database file is not readable or if there is an error reading the file.
 * @throws InvalidArgumentException If the extension or mime type is invalid.
 */
function get_mime(
    string $input, 
    ?string $magicDatabase = null, 
    ?string $customDatabase = null
): string|bool
{
    if($input === ''){
        return 'text/plain';
    }

    if($customDatabase !== null){
        Mime::database($customDatabase);
    }

    return Mime::guess($input, $magicDatabase);
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
function write_content(
    string $filename, 
    mixed $content, 
    int $flags = 0, 
    mixed $context = null
): bool 
{
    return Filesystem::write(
        $filename, 
        $content, 
        $flags, 
        context: $context
    );
}

/**
 * Convert status to int, return run status based on result.
 * 
 * In CLI, 0 is considered success while 1 is failure.
 * In some occasions, void or null may be returned, treating it as success.
 * 
 * @param mixed $result The response from the callback function or method to check (e.g, `void`, `bool`, `null`, `int`).
 * @param bool $returnInt Whether to return int or bool (default: int).
 * 
 * @return bool|int Return status response as boolean or integer value.
 */
function status_code(mixed $result = null, bool $returnInt = true): bool|int
{
    if ($result === false || (\is_int($result) && $result == 1)) {
        return $returnInt ? 1 : false;
    }

    return $returnInt ? (int) $result : true;
}

/**
 * Runtime shared memory store for application-wide values.
 * 
 * This function allows you to store and retrieve values in a shared memory, 
 * the values are not persisted across requests. 
 * 
 * It is useful for storing configuration, state, or other data that needs to be accessed globally within the application.
 *
 * @param string $key The key to identify the value.
 * @param mixed $value The value to store (optional).
 * @param mixed $default The default value return if key not found (default: NULL).
 * 
 * @return mixed Returns the value associated with the key, or default value if the key does not exist.
 * 
 * @see Runtime::get()  
 * @see Runtime::set()
 */
function shared(string $key, mixed $value = null, mixed $default = null): mixed 
{
    return ($value === null) 
        ? (Runtime::get($key) ?? $default)
        : Runtime::set($key, $value);
}

/**
 * Retrieve a session value or the session instance.
 *
 * When `$key` is provided, the value associated with the key is returned.
 * When `$key` is `null` or empty, the session instance itself is returned.
 *
 * @param string|null $key Session key to retrieve, or `null` to return the
 *        session instance.
 * @param bool $shared Whether to reuse the shared session instance.
 * @param SessionManagerInterface|null $manager Session manager to use, or
 *        `null` to use the default manager.
 *
 * @return \Luminova\Sessions\Session|mixed The session value when `$key` is provided, otherwise the
 *               session instance.
 *
 * @link https://luminova.ng/docs/0.0.0/sessions/session
 */
function session(
    ?string $key = null, 
    bool $shared = true, 
    ?SessionManagerInterface $manager = null
): mixed
{
    return ($key !== null && $key !== '') 
        ? Kernel::resolve(Kernel::SERVICE_SESSION, $shared, $manager)
            ->get($key) 
        : Kernel::resolve(Kernel::SERVICE_SESSION, $shared, $manager);
}

/**
 * Create and return cookie instance.
 *
 * @param string $name Name of the cookie.
 * @param string $value Value of the cookie.
 * @param array  $options Options to be passed to the cookie.
 * @param bool $shared Use shared instance (default: false).
 * 
 * @return Cookie<\Luminova\Interface\CookieInterface,LazyObjectInterface> Return cookie instance.
 * @link https://luminova.ng/docs/0.0.0/cookies/cookie
 */
function cookie(
    string $name, 
    string $value = '', 
    array $options = [], 
    bool $shared = false
): Cookie
{
    return Factory::cookie($name, $value, $options, $shared);
}

/**
 * Returns a shared instance of a class in factory or factory instance if context is null.
 * 
 * @template T of object
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
 * -   'renderer'       `\Luminova\Storage\Renderer`
 * -   'validate'       `\Luminova\Security\Validation`
 * -   'response'       `\Luminova\Template\Response`
 * -   'request'        `\Luminova\Http\Request`
 * -   'service'        `\Luminova\Foundation\Module\Service`
 * -   'notification'   `\Luminova\Notifications\Firebase\Notification`,
 * -   'caller'         `\Luminova\Foundation\Module\Caller`
 * 
 * @return T|object|Factory|null Return instance of factory or instance of factory class, otherwise null.
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
 * @link https://luminova.ng/docs/0.0.0/boot/factory
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
 * @template T of object
 * 
 * @param class-string<T>|string|null $service The service class name or alias.
 * @param bool $shared Allow shared instance creation (default: true).
 * @param bool $serialize Allow object serialization (default: false).
 * @param mixed $arguments [, mixed $... ] Service initialization arguments.
 * 
 * @return T|object|Service|null Return service class instance or instance of service class.
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
 * @link https://luminova.ng/docs/0.0.0/boot/service
 */
function service(
    ?string $service = null, 
    bool $shared = true, 
    bool $serialize = false, 
    mixed ...$arguments
): ?object
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
 * @param class-string<T>|string $service The class name or alias, to delete and clear it cached.
 * 
 * @return bool Return true if the service was removed or cleared, false otherwise.
 * @link https://luminova.ng/docs/0.0.0/boot/service
 */
function remove_service(?string $service = null): bool
{
    if($service === null){
        return Factory::service()->clear();
    }

    return Factory::service()->delete($service);
}

/**
 * Import a custom library from the libraries/libs directory.
 *
 * This function attempts to load a PHP library file from the `/libraries/libs/` path.
 *
 * @param string $library The library path or name (e.g., 'Foo/Bar/Baz' or 'Foo/Bar/Baz.php').
 * @param bool $throw  If true, throws an exception when the library file is not found.
 *
 * @example - Example:
 * ```php
 * import_lib('Foo/Bar/Baz');       // Loads /libraries/libs/Foo/Bar/Baz.php
 * import_lib('Foo/Bar/Baz.php');   // Loads /libraries/libs/Foo/Bar/Baz.php
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

    $path = Luminova::root('/libraries/libs/', \trim($library, '/\\'));

    try {
        Boot::import($path, throw: true);
        return true;
    } catch (Throwable $e) {
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
 * Get a cache instance based on the specified driver.
 * 
 * @param string $driver The cache driver to use (e.g., "filecache", "redis", "memcached").
 * @param string|null $storage Optional storage path or connection string for the cache driver (default: null):
 * @param string|null $persistentId Optional persistent ID for the cache connection (default: null).
 * 
 * @return FileCache|RedisCache|MemoryCache Return new instance of instance of cache class based on specified driver.
 * @throws ClassException If unsupported driver is specified.
 * @throws \Luminova\Exceptions\CacheException If there is an issue initializing the cache.
 * 
 * @see \App\Kernel::getCacheProvider()  For more details on cache provider initialization.
 * @see Luminova\Foundation\Core\ServiceKernel For more details on kernel service retrieval.
 */
function cache(
    string $driver = 'filecache', 
    ?string $storage = null, 
    ?string $persistentId = null
): FileCache|RedisCache|MemoryCache 
{
    return Kernel::resolve(
        Kernel::SERVICE_CACHE, 
        true, 
        $driver, 
        $storage, 
        $persistentId
    );
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

/**
 * Validate input fields or return validation instance.
 * 
 * If input and rules are specified, it will do the validation and return instance 
 * which you can then called method `$validation->isPassed()`
 * 
 * To check if passed or failed, or get the error information.
 *
 * @param array $inputs Input fields to validate on (e.g, `$_POST`, `$_GET` or `$this->request->getBody()`).
 * @param array $rules Validation filter rules to apply on each input field.
 * @param array $messages Validation error messages to apply on each filter on input field.
 * 
 * @return InputValidationInterface Return instance of input validation object.
 * 
 * @see Luminova\Interface\Rule
 * @link https://luminova.ng/docs/0.0.0/security/validation
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
 *      $error = $validate->getError();
 *      $errors = $validate->getErrors();
 *      echo $error;
 *      var_dump($errors);
 * }
 * ```
 */
function validate(
    ?array $inputs = null, 
    ?array $rules = null, 
    array $messages = []
): InputValidationInterface 
{
    $instance = Factory::validate();

    if($messages){
        $instance->setMessages($messages);
    }

    if ($rules) {
        $instance->setRules($rules);
    }

    if ($inputs) {
        $instance->setBody($inputs);
    }

    $instance->validate();
    
    return $instance;
}

/**
 * Convert a file path to a display-friendly format.
 * 
 * @param string $path The file path to convert.
 * 
 * @return string Return the cleaned path safe for display.
 * @deprecated Use display_path() instead for clarity and consistency.
 */
function filter_paths(string $path): string 
{
    return Luminova::toDisplayPath($path);
}

/**
 * Alias of `base_url()`. Use `base_url()` instead for clarity and consistency.
 *
 * @param string|null $route
 * @param boolean $relative
 * 
 * @return string
 * @deprecated Use base_url() instead for clarity and consistency.
 */
function start_url(?string $route = null, bool $relative = false): string
{
    return Luminova::toBaseUrl($route, $relative);
}