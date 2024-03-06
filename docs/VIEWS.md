### Application Routing & Controllers

To create your html template view, you must do that inside `resources/views` or if you have s sub custom directory inside.
When you pass an array parameter to your `$app->render('foo')->view([...])`, you can get the values in your template by simply echoing the key using `$this` keyword and underscore prefix `_`, before the key name. example `echo $this->_name`.

To access a class instance you registered in your application controller you don't need underscore `_`, You can just call `$this->MyClass->doSomething()` depending on the name you have registered your class instance with.

If you are using a Smarty template engin or `App\Config\Template::$optionsAsVariable` is set to true, you can access view options as variable name `$foo` instead of `$this->_foo`

##### Creating A View 
Luminova supports `Smarty` template engine or default the `PHP` template.

user.php 

```html
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Luminova User</title>
        <link rel="shortcut icon" type="image/png" href="<?php echo $this->_assets;?>images/icons/favicon.png">
    </head>
    <body>
        <h1>Welcome <?= $this->_name; ?></h1>
        <a href="<?= $this->_base; ?>">Go Home</a>
    </body>
</html>
```

user.tpl 

```html
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Luminova User</title>
        <link rel="shortcut icon" type="image/png" href="{$assets}images/icons/favicon.png">
    </head>
    <body>
        <h1>Welcome {$name}</h1>
        <a href="{$base}">Go Home</a>
    </body>
</html>
```

#### Configuring Templates View

To configure your template view default options, you can pass array of options to render view with.

```php 
$app->view("user")->render([
    "active" => "String: Set the name of activate view,"
    "ContentType" => "String: Set the rendering content type of view [json, text or html], the default is html",
    "optimize" => "Boolean: Enable or disable view optimization. The default is true. But before it work you have to enable it in your initialization environment config file .env",
    "title" => "String: Set view title"
    "subtitle" => "String: Set view subtitle",
    "base" => "String: Set project base directory",
    "assets" => "String: Set project assets directory"
]);
```

Rendering views in controller class 

```php
$this->app->view('user')->render([]);
```
Same as above 

```php
$this->view('user', []);
```

#### Templates Global Variables

Variable         |   Description                                                                     | Modifiable
-----------------|-----------------------------------------------------------------------------------|-------------
$this->_base     | Get your base relative directory, using it withing any view will return you to home view   | Yes
$this->_active   | get the current view name                                                         | No
$this->_title    | Get the current view page title                                                   | Yes
$this->_subtitle | Get the current view subtitle                                                     | Yes
$this->_assets   | Get assets folder                                                                 | No
ALLOW_ACCESS     | Define if only template class can access and render view                          | No
$this->_ContentType | Get view content type | Yes
$this->_optimize  | Get view optimizer state | Yes

#### Template Configuration Class 

Path: `app/Controllers/Config/Template.php`

```php 
App\Config\Template
```


## Application Request & Validations

Validating user request and input

[Request Validations](VALIDATION.md)