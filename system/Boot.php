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

use \Luminova\Application\Foundation;
use \App\Application;

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
        Foundation::initialize();
        self::finish();
    }

    /**
     * Initializes the CLI (Command Line Interface) environment.
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
        defined('STDOUT') || define('STDOUT', 'php://output');
        defined('STDIN') || define('STDIN', 'php://stdin');
        defined('STDERR') || define('STDERR', 'php://stderr');

        self::finish();
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
        require_once __DIR__ . '/Application/Foundation.php';
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
     * Spoofs the HTTP request method based on hidden input or query param `_METHOD_OVERRIDE_` from the client.
     *
     * This method checks for a custom request method specified in the 
     * POST or GET data, allowing clients to simulate different HTTP 
     * methods (e.g., PUT, DELETE) using a hidden form field or query parameter.
     *
     * @return void
     */
    private static function override(): void 
    {
        if (PRODUCTION || php_sapi_name() === 'cli') {
            return;
        }

        $method = (
            $_POST['_OVERRIDE_REQUEST_METHOD_'] ?? 
            $_GET['_override_request_method_'] ??
            $_GET['_OVERRIDE_REQUEST_METHOD_'] ?? 
            null
        );
        if ($method !== null) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        }
    }
}
Boot::warmup();