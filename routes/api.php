<?php
declare(strict_types=1);

use \Luminova\Routing\Router;

/**
 * This file handles all URI that start with `api` (e.g, https://example.com/api)
 * 
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Routing\Router $router The routing object.
 * @var \Luminova\Foundation\Core\Application $app The application object.
 * @var string $context The name of the current routing context (this file's context).
 */

Router::post('/info', 'RestController::info');