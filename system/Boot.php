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

use \Luminova\Luminova;
use \Luminova\Exceptions\RuntimeException;
use \App\Application;
use \Throwable;

final class Boot 
{
    /**
     * Initializes the HTTP environment for web application, 
     * sets up error handler, finish bootstrapping process and return application object.
     *
     * @return Application Return the application instance.
     */
    public static function http(): Application
    {
        self::init();
        return Application::getInstance();
    }

    /**
     * Initializes the HTTP environment for web application error handler 
     * and finish bootstrapping process.
     *
     * @return void
     */
    public static function init(): void 
    {
        Luminova::initialize();
        self::finish();
    }

    /**
     * Attempts to open a file using `fopen()` and throws a RuntimeException on failure.
     *
     * This method provides a safe wrapper around `fopen()` with enhanced error reporting.
     * If an exception occurs or the returned value is not a valid resource, a RuntimeException is thrown.
     *
     * @param string $filename The path to the file to open.
     * @param string $mode The mode in which to open the file (e.g., 'r', 'w', 'a').
     * 
     * @return resource Return a valid stream resource on success.
     * @throws RuntimeException If the file cannot be opened or an error occurs.
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
                'Failed to open file "%s" with mode "%s"%s',
                $filename,
                $mode,
                $error ? ': ' . $error->getMessage() : ''
            ), RuntimeException::ERROR, $error);
        }

        return $handle;
    }

    /**
     * Initializes the CLI (Command Line Interface) environment.
     * 
     * Define CLI-related constants, finishes bootstrapping.
     * 
     * @return void
     */
    public static function cli(): void
    {
        /**
         * And display errors to developers when using it from the CLI.
         */
        ini_set('display_errors', '1');
        error_reporting(E_ALL);

        /**
         * Refuse to run when called from php-cgi
         */
        if (str_starts_with(PHP_SAPI, 'cgi')) {
            echo 'Novakit cli tool is not supported when running php-cgi. It needs php-cli to function!';
            exit(1);
        }

        // Define CLI environment
        defined('CLI_ENVIRONMENT') || define('CLI_ENVIRONMENT', env('cli.environment.mood', 'testing'));
        self::shouldDefineCommandStreams();
        self::finish();
    }

    /**
     * Ensures that standard CLI streams (STDIN, STDOUT, STDERR) are defined.
     *
     * This method checks if the standard input/output/error stream constants are defined,
     * and if not, it defines them using the appropriate mode.
     *
     * @return void
     * @throws RuntimeException If the file cannot be opened or an error occurs.
     */
    public static function shouldDefineCommandStreams(): void 
    {
        defined('STDIN') || define('STDIN', self::tryFopen('php://stdin', 'r'));
        defined('STDOUT') || define('STDOUT', self::tryFopen('php://stdout', 'w'));
        defined('STDERR') || define('STDERR', self::tryFopen('php://stderr', 'w'));
    }

    /**
     * Performs the initial setup tasks required for the application to run.
     * Load all necessary files, setting timezone and execution limits, and configuring script behavior.
     * 
     * @return void
     * @ignore
     */
    public static function warmup(): void
    {
        require_once __DIR__ . '/../bootstrap/constants.php';
        self::override();

        require_once __DIR__ . '/../bootstrap/functions.php';
        require_once __DIR__ . '/Errors/ErrorHandler.php';
        require_once __DIR__ . '/Luminova.php';
    }

    /**
     * Completes the bootstrapping process.
     *
     * This private method requires autoload plugins and additional features,
     * and defines the IS_UP constant indicating (no composer error), if not already defined.
     */
    private static function finish(): void
    {
        require_once __DIR__ . '/plugins/autoload.php';
        require_once __DIR__ . '/../bootstrap/features.php';
        defined('IS_UP') || define('IS_UP', true);
    }

    /**
     * Spoofs the HTTP POST request method 
     * using `_method` or `_METHOD` as a hiding field or query param .
     *
     * This method checks for a custom request method specified in the 
     * POST data, allowing clients to simulate different HTTP methods 
     * (e.g., PUT, DELETE, PATCH, OPTIONS).
     *
     * @return void
     */
    private static function override(): void 
    {
        if (PRODUCTION || php_sapi_name() === 'cli') {
            return;
        }

        if(strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'){
            $override = $_POST['_METHOD'] 
                ?? $_POST['_method'] 
                ?? $_GET['_method'] 
                ?? null;

            if ($override) {
                $override = strtoupper(trim($override));
                if(in_array($override, ['PUT', 'DELETE', 'PATCH', 'OPTIONS'], true)){
                    $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = $override;
                }
            }
        }
    }
}
Boot::warmup();