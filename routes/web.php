<?php
/**
 * Handles all URI prefixes except for `api` (e.g., https://example.com/).
 * This file defines the routes that are not under the `/api` prefix.
 *
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Routing\Router $router The routing instance.
 * @var \App\Application $app The application instance that provides access to the overall application context.
 * @var string $context The name of the current routing context (this file's context).
 */

$router->get('/', 'Welcome::home');