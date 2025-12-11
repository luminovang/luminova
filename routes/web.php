<?php
declare(strict_types=1);
/**
 * Handles all URI prefixes except for `api` (e.g., https://example.com/).
 * This file defines the routes that are not under the `/api` prefix. 
 *
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Foundation\Core\Application $app The application object.
 * @var string $context The name of the current routing context (this file's context).
 */
use \Luminova\Routing\Router;

// MVC and HMVC index view
Router::get('/', 'MainController::home');

// Router::get('/', static function (): int {
//   return \Luminova\Funcs\view('index');
// });

// HMVC info view
Router::get('/info', 'MainInfoController::info');