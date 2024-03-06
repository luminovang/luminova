### Application HTTP Request


#### Luminova\Http\Request

To make use of request instance in your controller class you can call `$this->request` or initialize `Request` class based on your application requirement.

```php 
namespace App\Controllers;
use Luminova\Base\BaseController;

class UserController extends BaseController
{
    /** @var \ Luminova\Http\Request $this->request */
    /** @var \ Luminova\Application $this->app */
    /** @var \Luminova\Security\InputValidator $this->validate */

}
```

Get request method 
```php 
$this->request->getMethod() 
```

Get request value from `GET` method  
```php 
$this->request->get("name");
//OR 
$this->request->get("name", "default name");
```

Get request value from 
```php 
$this->request->find("POST", "name");
//OR 
$this->request->find("POST", "name", "default name");
```

Other similar getters 

```php 
$this->request->getPost($field, $default)
$this->request->getPut($field, $default)
$this->request->getDelete($field, $default)
$this->request->getOption($field, $default)
$this->request->getPatch($field, $default)
$this->request->getHead($field, $default)
$this->request->getConnect($field, $default)
$this->request->getTrace($field, $default)
$this->request->getPropfind($field, $default)
$this->request->getMkcol($field, $default)
$this->request->getCopy($field, $default)
$this->request->getMove($field, $default)
$this->request->getLock($field, $default)
$this->request->getUnlock($field, $default)
```

Get request body 
```php 
$this->request->getBody(): array
```

Get request body 
```php 
$this->request->getBodyAsObject(): object
```


Get request upload file info object
```php 
$this->request->getFiles(): ?object
```

Get request upload file by filed name and return an object of file info or null
```php 
$this->request->getFile('file name'): ?object
```

Get authorization header
```php 
$this->request->getAuthorization(): string
```

Get access token from header
```php 
$this->request->getAuthBearer(): ?string
```

Check if the current connection is secure
```php 
$this->request->isSecure(): bool
```

Check if request is ajax request
```php 
$this->request->isAJAX(): bool
```

Test to see if a request was made from the command line.
```php 
$this->request->isCommandLine(): bool
```

Get request url
```php 
$this->request->getUri(): string
```

Get user browser info
```php 
$this->request->getBrowser(): string
```

Get user agent string
```php 
$this->request->getUserAgent(): string
```

Check if request header exist
```php 
$this->request->hasHeader("Foo"): bool
```

Get request header by key name.
```php 
$this->request->header('Foo'): ?Header
```

Get request headers
```php 
$this->request->getHeaders(): array
```


## Application Database Management

Creating and managing application database

[Database Management](DATABASE.md)