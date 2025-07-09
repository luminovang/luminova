<?php 
/**
 * Luminova Framework Bootstrapping.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova;

use \Throwable;
use \App\Application;
use \Luminova\Luminova;
use \Luminova\Exceptions\RuntimeException;

/**
 * Luminova framework autoloader helper class.
 *
 * Luminova relies on Composer for autoloading. This class simplifies the process 
 * of loading both Composer and framework modules without needing to manually include 
 * `plugins/vendor/autoload.php` and other boot files.
 * 
 * To autoload required files, include `/system/Boot.php` and call the appropriate 
 * static method based on your environment (`http()`, `cli()`, or `autoload()`).
 * 
 * @see https://luminova.ng/docs/0.0.0/boot/autoload
 */
final class Boot 
{
    /**
     * Indicates whether warmup initialization has already been performed.
     *
     * @var bool $isWarmed
     */
    private static bool $isWarmed = false;

    /**
     * Initializes the HTTP environment for web and API applications.
     *
     * Warms up the application, registers error handlers, and loads core modules.
     * 
     * > Similar to the `http()` method but does not return the application instance.
     *
     * @example Usage:
     * ```php
     * use \Luminova\Boot;
     * 
     * require_once __DIR__ . '/system/Boot.php';
     * 
     * Boot::init();
     * ```
     * @see http()
     * @see autoload()
     */
    public static function init(): void
    {
        self::warmup();
        Luminova::initialize();
        self::finish();
    }

    /**
     * Initializes all required autoload modules.
     *
     * Wrapper for `warmup()` and `finish()`. Loads core modules and prepares the application 
     * context without registering error handlers or configuring the CLI environment.
     * 
     * > Use this to ensure Composer and framework modules are available in the current context.
     *
     * @example Usage:
     * ```php
     * use \Luminova\Boot;
     * 
     * require_once __DIR__ . '/system/Boot.php';
     * 
     * Boot::autoload();
     * ```
     * @see init()
     * @see http()
     * @see cli()
     */
    public static function autoload(): void
    {
        self::warmup();
        self::finish();
    }

    /**
     * Prepares the HTTP environment for web and API applications.
     *
     * Typically used in `public/index.php`. Ensures the application is warmed up, 
     * registers error handlers, and loads required core modules before returning 
     * the application instance.
     *
     * @return Application<CoreApplication,LazyInterface> Return the application instance.
     * @example - Usage (public/index.php)
     * 
     * ```php
     * use \Luminova\Boot;
     * 
     * require_once __DIR__ . '/../system/Boot.php';
     * 
     * Boot::http()->router->context(...)->run();
     * ```
     * @see init()
     */
    public static function http(): Application
    {
        self::init();
        return Application::getInstance();
    }

    /**
     * Prepares the CLI environment.
     *
     * Intended for use in custom CLI scripts. Autoloads required files, 
     * enables error reporting, validates the SAPI type, defines CLI constants, 
     * and completes the bootstrapping process.
     *
     * @return void
     * @example - Usage (/bin/script.php)
     * ```php
     * #!/usr/bin/env php
     * <?php
     * use \Luminova\Boot;
     * 
     * require __DIR__ . '/system/Boot.php';
     * 
     * Boot::cli();
     * 
     * // Your cli implementation
     * ```
     */
    public static function cli(): void
    {
        self::warmup();
        ini_set('display_errors', '1');
        error_reporting(E_ALL);

        if (str_starts_with(PHP_SAPI, 'cgi')) {
            echo 'Novakit CLI tool requires php-cli. php-cgi is not supported.';
            exit(1);
        }

        defined('CLI_ENVIRONMENT') || define('CLI_ENVIRONMENT', env('cli.environment.mood', 'testing'));

        self::shouldDefineCommandStreams();
        self::finish();
    }

    /**
     * Loads core modules and prepares the application environment.
     *
     * Ensures constants, functions, error handlers, and the core framework 
     * are loaded in the correct order. Also applies HTTP method spoofing early 
     * to ensure routing uses the correct request method.
     *
     * @return void
     * @ignore
     */
    public static function warmup(): void
    {
        if (self::$isWarmed) {
            self::override();
            return;
        }

        require_once __DIR__ . '/../bootstrap/constants.php';
        self::override();

        require_once __DIR__ . '/../bootstrap/functions.php';
        require_once __DIR__ . '/Errors/ErrorHandler.php';
        require_once __DIR__ . '/Luminova.php';

        self::$isWarmed = true;
    }

    /**
     * Opens a file using `fopen()` with exception handling.
     *
     * If the file can't be opened or an error occurs, a RuntimeException is thrown.
     *
     * @param string $filename Path to the file.
     * @param string $mode File access mode (e.g., 'r', 'w').
     *
     * @return resource|null Return a valid stream resource.
     * @throws RuntimeException If the file can't be opened.
     */
    public static function tryFopen(string $filename, string $mode): mixed
    {
        $error = null;
        $handle = null;

        try {
            $handle = fopen($filename, $mode);
        } catch (Throwable $e) {
            $error = $e;
        }

        if (!is_resource($handle)) {
            throw new RuntimeException(sprintf(
                'BootError: Failed to open file "%s" with mode "%s"%s',
                $filename,
                $mode,
                $error ? ': ' . $error->getMessage() : ''
            ), RuntimeException::ERROR, $error);
        }

        return $handle;
    }

    /**
     * Ensures CLI standard streams (STDIN, STDOUT, STDERR) are defined.
     *
     * Uses `tryFopen()` to safely define them if not already set.
     *
     * @return void
     * @throws RuntimeException If any stream cannot be defined.
     */
    public static function shouldDefineCommandStreams(): void 
    {
        defined('STDIN') || define('STDIN', self::tryFopen('php://stdin', 'r'));
        defined('STDOUT') || define('STDOUT', self::tryFopen('php://stdout', 'w'));
        defined('STDERR') || define('STDERR', self::tryFopen('php://stderr', 'w'));
    }

    /**
     * Finalizes the bootstrapping process.
     *
     * Loads plugin autoloaders and custom framework feature. Sets `IS_UP` constant if not defined.
     *
     * @return void
     */
    private static function finish(): void
    {
        require_once __DIR__ . '/plugins/autoload.php';
        require_once __DIR__ . '/../bootstrap/features.php';
        defined('IS_UP') || define('IS_UP', true);
    }

    /**
     * Overrides the HTTP request method using `_method` or `_METHOD`.
     *
     * Allows browsers or clients to spoof HTTP methods (e.g., PUT, DELETE) via POST.
     *
     * @return void
     */
    private static function override(): void 
    {
        if (PRODUCTION || php_sapi_name() === 'cli') {
            return;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $override = $_POST['_METHOD'] 
                ?? $_POST['_method'] 
                ?? $_GET['_method'] 
                ?? null;

            if (!$override) {
                return;
            }

            $override = strtoupper(trim($override));

            if (in_array($override, ['PUT', 'DELETE', 'PATCH', 'OPTIONS'], true)) {
                $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = $override;
            }
        }
    }
}
Boot::warmup();