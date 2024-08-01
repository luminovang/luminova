<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace Luminova;

use \Luminova\Application\Foundation;
use \App\Application;

final class Boot 
{
    /**
     * Initializes the HTTP environment for web application.
     * Sets up the error handler, finishes bootstrapping.
     *
     * @return Application The application instance.
     */
    public static function http(): Application
    {
        Foundation::initialize();
        self::finish();

        return Application::getInstance();
    }

    /**
     * Sets up the CLI (Command Line Interface) environment.
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

        // Define STDOUT if not already defined
        defined('STDOUT') || define('STDOUT', 'php://output');

        // Define STDIN if not already defined
        defined('STDIN') || define('STDIN', 'php://stdin');

        // Define STDERR if not already defined
        defined('STDERR') || define('STDERR', 'php://stderr');

        self::finish();
    }

    /**
     * Performs the initial setup tasks required for the application to run.
     * Load all necessary files, setting timezone and execution limits, and configuring script behavior.
     * 
     * @return void
     */
    public static function warmup(): void
    {
        require_once __DIR__ . '/../bootstrap/constants.php';
        require_once __DIR__ . '/../bootstrap/functions.php';
        require_once __DIR__ . '/Errors/ErrorStack.php';
        require_once __DIR__ . '/Application/Foundation.php';

        /**
         * Set default timezone
        */
        date_default_timezone_set(env("app.timezone", 'UTC'));

        /**
         * Limits the maximum execution time
        */
        set_time_limit((int) env("script.execution.limit", 30));

        /**
         * Set whether a client disconnect should abort script execution
        */
        ignore_user_abort((bool) env('script.ignore.abort', true));
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
}

Boot::warmup();