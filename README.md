<p align="center">
  <img src="https://github.com/luminovang/luminova/raw/main/docs/logo.svg" alt="Luminova PHP Framework" width="100%">
</p>

<h1 align="center">PHP Luminova Framework</h1>

<p align="center">
  A modular PHP framework for building web applications, APIs, and command-line tools.
</p>

<p align="center">
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.1+"></a>
  <a href="https://github.com/luminovang/luminova"><img src="https://img.shields.io/github/tag/luminovang/luminova?label=version" alt="Latest version"></a>
  <a href="https://packagist.org/packages/luminovang/framework"><img src="https://img.shields.io/packagist/dt/luminovang/framework?label=downloads" alt="Packagist downloads"></a>
  <a href="https://github.com/luminovang/luminova/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-2ea44f" alt="MIT license"></a>
  <a href="https://github.com/luminovang/luminova/issues"><img src="https://img.shields.io/github/issues/luminovang/luminova" alt="GitHub issues"></a>
</p>

<p align="center">
  <a href="https://luminova.ng/docs">Documentation</a> ·
  <a href="https://luminova.ng">Website</a> ·
  <a href="https://github.com/luminovang/luminova">Source code</a> ·
  <a href="https://www.youtube.com/@luminovang">Tutorials</a>
</p>

---

## Overview

Luminova is a modular PHP framework for building maintainable web applications and command-line tools. It supports **MVC** and **HMVC** application structures, flexible routing, native PHP views, database access, request handling, and a set of built-in tools for common application tasks.

Configure the features your application needs, keep application code organized, and use the same project to build websites, APIs, and CLI commands.

