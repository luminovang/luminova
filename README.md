# PHP Luminova Framework

[![Author](https://img.shields.io/badge/author-@peterchig-blue.svg)](https://instagram.com/peterchig)
[![Source Code](https://img.shields.io/badge/source-luminovang/luminova-blue.svg)](https://github.com/luminovang/luminova)
[![Latest Version](https://img.shields.io/github/tag/luminovang/luminova.svg)](https://github.com/luminovang/luminova/releases)
[![Framework Latest Version](https://img.shields.io/github/tag/luminovang/framework.svg)](https://github.com/luminovang/framework/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/luminovang/luminova/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/luminovang/luminova.svg)](https://luminova.ng/public/docs/2.1.0/download/)
![PHP 8.0+](https://img.shields.io/badge/php-min%208.0.0-red.svg)


![Local Image](https://github.com/luminovang/luminova/docs/logo.svg)


# About Luminova!

Luminova is a PHP framework built for speed and efficiency, designed to enhance your existing coding skills. At Luminova, we prioritize performance by offering feature customization through the `env` file. This ensures the framework includes only what's needed for your project, based on the features you enable. This approach allows you to enable or disable features as well as customizing your preferred template rendering mode and coding style.

Luminova, provide access to the `Template` object within the view files, allowing you to call template methods and properties using `$this` keyword within template files. This can be disabled if you prefer your views to be rendered in isolation, disabling it will allow you to access exported application classes using custom keyword `$self`.

Get started with Luminova by visiting our [official documentation](https://luminova.ng/docs). For more information and sample code, check out our [YouTube channel](https://www.youtube.com/@luminovang).

---

## Composer Installation

Install luminova via Composer.

```bash
composer create-project luminovang/luminova my-project
```

## Start Development Server

To start the `PHP` development server, run the following `NovaKit` command.

```bash
php novakit server
```

## Sitemap Generator

To generate your website sitemap use the below `NovaKit` command.

```bash
php novakit generate:sitemap
```

To learn more about NovaKit commands [read the novakit documentation](https://luminova.ng/docs/0.0.0/commands/novakit).

---

## Routing

Luminova support flexible routing implementation using `Attributes` or `Router` methods.

**Define your route using `PHP8` attributes:***

```php
#[Route('/', methods: ['GET'])]
public function index(): int 
{
    return $this->view('index');
}
```

**Or define your route using code-based routing:**

```php
<?php 
$router->get('/', 'YourController::index');
```
---

### What's There For Me?

Here we can brief you on the basic features you can expect in Luminova. There's a lot more than what is written here. As Linus Torvalds said, "Talk is cheap. Show me the code."

- **Database Builder:** A powerful Object Relational Mapping (ORM) tool that organizes CRUD operations and simplifies database interactions.
- **MVC & HMVC Architecture:** Adheres to the Model-View-Controller and  Hierarchical Model-View-Controller implementation.
- **Flexible HTTP Routing:** Dynamic and fast routing implementation with a clear separation of concerns.
- **Templating:** Optimized native PHP templating with additional inheritance and caching features. You can also use `Twig` or `Smarty` template engines.
- **Error Handling:** Comprehensive error handling ensures that no errors go unnoticed, including non-fatal inline errors.
- **Session Management:** Easily manage user login sessions without additional implementation.
- **CLI Routing:** Dynamic routing for CLI implementations similar to HTTP routing methods.
- **Cloud Storage:** Supports various cloud storage solutions like AWS, Azure, Google Cloud, and more.
- **File Management:** Deliver files to the browser from any location with temporary or permanent URLs to access private files.
- **Sitemap Generator:** Generate website sitemaps using the `NovaKit` command.
- **Schema Object:** Support for generating schema objects for website pages.
- **Command Line Tool:** Full support for implementing CLI tools, with everything you need available.
- **AI Models:** Integrate or extend AI features into your application.
- **Database:** A flexible database system that supports instant failover to a backup database without user interruption.
- **Security:** Various security implementations to secure your application and user information.
- **Request Handling:** Secure handling of incoming and outgoing HTTP requests.
- **Email:** Send emails anywhere, with support for sending entire view content as the email body.
- **Translation:** Create translations for your application using our translation class.
- **Encryption:** Support for different encryption handlers and methods.
- **Services:** Define classes that can be shared and discovered anywhere in your codebase, with support for serialization and class object caching.

---

## Quick Tips

**Q: My session works on the development server but not on the production server.**
- **A:** In production, change the `$sessionDomain` in `/app/Controllers/Config/Session.php` to your actual production domain. You can use `'.' . APP_HOST` for this. Also, update the `Cookie.php` configuration accordingly.

**Q: My CSS and images are broken on the production server.**
- **A:** Ensure you set the `app.environment.mood` key to `production` in your environment file when in production.

---

## Something Missing?

Your feedback is highly appreciated [peter@luminova.ng](mailto:peter@luminova.ng). Let us know what more to add to enhance your experience with Luminova. You can also recommend tutorials for our YouTube channel to help you understand and use Luminova better.

Most important, don't forget to rate Luminova on GitHub. Your rating is like fuel to illuminate our motivation to add more features and make Luminova widely recognized and better.
