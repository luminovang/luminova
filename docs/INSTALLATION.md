### Installation Guide

Luminova can be installed by manual download, or using Composer. 
Installing via Composer is recommended because it keeps Framework up to date easily.

##### Installation via composer 

```bash
composer create-project luminovang/luminova project-root
```

##### Installation via git

```bash
$ git clone https://github.com/luminovang/luminova
$ composer install
$ composer test
```

### Manual Installation 

Download the framework project and extract it as zip in your project directory


### Server Configuration

Let's start by creating the first website using the Luminova framework

The first thing to do is configure your web server to use a custom document root which should be `path/to/your/project/public`.
Assuming your existing document root is `user/var/www/public_html` now you have to change it to `user/var/www/project/public` or `user/var/www/public_html/public`. The `project` or `public_html` will serve as your private document root where the framework files will be located which is not accessible from web browsers.

### Document Root Configuration Samples

Example configuration for your virtual host file may look like below.

```bash 
<VirtualHost *:80>
    DocumentRoot "/user/var/www/public_html/public"
    ErrorLog     "/user/var/www/public_html/error_log"
    CustomLog    "/user/var/www/public_html/access_log" common

    <Directory "/user/var/www/public_html/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
The above configuration assumes the project folder is located as follows:

```bash
var/www/
    ├── public_html/      (Project Folder)
            └── public/  (DocumentRoot for project)
            │      └── index.php (Font Controller)
            │      └── assets/ (Static Assets Folder)
            └── system/
```

*IMPORTANT*
Make sure you set the required permissions for `public` directory to be accessible in browsers.

Now that we are done setting up our website document root let's head to uploading our project online.
Upload your project version which is located on `builds/v-{*.*}` folder, to your project private directory.


## Running Application

Creating and running applications

[Running Application](START.md)
