## Template and Controller View Handling

This documentation covers the basic implementation of template handling within the Luminova framework controllers.

### Controller Overview 

Controllers are classes that handle requests made to your application, whether they originate from HTTP requests or CLI commands. Upon initialization, a controller method processes the request, receiving all necessary parameters and dependencies. After processing the information, the controller renders the response or handles it accordingly. All controller classes should be placed in the `/app/Controllers/` directory.

***

### HTTP Controllers

Luminova provides two base controller classes for handling HTTP requests, both of which manage requests in a similar manner but differ in their initialization processes.

***

#### Base Controller

Extending [Luminova\Base\BaseController](https://luminova.ng/docs/0.0.0/base/controller) automatically initializes the HTTP request class `\Luminova\Http\Request` and the input validation class `Luminova\Security\Validation`.

```php
// /app/Controllers/MyController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseController;

class MyController extends BaseController 
{
    //...
}
```

***

#### Base View Controller

Extending [Luminova\Base\BaseViewController](https://luminova.ng/docs/0.0.0/base/view-controller) does not automatically initialize any additional classes, allowing for manual initialization when necessary. This is particularly useful for web pages that do not require immediate user input validation.

```php
// /app/Controllers/MyController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseViewController;

class MyController extends BaseViewController 
{
    //...
}
```

***

### Base Command

Extending [Luminova\Base\BaseCommand](https://luminova.ng/docs/0.0.0/base/command) allows the controller to handle command line operations in a manner similar to HTTP controllers. For more details on command line implementation, [see examples](https://luminova.ng/docs/0.0.0/commands/examples).

```php
// /app/Controllers/MyCommand.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseCommand;

class MyCommand extends BaseCommand 
{
    //...
}
```

***

## Rendering Controller Responses

In Luminova, you have multiple ways to handle and respond to requests. You can use the **`Luminova\Template\Response`** class, which provides a global `response` function, or utilize the **Template `View`** handling class, readily available in the core application object.
The primary difference lies in how they receive and process content. Additionally caching is not implemented for response class but for template view object cache is implemented. 

***

### Response Class Example

The `Response` class allows you to handle any type of response rendering without additional processing, making it particularly useful for APIs that need to return JSON responses, downloadable content, and more.

```php
// /app/Controllers/BookController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseController;
use Luminova\Attributes\Route;
use Luminova\Attributes\Error;
use App\Controllers\Errors\ViewErrors;

#[Error('api', onError: [ViewErrors::class, 'onApiError'])]
class BookController extends BaseController 
{
    #[Route('/api/books', methods: ['POST'])]
    public function apiListBooks(): int 
    {
        $books = [
            ['id' => 100, 'name' => 'Basic Programming'],
            ['id' => 1001, 'name' => 'Advanced Programming']
        ];

        return response(200)->json($books);
    }
}
```

In above example, when a POST request is sent to `https://example.com/api/books`, it will output a JSON response containing the list of books.

***

### Application View Example

The **`View`** object is designed to load and render templates stored in the `/resources/views` directory. Supported template file extensions include:
- **`.tpl`**: For the Smarty template engine
- **`.twg`**: For the Twig template engine
- **`.php`**: For standard PHP templates

When you pass the template file name without the extension, the `View` object automatically loads the template, processes the content, and allows you to pass optional metadata during rendering.

#### Template File Example

```php
// /resources/views/books.php
<!DOCTYPE html>
<html lang="en"> 
<head>
    <title><?php $this->_title;?></title>
</head>
<body>
    <h1>My Book Website</h1>
    <ul>
        <?php foreach ($this->_books as $book): ?>
            <li>[<?= $book['id']; ?>] <?= $book['name']; ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
```

***

#### Books Template Controller Handler Example

```php
// /app/Controllers/BookController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseViewController;
use Luminova\Attributes\Route;
use Luminova\Attributes\Error;
use App\Controllers\Errors\ViewErrors;

#[Error(onError: [ViewErrors::class, 'onWebError'])]
class BookController extends BaseViewController 
{
    #[Route('/books', methods: ['GET'])]
    public function webShowBooks(): int 
    {
        // Assume loading books from a database.
        $books = [
            ['id' => 100, 'name' => 'Basic Programming'],
            ['id' => 1001, 'name' => 'Advanced Programming']
        ];

        return $this->view('books', [
            'books' => $books
        ]);
    }
}
```

In this example, when a GET request is sent to `https://example.com/books`, it displays an HTML page containing the list of books.

***

### Retrieving and Using View Content

There may be cases where you want to generate a template's output without displaying it directly to the user, such as when sending an email with the rendered content.

Here's how you can achieve this:

```php
// /app/Controllers/BookController.php
<?php
namespace App\Controllers;

use Luminova\Base\BaseController;
use Luminova\Email\Mailer;
use Luminova\Attributes\Route;
use Luminova\Attributes\Error;
use App\Controllers\Errors\ViewErrors;

#[Error(onError: [ViewErrors::class, 'onWebError'])]
class BookController extends BaseController
{
    #[Route('/books/send', methods: ['GET'])]
    public function sendShowBooks(string $email): int
    {
        $books = [
            ['id' => 100, 'name' => 'Basic Programming'],
            ['id' => 1001, 'name' => 'Advanced Programming']
        ];

        // Generate the view content without rendering it
        $content = $this->respond('books', [
            'books' => $books
        ]);

        // Send the generated content as an email
        Mailer::to($email)->send($content);

        // Redirect the user after sending the email
        $this->app->redirect('thank-you');
        return STATUS_SUCCESS;
    }
}
```

### How It Works

- **Generating Content:** The `respond` method is used to process the template and return the rendered content as a string. This allows you to use the content in other contexts, such as sending it via email.
- **Sending the Email:** The content is passed to the `Mailer::to()->send()` method, which sends the email to the specified recipient.
- **Redirecting the User:** After the email is sent, the user is redirected to a "thank you" page or any other desired location.

This approach is useful when you need to use the output of a template for purposes other than direct display, such as generating emails, saving content to a file, or performing other custom actions.

***

### Custom View Headers

Luminova automatically handles the basic headers for your template output, but there may be times when you want to include additional headers to be sent along with your template.

Here's how you can do it:

```php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseViewController;
use Luminova\Attributes\Route;
use Luminova\Attributes\Error;
use App\Controllers\Errors\ViewErrors;

#[Error(onError: [ViewErrors::class, 'onWebError'])]
class MyController extends BaseViewController 
{
    #[Route('/books', methods: ['GET'])]
    public function webShowBooks(): int 
    {
        // Set multiple headers at once using an array
        $this->app->headers([
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Custom-Header' => 'CustomValue'
        ]);
        
        // Or set a single header using key-value pairs
        $this->app->header('Content-Type', 'text/html; charset=utf-8');
        
        return $this->view('books');
    }
}
```

#### How It Works

- **Multiple Headers:** You can pass an associative array to the `headers` method, where the keys represent the header names and the values represent their corresponding values. This allows you to set multiple headers at once.
- **Single Header:** If you only need to set a single header, you can use the `header` method, providing the header name and value as arguments.

This flexibility ensures that you can customize the headers sent with your views to match your specific needs, whether it's content type, caching controls, or custom headers.

***

### Application View Caching Examples

The template view in Luminova allows you to cache content, serving a cached version of the page upon revisits. 
Caching can be implemented either automatically or manually to learn more about view caching [read documentation](https://luminova.ng/docs/0.0.0/cache/view-caching).

To enable page view caching, update your environment variables as follows:

1. Set `page.caching` to `true` to enable caching.
2. Define the cache expiration duration by setting `page.cache.expiry`.
3. For content that should never expire, set `page.caching.immutable` to `true`. This will set the cache duration to 5 years and add the `immutable` directive to your cache control headers.

#### Automatic Caching

Automatic caching requires no additional implementation after enabling the caching feature and setting the desired cache expiration and controls.

***

#### Manual Caching with Auto Renewal

This method allows you to render cached content if it is still valid. The callback function will only be executed if the cache does not exist or has expired.

```php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseViewController;
use Luminova\Attributes\Route;
use Luminova\Attributes\Error;
use App\Controllers\Errors\ViewErrors;

#[Error(onError: [ViewErrors::class, 'onWebError'])]
class MyController extends BaseViewController 
{
    #[Route('/books', methods: ['GET'])]
    public function webShowBooks(): int 
    {
        // The first argument is the type of view content.
        $this->app->onExpired('html', function() {
            $books = [
                ['id' => 100, 'name' => 'Basic Programming'],
                ['id' => 1001, 'name' => 'Advanced Programming']
            ];

            return $this->view('books', [
                'books' => $books
            ]);
        });
    }
}
```

***

#### Manual Caching and Renewal

This approach uses a traditional if-else check to first verify if the cache has expired. If the cache is still valid, the existing content is rendered when you call the `reuse` method. If the cache has expired, new content is generated to refresh the content.

```php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseViewController;
use Luminova\Attributes\Route;
use Luminova\Attributes\Error;
use App\Controllers\Errors\ViewErrors;
use Luminova\Core\CoreApplication;

#[Error(onError: [ViewErrors::class, 'onWebError'])]
class MyController extends BaseViewController 
{
    #[Route('/books', methods: ['GET'])]
    public function webShowBooks(): int 
    {
        // Set an expiration time (in seconds) or leave blank/null to use the default expiration.
        $this->app->cache(3600);

        if ($this->app->expired()) {
            $books = [
                ['id' => 100, 'name' => 'Basic Programming'],
                ['id' => 1001, 'name' => 'Advanced Programming']
            ];

            return $this->view('books', [
                'books' => $books
            ]);
        }

        return $this->app->reuse();
    }
}
```

In these examples, when a GET request is sent to `https://example.com/books`, the application checks for cached content and serves it accordingly, ensuring efficient use of resources and faster response times.

***

### Serving Static Content

Luminova supports serving static content, meaning that when content is cached, your controller class or method will not be invoked upon page visits. 
Instead, the cached version is served, enhancing performance. 

To enable static cache serving, specify the list of supported static content types in your environment configuration file (`.env`).

#### Example:

In the following example, we specify the supported view content types as `HTML` and `JSON`. 
This configuration instructs the framework to process any URLs ending with these extensions. If a corresponding view exists, it will be served.

```bash
page.caching.statics = html|json
```

In this case, if a user visits `https://example.com/books.html`, the cached content will be served if available; otherwise, it will be cached. 
Conversely, since there is no view matching `.json`, visiting `https://example.com/books.json` will trigger a 404 error.

***

### View Caching Exclusion and Inclusion

While caching is enabled globally, you can exclude certain views from being cached or specify a list of views that can be cached. This can be accomplished in your application class's `onCreate` or `__construct` method, or within your controller class's `onCreate` or `__construct` method.

#### Exclusion Example

To exclude views from caching, define them as follows:

```php
<?php 
namespace App;

use Luminova\Core\CoreApplication;

class Application extends CoreApplication
{
	protected function onCreate(): void 
	{
		$this->noCaching([
			'edit_book',
			'book_payment',
			'book_cart'
		]);
	}
}
```

***

#### Inclusion Example

If you have many views but only want to cache one, you can use the `cacheOnly` method to specify just that view, avoiding the need to list all others:

```php
<?php 
namespace App;

use Luminova\Core\CoreApplication;

class Application extends CoreApplication
{
	protected function onCreate(): void 
	{
		// Accepts a string or an array of views.
		$this->cacheOnly('books');
	}
}
```

***

### View Directory Structure

Organizing content in a framework is crucial for easy access and management. Luminova provides a simple and effective way to maintain a clean and organized structure for your view templates based on URL prefixes.

#### Scenario

Imagine you have a website with the following URL patterns:

- `https://example.com/api/*` (API endpoints)
- `https://example.com/admin/*` (Admin interface)
- `https://example.com/*` (Main website)

Placing all templates directly under the `resources/views/` directory could become unmanageable as your project grows. To maintain orderliness, you can organize your views into separate directories based on URL prefixes.

#### Example

You can create dedicated controller classes for each URL prefix, such as `ApiController` for API requests, `AdminController` for the admin interface, and `WebController` for the main website.

Within each controller's `onCreate` or `__construct` method, you can specify the base directory where the corresponding template files are located under the `/resources/views/` root directory.

Here's how you can set it up:

```php
// /app/Controllers/WebController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseController;

class WebController extends BaseController
{
	protected function onCreate(): void 
	{
		// No need to change the directory; it uses the default root directory.
	}
}
```

```php
// /app/Controllers/ApiController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseController;

class ApiController extends BaseController
{
	protected function onCreate(): void 
	{
	 	// Organize API views in /resources/views/apis/
		$this->setFolder('apis');
	}
}
```

```php
// /app/Controllers/AdminController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseController;

class AdminController extends BaseController
{
	protected function onCreate(): void 
	{
		// Organize admin views in /resources/views/admins/
		$this->setFolder('admins'); 
	}
}
```

***

### Output Minification

In Luminova, you can easily enable output minification when rendering HTML templates using the template view object. This feature automatically minifies your web page content by removing inline comments and newlines, resulting in a more compact and faster-loading page.

When you visit some websites and use `Ctrl + U` to view the HTML source, you may notice that it's not easily readable due to such minification. Luminova allows you to achieve this in your project by enabling the `page.minification` environment variable in your `.env` file. Simply set it to `true` to minify your template output.

***

#### Minification Options

Minifying all content is generally beneficial, but it can be problematic for websites that display sample code within HTML `<pre><code>` blocks. Minification can make the code within these blocks unreadable by removing newlines, which also prevents JavaScript syntax highlighters from functioning correctly.

Luminova offers a solution to this issue by allowing you to exclude code blocks from minification while still ensuring the rest of your page content is properly minified. Here's how you can do it:

**Example:**

In your application controller's `onCreate` or `__construct` method, you can call the `codeblock` method and pass `false` as the first parameter to exclude code blocks from minification. You can also pass `true` as the second parameter to include a copy button alongside the code block, similar to what's found on the Luminova documentation website.

```php
// /app/Controllers/AdminController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseController;

class AdminController extends BaseController
{
	protected function onCreate(): void 
	{
		$this->codeblock(
			false,  // Exclude code blocks from minification
			true    // Include a copy button with the code block
		); 
	}
}
```

By using this approach, you can maintain both the readability of your code samples and the overall performance benefits of minified content.

***

### Object Exportation in Luminova

Luminova provides two rendering modes for templates, which can be configured in your application's template configuration class located at `/app/Config/Template.php`. These modes allow you to choose your preferred coding style and determine how templates interact with application objects.

When the template rendering mode is set to *isolation*, the view processing is entirely isolated, meaning you cannot access application class objects using the `$this` keyword. Instead, a custom keyword `$self` is provided to access properties defined in the application's class scope.

However, to still access protected or public properties defined in the application class, you need to export them using the `export` method. This method allows you to make specific objects or class instances available within the isolated template context.

#### Example

```php
// /app/Controllers/AdminController.php
<?php 
namespace App\Controllers;

use Luminova\Base\BaseController;
use App\Models\Users;
use App\Utils\Foo;
use App\Utils\Bar;
use App\Utils\Baz;

class AdminController extends BaseController
{
	protected Users $users;

	public function __construct() 
	{
		// Initialize and export an instance of the Users class
		$this->users = new Users();
		$this->export($this->users, 'users');

		// Export a class that has static methods without initialization
		$this->export(Foo::class, null, false);

		// Export a class and initialize it upon export
		$this->export(Bar::class, null, true);

		// Export a class, initialize it, and assign it a different name (alias) for use in templates
		$this->export(Baz::class, 'ClassBaz', true);
	}
}
```

***

#### How It Works

- **Exporting Instances:** When you initialize an object, such as the `Users` class, you can export it to make it accessible within the isolated template context. In this case, the object is assigned the name `users` for use within the template.
- **Static Class Export:** For classes like `Foo`, which only contain static methods and don't require initialization, you can export them without creating an instance. This allows the static methods to be accessed directly in the template.
- **Exporting with Initialization:** For classes that need to be initialized (e.g., `Bar` and `Baz`), you can specify this during export. Additionally, you can assign a different name (alias) to the exported object for use within the template.

***

#### Use Case

This approach is particularly useful in complex applications where templates need access to specific objects or classes while maintaining a strict separation between the view and controller logic. By exporting the necessary objects or classes, you ensure that your templates have the required context and data to render correctly, even in isolated mode.