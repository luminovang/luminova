### Application Routing & Controllers

Luminova comes with four default `WEB, API, CONSOLE and CLI` routing interface where you can register your project routes.

#### How Routing Works

Framework will redirect every request uri that doesn't start with your custom routing name to main web controller, example `api` or `console` will not be redirected to main web controller. 

`https://example.com/api/foo, https://example.com/api/*` will be treated as api request and `routes/api.php` will handle all matching request.
`https://example.com/console/foo, https://example.com/console/*` will be treated as admin console request and `routes/console.php` will handle all matching request.

So make sure you don't have any custom web routs that start with any of the path which you have registered on `public/index.php`.

### Registering Custom Routes 

To register a custom route, it can be done in `public/index.php` and also create the controller php file in `routes/`.
The controller file name must match with routing bootstrap name. See below examples.

```php 
new Bootstrap("routing name", "callback function for error handling");
```
`public/index.php`

The routing bootstrap `$app->router->bootstraps()` method accepts arguments of `Bootstrap` instance. You can registered as many routes as you want as long as the names are unique. And also ensure that the uri start segment are unique and matches with your desired route.

The below route can be accessed in browser by visiting `https://example.com/panel/`, every request that starts with `panel` will be handles by `routes/panel.php`

```php
$app->router->bootstraps($app, new Bootstrap("panel", function() use ($app){
    $app->view("panelError")->render();
}));
```
*IMPORTANT*

While creating your custom routes, do not change the default web rout `Bootstrap::WEB`, changing the name may cause unexpected error.

### Default Routes 

#### WEB
The `Bootstrap::WEB` interface will handle all your website implementation. 
Location: `/routes/web.php`

#### CLI
The `Bootstrap::CLI` interface will handle all your command line implementation
Location: `/routes/cli.php`

#### API 
The `Bootstrap::API` interface will handle all your API implementation
Location: `/routes/api.php`

#### CONSOLE 
The `Bootstrap::CONSOLE` interface will handle all your API implementation
Location: `/routes/console.php`

#### WEBHOOK 
The `Bootstrap::WEBHOOK` interface will handle all your WEBHOOK implementation
Location: `/routes/webhook.php`


#### Routing Capture & Controllers 

Within any of your routes files located in `/routes/`, you can register routing controllers with patterns and methods depending on your application needs. 

*Examples*

For APIs, Websites and none CLI routes, they uses the same methods on like CLI routes.

Setting up your application global middleware security here such as session etc...
Or you can also do that on bind for group capture.

Your middleware must return and integer value `0` as passed or `1` as failed.

```php 
$router->before('GET|POST', '/.*', function () use($router): int {
    if($ok){
        return STATUS_OK;
    }
    return STATUS_ERROR;
});

//OR

$router->before('GET|POST', '/.*', 'Security::middleware');
```

Landing page goes here if you need to

```php
$router->get('/', function() use ($app) {
    $app->view("index")->render();
});

//OR

$router->get('/', 'HomeController::index');
```

Route to user profile example `https://example.com/user/peter1` 
```php
$router->get('/user/(.*)', 'UserController::profile');
```

Post update user profile, we recommend not doing this in your main web controller, is better to create a custom rout for your post updates or do it in `API` controller route instead.

```php 
$router->post('/user', 'UserController::update');
```

Bind multiple routes.
The below can be accessed from `https://example.com/foo` or to access the inner group `https://example.com/foo/bar/id7366` 

```php
$router->bind('/foo', function() use ($router, $app) {

    $router->get('/', function() use ($router, $app) {
        $app->view("foo")->render();
    });

    $router->get('/bar/([a-zA-Z0-9]+)', function($id) use ($app) {
        $app->view("bar")->render([
            "id" => $id
        ]);
    });
});
```

### Template View Directory

Loading template view from sub folder in `resources/views` directory. example `resources/views/admin`

This can be done in before middleware or call before revering view.
But be careful while using it, if registered globally and other controllers uses default view directory it might affect them as all controllers will be searching for view in the custom folder. To use it in before middleware always use a matching pattern.

```php
$router->before('GET', '/admin/.*', function () use($app, $router): int {
    $app->setFolder("admin");
    if($ok){
        return STATUS_OK;
    }
    return STATUS_ERROR;
});
```
OR 

```php
$router->bind('/admin', function() use ($router, $app) {
    $router->get('/', function() use ($app) {
        $app->setFolder("admin")->view("foo")->render();
    });
});
```

#### CLI Routing Capture & Controllers 

In NovaKit CLI, you can register a global before middleware security check as like you did on web and apis but using another method name call `authenticate`.

You callback or controller method must return an integer (`0` or `STATUS_OK`) as passed or (`1` or `STATUS_ERROR`) as failed.

```php
$router->authenticate(function(): int {
    if($ok){
        return STATUS_OK;
    }
    return STATUS_ERROR;
});
```

Registering your command controllers, in CLI, you `post, get, put or option` are not supported.
To register a command rout it can be done in `command` method of router class instance


```php
$router->command("foo", 'CommandController::run');
```
Executing: `php index.php foo` 

```php
$router->command("demo", 'CommandController::run');
```
Executing: `php index.php demo` 

##### Creating a Route with Dynamic Segments


In the router, you can define routes with dynamic segments, similar to a website front controller. To capture dynamic values within a segment, use the syntax `(:value)`.

```php
$router->command('/user/name/(:value)', function($name): int {
    echo "Username: {$name}";
    return STATUS_OK;
});
```
Executing: `php index.php user name "Peter"`

```php
$router->command('/user/name/(:value)/id/(:value)', function($name, $id): int {
    echo "UserInfo: {$name}, Id: {$id}";
    return STATUS_OK;
});
```
Executing: `php index.php user name "Peter" id 22`

## Application Controller Classes

Creating an application controller classes

[Controller Classes](CONTROLLERS.md)