> This repository provides the Luminova project skeleton and application bootstrapping. For detailed usage and API references, see the [official documentation](https://luminova.ng/docs).

## Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Run the application](#run-the-application)
- [Project structure](#project-structure)
- [Quick start](#quick-start)
  - [Attribute-based routing](#attribute-based-routing)
  - [Method-based routing](#method-based-routing)
  - [Render a view](#render-a-view)
- [NovaKit commands](#novakit-commands)
- [Configuration](#configuration)
- [Documentation and support](#documentation-and-support)
- [Contributing](#contributing)
- [License](#license)

## Features

- **MVC and HMVC:** Organize application code into controllers, models, views, and optional modules.
- **Flexible routing:** Choose PHP attribute-based routes or register routes in route files.
- **HTTP and CLI applications:** Handle web requests and build command-line commands with routing support.
- **Database tools:** Use the database builder and ORM features for common data operations.
- **PHP templating:** Render native PHP views, with support for optional template engines such as Twig and Smarty.
- **Request and response handling:** Work with HTTP requests, responses, and application middleware.
- **Sessions and authentication support:** Manage session state and application user flows.
- **Storage integrations:** Work with local storage and supported cloud storage drivers.
- **File responses:** Deliver files and use supported temporary or permanent file links.
- **Localization:** Build applications that support multiple languages and locales.
- **Email and encryption:** Use framework integrations for common application needs.
- **SEO utilities:** Generate sitemaps and structured data with the available framework tools.
- **Caching and services:** Share application services and use supported caching integrations.
- **AI integrations:** Access supported AI model integrations when your application needs them.
- **Configurable features:** Adjust framework behavior through application environment settings.

See the [documentation](https://luminova.ng/docs) for feature details, configuration options, and supported integrations.

## Requirements

- PHP **8.1 or later**
- [Composer](https://getcomposer.org/)
- PHP extensions:
  - `ext-json`
  - `ext-mbstring`
- Required packages are installed by Composer, including the PSR logging and HTTP client interfaces.
- Apache, Nginx, or the PHP built-in development server.

Some features require additional packages or PHP extensions. Check `composer.json` and the documentation for the requirements of each feature.

## Installation

Create a new project with Composer:

```bash
composer create-project luminovang/luminova my-project
cd my-project
```

Composer installs the project dependencies and runs the configured project setup scripts, including application key generation.

If setup did not generate an application key, run:

```bash
php novakit generate:key
```

## Run the application

Start the built-in development server:

```bash
php novakit server
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

Use the built-in server for local development. Configure Apache or Nginx and your document root appropriately for deployment.

## Project structure

The exact structure can vary as your application grows. The main directories include:

```text
app/
├── Config/             # Application configuration
├── Controllers/        # HTTP and CLI controllers
├── Models/             # Application models
├── Views/              # Application view templates
└── Modules/            # Optional HMVC modules
bootstrap/              # Framework bootstrap and helper functions
bin/                    # Console entry points and commands
public/                 # Public web root and front controller
resources/              # Application resources and views
routes/                 # Method-based route definitions
writeable/              # Private storage, logs, cache, sessions, and temp files
system/                 # Framework system and installed packages
```

Keep private files and runtime data out of the public web root. In production, point your web server at the project's `public/` directory.

## Quick start

### Attribute-based routing

Enable attribute routing in your environment configuration:

```ini
feature.route.attributes = true
```

Then define a controller and route:

```php
// /app/Controllers/Http/MainController.php

<?php

namespace App\Controllers\Http;

use Luminova\Attributes\Prefix;
use Luminova\Attributes\Route;
use Luminova\Base\Controller;

#[Prefix('/(:base)', exclude: ['api'])]
class MainController extends Controller
{
    #[Route('/', methods: ['GET'])]
    public function index(): int
    {
        return $this->view('index');
    }
}
```

This maps a `GET` request to `/` to the controller's `index()` method. See the [routing documentation](https://luminova.ng/docs) for route patterns, middleware, and additional options.

### Method-based routing

If you prefer defining routes in route files, disable attribute routing:

```ini
feature.route.attributes = false
```

Create a controller:

```php
// /app/Controllers/Http/MainController.php

<?php
namespace App\Controllers\Http;

use Luminova\Base\Controller;

class MainController extends Controller
{
  public function index(): int
  {
    return $this->view('index');
  }
}
```

Register the route in `routes/web.php`:

```php
// /routes/web.php

$route->get('/', 'MainController::index');
```

You can also register callback handlers where appropriate. Use the router and helper APIs documented for your installed Luminova version.

### Render a view

From a controller, return a view response:

```php
return $this->view('index');
```

You can pass view options and configure the content type or HTTP status when needed:

```php
return $this->view(
    'index',
    [],             // View options
    View::HTML,     // Content type
    200             // HTTP status
);
```

The view name refers to a template in your application's configured view paths. Refer to the [official documentation](https://luminova.ng/docs) for view locations, rendering modes, and template options.

## Novakit commands

Novakit provides commands for common project tasks. Examples:

| Command | Purpose |
| --- | --- |
| `php novakit list` | List all available command. |
| `php novakit server` | Start the local development server. |
| `php novakit generate:key` | Generate an application encryption key. |
| `php novakit sitemap` | Generate the site's XML sitemap. |

Run the relevant command from your project root. Check the documentation for the full command list and any command-specific options.

## Configuration

Application settings are managed through the project's environment configuration and config files. Review the generated project configuration before deployment.

- Keep production secrets and credentials out of source control.
- Ensure `writeable/` is writable by the application process, with permissions limited to what the application needs.
- Configure session and cookie settings for your production domain.
- Set the appropriate production environment mode so assets and runtime behavior use production settings.
- Install optional packages and PHP extensions only for the features your application uses.

For configuration keys and deployment guidance, use the [Luminova documentation](https://luminova.ng/docs).

## Documentation and support

- **Documentation:** [luminova.ng/docs](https://luminova.ng/docs)
- **Website:** [luminova.ng](https://luminova.ng)
- **Source code:** [github.com/luminovang/luminova](https://github.com/luminovang/luminova)
- **Framework package:** [luminovang/framework on Packagist](https://packagist.org/packages/luminovang/framework)
- **Issues and bug reports:** [GitHub Issues](https://github.com/luminovang/luminova/issues)
- **Tutorials:** [Luminova on YouTube](https://www.youtube.com/@luminovang)
- **Contact:** [peter@luminova.ng](mailto:peter@luminova.ng)

When reporting a problem, include your PHP version, Luminova version, a clear description of the issue, and steps to reproduce it. Remove credentials and other private data from logs before sharing them.

## Contributing

Contributions to the project skeleton are welcome, especially improvements to project structure, default configuration, setup, and developer experience.

1. Open an issue to discuss a significant change or bug.
2. Keep changes focused and consistent with the existing project.
3. Test your changes before opening a pull request.
4. Describe what changed and how you tested it.

For changes to the core framework or its documentation, use the relevant repositories:

- [Framework source code](https://github.com/luminovang/framework)
- [Documentation source](https://github.com/luminovang/documentation)

## License

Luminova is open-source software released under the [MIT License](https://github.com/luminovang/luminova/blob/main/LICENSE).