### Running Application

Luminova has a local development server, leveraging PHP's built-in web server. You can launch the server in development mode which is totally optional or you can keep using on `XAMPP, WAMPP` etc for your application development.

#### Without Local Development Server

To run your application without Luminova built-in development web server, you only need to install `XAMPP` or `WAMPP`, the start the installed application services. Locate the `htdocs` in XAMPP directory or `www` in WAMPP directory and create your project directory example `myproject.com`.

Now access your project `myproject.com` in a web browser like `http://myproject.com/public/`, this will lunch your project.

#### With Local Development Server

You can launch Luminova development server, with the following command line in the main directory:

This will launch the server and you can now view your application in your browser at http://localhost:8080.

```bash
php novakit server
```

Passing your hostname and port to launch the server
This will launch the server and you can now view your application in your browser at http://127.0.0.1:8081.
```bash
php novakit server --host=127.0.0.1 --port=8081
```

Specifying a specific version of PHP to use, can be done with `--php` option, the value should specify the path to the PHP executable version you want to use:

```bash
php novakit server --php /path/to/php.version
```

## Application Routing & Controllers

Accessing global helper functions

[Global Helper Functions](GLOBAL.md)