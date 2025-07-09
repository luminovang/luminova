# PHP Luminova Framework

[![Author](https://img.shields.io/badge/author-@peterchig-blue.svg)](https://instagram.com/peterchig)
![PHP 8.0+](https://img.shields.io/badge/php-min%208.0.0-red.svg)
[![Source Code](https://img.shields.io/badge/source-luminovang/luminova-blue.svg)](https://github.com/luminovang/luminova)
[![Latest Version](https://img.shields.io/github/tag/luminovang/luminova.svg)](https://github.com/luminovang/luminova/releases)
[![Framework Source Code](https://img.shields.io/badge/source-luminovang/framework-blue.svg)](https://github.com/luminovang/framework)
[![Framework Latest Version](https://img.shields.io/github/tag/luminovang/framework.svg)](https://github.com/luminovang/framework/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/luminovang/luminova/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/luminovang/framework.svg)](https://packagist.org/search/?tags=php%20luminova)
![Endpoint Badge](https://img.shields.io/endpoint?url=https%3A%2F%2Fluminova.ng%2Fdownloads)


![Local Image](https://github.com/luminovang/luminova/raw/main/docs/logo.svg)


Luminova is a high-performance PHP framework designed for speed, efficiency, and developer productivity. It allows you to customize the framework to your project via the `.env` file, enabling only the features you need. This modular approach ensures optimal performance while giving you full control over template rendering modes and coding styles.

Within Luminova templates, `View` objects are accessible directly in your view files. You can call application methods and properties using `$this` or `$self` for stricter isolation mode.

Ready to try Luminova with your next projects? Explore the [official documentation](https://luminova.ng/docs) or join the community for tips, tutorials, and coding insights on our [YouTube channel](https://www.youtube.com/@luminovang).


> **Luminova Skeleton**
> This repository gives you everything you need to start a new Luminova project: a clean directory structure, essential configuration files, and the core bootstrapping setup.

---

## Key Features

Luminova comes packed with tools to speed up development while keeping your application organized and performant.

* **Database Builder:** ORM for easier CRUD operations and database management.
* **MVC & HMVC Architecture:** Supports both MVC and Hierarchical MVC for modular applications.
* **Flexible Routing and Controllers:** Fast, dynamic routing with attribute or method-based configuration.
* **HTTP and CLI Routing:** Handing dynamic routing for HTTP or CLI, similar to HTTP routing.
* **Templating:** Native PHP templating with inheritance, caching, and optional engines like Twig or Smarty.
* **Error Handling:** Comprehensive error tracking, including non-fatal inline errors.
* **Session Management:** Simplified session handling for authentication and user state.
* **Cloud Storage:** Integrations with AWS, Azure, Google Cloud, and more.
* **File Management:** Secure file delivery with temporary or permanent URLs.
* **Sitemap Generator:** Generate sitemaps easily using NovaKit.
* **Schema Objects:** Generate structured schema objects for pages.
* **Command Line Tools:** Build full CLI applications with built-in routing support.
* **CLI Tooling:** Command Line Helper for application development/production (`php novakit`).
* **AI Models:** Extend your application with AI-powered features.
* **Flexible Database Failover:** Instant failover support for backup databases.
* **Security:** Implements multiple security layers to protect user data.
* **Request Handling:** Securely process incoming and outgoing HTTP requests.
* **Email:** Send emails with support for rendering full template content.
* **Translation & Localization:** Build multilingual applications.
* **Encryption:** Supports multiple encryption handlers and methods.
* **Services:** Share classes and objects across your codebase with caching support.

---

## Requirements

* PHP **8.0+**
* Composer
* `ext-mbstring` enabled
* `ext-json` enabled
* `psr/http-client` installed
* `psr/log` installed
* `ext-json` enabled
* Web server (Apache, Nginx, or PHP built-in)


---

## Installation

Create a new luminova project using Composer:

```bash
composer create-project luminovang/luminova my-project
```

## Encryption Key

Generate your application encryption key:

```bash
php novakit generate:key
```

## Start Development Server

To start PHP development server.

Run the following `novakit` command:

```bash
php novakit server
```

> Visit: http://localhost:8000


## Sitemap Generator

To generate your website XML sitemap:

Use the below `novakit` command:

```bash
php novakit sitemap
```

---

## Directory Structure

```
app/
    │── Controllers/     # Route handlers
    │── Config/          # Application settings
    │── Models/          # Application data models
    │── Views/           # UI templates
    │── Modules/         # HMVC modules (optional)
bootstrap/      # Core Helper Functions
bin/            # Novakit Console Commands
public/         # Front Controller (Framework entry point)
resources/
        │── Views/     # MVC Template files
routes/         # Routes for Method-Based Routing
writeable/      # Private Storage, Logs, cache, Sessions, and Temp Files
system/         # Core Modules
    │── plugins/       # ThirdParty Vendor Files
```

---


## Basic Usage

### Defining Routes

Luminova supports flexible routing using **PHP Attributes** or **Method-Based Routing**.

#### Attribute Routing

Enable attribute routing by setting the environment variable:

```ini
feature.route.attributes = true
```

Example:

```php
namespace App\Controllers\Http;

use Luminova\Base\Controller;
use Luminova\Attributes\Route;
use Luminova\Attributes\Prefix;

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

#### Method-Based Routing

Disable attribute routing by setting:

```ini
feature.route.attributes = false
```

Example:

```php
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

Then register your route:

```php
// /routes/web.php
$route->get('/', 'MainController::index');
```

**Using a Callback Handler:**

```php
// /routes/web.php
use Luminova\Routing\Router;
use function Luminova\Funcs\view;

$router->get('/', function (): int {
    return view('index');
});
```

---

### Rendering Templates

#### Inside a Controller

```php
return $this->view(
    string $template,           // Template name
    array $options = [],        // View options
    string $type = View::HTML,  // Template content type
    int $status = 200           // HTTP status code
);
```

**Alternative Using Template Property (`$tpl`):**

```php
return $this->tpl->view(
    string $template,           // Template name
    string $type = View::HTML   // Template content type
)->render(
    array $options = [],        // View options
    int $status = 200           // HTTP status code
);
```

**Global Helper Function:**

You can render views outside of a controller using the `view()` helper:

```php
use function Luminova\Funcs\view;

return view(
    string $template,           // Template name
    array $options = [],        // View options
    string $type = View::HTML,  // Template content type
    int $status = 200           // HTTP status code
);
```

> To learn more about Luminova, see the **[official documentation](https://luminova.ng/docs/)**.


---

### Quick Tips

**Q:** My session works locally but not on production.
**A:** Update `$sessionDomain` in `/app/Config/Session.php` to match your production domain (e.g., `'.' . APP_HOST`). Also check `Cookie.php` configuration.

**Q:** CSS or images are broken on production.
**A:** Ensure `app.environment.mood` is set to `production` in your environment file to serve assets correctly.

---

## Contributing

If your contribution targets the core framework or its docs, use these repositories instead:

* **[Framework Source Code](https://github.com/luminovang/framework)**
* **[Documentation](https://github.com/luminovang/documentation)**

For this skeleton repository, pull requests should focus on:

* Improving the project structure
* Fixing default configuration files
* Enhancing developers experience

---

### Feedback

We welcome your feedback! Contact us at [peter@luminova.ng](mailto:peter@luminova.ng) with suggestions, feature requests, or tutorial ideas.

Don’t forget to ⭐ Luminova on GitHub. Your support helps us continue improving the framework and adding new features.

---

## License

Luminova is open-source and released under the MIT License.