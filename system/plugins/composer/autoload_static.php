<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitce7d6b0379bc9a35d3afd6dd2b0e131e
{
    public static $files = array (
        'a2c9dda26e51c832b5344ac5c9ba205b' => __DIR__ . '/../../..' . '/libraries/sys/constants.php',
        '95fdf39a8122bf72f14ebe8df7aa8d5b' => __DIR__ . '/../../..' . '/libraries/sys/functions.php',
        '8048f6d5e0718bf65fe2ff18f6c25d93' => __DIR__ . '/../../..' . '/libraries/sys/features.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Log\\' => 8,
            'Psr\\Cache\\' => 10,
        ),
        'L' => 
        array (
            'Luminova\\' => 9,
        ),
        'A' => 
        array (
            'App\\Controllers\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Cache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/cache/src',
        ),
        'Luminova\\' => 
        array (
            0 => __DIR__ . '/../../..' . '/system',
            1 => __DIR__ . '/..' . '/luminovang/framework/src',
        ),
        'App\\Controllers\\' => 
        array (
            0 => __DIR__ . '/../../..' . '/app/Controllers',
        ),
    );

    public static $classMap = array (
        'App\\Controllers\\Application' => __DIR__ . '/../../..' . '/app/Controllers/Application.php',
        'App\\Controllers\\Config\\Browser' => __DIR__ . '/../../..' . '/app/Controllers/Config/Browser.php',
        'App\\Controllers\\Config\\Cookie' => __DIR__ . '/../../..' . '/app/Controllers/Config/Cookie.php',
        'App\\Controllers\\Config\\Encryption' => __DIR__ . '/../../..' . '/app/Controllers/Config/Encryption.php',
        'App\\Controllers\\Config\\Files' => __DIR__ . '/../../..' . '/app/Controllers/Config/Files.php',
        'App\\Controllers\\Config\\IPConfig' => __DIR__ . '/../../..' . '/app/Controllers/Config/IPConfig.php',
        'App\\Controllers\\Config\\Preference' => __DIR__ . '/../../..' . '/app/Controllers/Config/Preference.php',
        'App\\Controllers\\Config\\Security' => __DIR__ . '/../../..' . '/app/Controllers/Config/Security.php',
        'App\\Controllers\\Config\\Servers' => __DIR__ . '/../../..' . '/app/Controllers/Config/Servers.php',
        'App\\Controllers\\Config\\Services' => __DIR__ . '/../../..' . '/app/Controllers/Config/Services.php',
        'App\\Controllers\\Config\\Session' => __DIR__ . '/../../..' . '/app/Controllers/Config/Session.php',
        'App\\Controllers\\Config\\Sitemap' => __DIR__ . '/../../..' . '/app/Controllers/Config/Sitemap.php',
        'App\\Controllers\\Config\\Template' => __DIR__ . '/../../..' . '/app/Controllers/Config/Template.php',
        'App\\Controllers\\Config\\Templates\\Smarty\\Classes' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Smarty/Classes.php',
        'App\\Controllers\\Config\\Templates\\Smarty\\Modifiers' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Smarty/Modifiers.php',
        'App\\Controllers\\Config\\Templates\\Twig\\Extensions' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/Extensions.php',
        'App\\Controllers\\Config\\Templates\\Twig\\Filters' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/Filters.php',
        'App\\Controllers\\Config\\Templates\\Twig\\Functions' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/Functions.php',
        'App\\Controllers\\Config\\Templates\\Twig\\Globals' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/Globals.php',
        'App\\Controllers\\Config\\Templates\\Twig\\NodeVisitors' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/NodeVisitors.php',
        'App\\Controllers\\Config\\Templates\\Twig\\Operators' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/Operators.php',
        'App\\Controllers\\Config\\Templates\\Twig\\Rot13Provider' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/Rot13Provider.php',
        'App\\Controllers\\Config\\Templates\\Twig\\Tests' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/Tests.php',
        'App\\Controllers\\Config\\Templates\\Twig\\TokenParsers' => __DIR__ . '/../../..' . '/app/Controllers/Config/Templates/Twig/TokenParsers.php',
        'App\\Controllers\\Config\\ViewErrors' => __DIR__ . '/../../..' . '/app/Controllers/Config/ViewErrors.php',
        'App\\Controllers\\Utils\\Config' => __DIR__ . '/../../..' . '/app/Controllers/Utils/Config.php',
        'App\\Controllers\\Utils\\Functions' => __DIR__ . '/../../..' . '/app/Controllers/Utils/Functions.php',
        'App\\Controllers\\Welcome' => __DIR__ . '/../../..' . '/app/Controllers/Welcome.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Luminova\\Application\\Factory' => __DIR__ . '/../../..' . '/system/Application/Factory.php',
        'Luminova\\Application\\FileSystem' => __DIR__ . '/../../..' . '/system/Application/FileSystem.php',
        'Luminova\\Application\\Foundation' => __DIR__ . '/../../..' . '/system/Application/Foundation.php',
        'Luminova\\Application\\Functions' => __DIR__ . '/../../..' . '/system/Application/Functions.php',
        'Luminova\\Application\\Services' => __DIR__ . '/../../..' . '/system/Application/Services.php',
        'Luminova\\Arrays\\ArrayCountable' => __DIR__ . '/../../..' . '/system/Arrays/ArrayCountable.php',
        'Luminova\\Arrays\\ArrayInput' => __DIR__ . '/../../..' . '/system/Arrays/ArrayInput.php',
        'Luminova\\Arrays\\Arrays' => __DIR__ . '/../../..' . '/system/Arrays/Arrays.php',
        'Luminova\\Base\\BaseApplication' => __DIR__ . '/../../..' . '/system/Base/BaseApplication.php',
        'Luminova\\Base\\BaseCommand' => __DIR__ . '/../../..' . '/system/Base/BaseCommand.php',
        'Luminova\\Base\\BaseConfig' => __DIR__ . '/../../..' . '/system/Base/BaseConfig.php',
        'Luminova\\Base\\BaseConsole' => __DIR__ . '/../../..' . '/system/Base/BaseConsole.php',
        'Luminova\\Base\\BaseController' => __DIR__ . '/../../..' . '/system/Base/BaseController.php',
        'Luminova\\Base\\BaseFiles' => __DIR__ . '/../../..' . '/system/Base/BaseFiles.php',
        'Luminova\\Base\\BaseFunction' => __DIR__ . '/../../..' . '/system/Base/BaseFunction.php',
        'Luminova\\Base\\BaseModel' => __DIR__ . '/../../..' . '/system/Base/BaseModel.php',
        'Luminova\\Base\\BaseServers' => __DIR__ . '/../../..' . '/system/Base/BaseServers.php',
        'Luminova\\Base\\BaseServices' => __DIR__ . '/../../..' . '/system/Base/BaseServices.php',
        'Luminova\\Base\\BaseViewController' => __DIR__ . '/../../..' . '/system/Base/BaseViewController.php',
        'Luminova\\Builder\\Csp' => __DIR__ . '/../../..' . '/system/Builder/Csp.php',
        'Luminova\\Builder\\Document' => __DIR__ . '/../../..' . '/system/Builder/Document.php',
        'Luminova\\Builder\\Html' => __DIR__ . '/../../..' . '/system/Builder/Html.php',
        'Luminova\\Builder\\Inputs' => __DIR__ . '/../../..' . '/system/Builder/Inputs.php',
        'Luminova\\Cache\\Cache' => __DIR__ . '/../../..' . '/system/Cache/Cache.php',
        'Luminova\\Cache\\FileCache' => __DIR__ . '/../../..' . '/system/Cache/FileCache.php',
        'Luminova\\Cache\\MemoryCache' => __DIR__ . '/../../..' . '/system/Cache/MemoryCache.php',
        'Luminova\\Cache\\PageMinifier' => __DIR__ . '/../../..' . '/system/Cache/PageMinifier.php',
        'Luminova\\Cache\\PageViewCache' => __DIR__ . '/../../..' . '/system/Cache/PageViewCache.php',
        'Luminova\\Command\\Colors' => __DIR__ . '/../../..' . '/system/Command/Colors.php',
        'Luminova\\Command\\Console' => __DIR__ . '/../../..' . '/system/Command/Console.php',
        'Luminova\\Command\\Executor' => __DIR__ . '/../../..' . '/system/Command/Executor.php',
        'Luminova\\Command\\Novakit\\Builder' => __DIR__ . '/../../..' . '/system/Command/Novakit/Builder.php',
        'Luminova\\Command\\Novakit\\Commands' => __DIR__ . '/../../..' . '/system/Command/Novakit/Commands.php',
        'Luminova\\Command\\Novakit\\Context' => __DIR__ . '/../../..' . '/system/Command/Novakit/Context.php',
        'Luminova\\Command\\Novakit\\Database' => __DIR__ . '/../../..' . '/system/Command/Novakit/Database.php',
        'Luminova\\Command\\Novakit\\Generators' => __DIR__ . '/../../..' . '/system/Command/Novakit/Generators.php',
        'Luminova\\Command\\Novakit\\Help' => __DIR__ . '/../../..' . '/system/Command/Novakit/Help.php',
        'Luminova\\Command\\Novakit\\Lists' => __DIR__ . '/../../..' . '/system/Command/Novakit/Lists.php',
        'Luminova\\Command\\Novakit\\Server' => __DIR__ . '/../../..' . '/system/Command/Novakit/Server.php',
        'Luminova\\Command\\Novakit\\System' => __DIR__ . '/../../..' . '/system/Command/Novakit/System.php',
        'Luminova\\Command\\Terminal' => __DIR__ . '/../../..' . '/system/Command/Terminal.php',
        'Luminova\\Command\\TextUtils' => __DIR__ . '/../../..' . '/system/Command/TextUtils.php',
        'Luminova\\Composer\\BaseComposer' => __DIR__ . '/../../..' . '/system/Composer/BaseComposer.php',
        'Luminova\\Composer\\Builder' => __DIR__ . '/../../..' . '/system/Composer/Builder.php',
        'Luminova\\Composer\\Updater' => __DIR__ . '/../../..' . '/system/Composer/Updater.php',
        'Luminova\\Config\\Database' => __DIR__ . '/../../..' . '/system/Config/Database.php',
        'Luminova\\Config\\DotEnv' => __DIR__ . '/../../..' . '/system/Config/DotEnv.php',
        'Luminova\\Config\\PHPStanRules' => __DIR__ . '/../../..' . '/system/Config/PHPStanRules.php',
        'Luminova\\Config\\SystemPaths' => __DIR__ . '/../../..' . '/system/Config/SystemPaths.php',
        'Luminova\\Controllers\\Controller' => __DIR__ . '/../../..' . '/system/Controllers/Controller.php',
        'Luminova\\Cookies\\Cookie' => __DIR__ . '/../../..' . '/system/Cookies/Cookie.php',
        'Luminova\\Cookies\\Exception\\CookieException' => __DIR__ . '/../../..' . '/system/Cookies/Exception/CookieException.php',
        'Luminova\\Database\\Connection' => __DIR__ . '/../../..' . '/system/Database/Connection.php',
        'Luminova\\Database\\Drivers\\MySqliDriver' => __DIR__ . '/../../..' . '/system/Database/Drivers/MySqliDriver.php',
        'Luminova\\Database\\Drivers\\PdoDriver' => __DIR__ . '/../../..' . '/system/Database/Drivers/PdoDriver.php',
        'Luminova\\Database\\Manager' => __DIR__ . '/../../..' . '/system/Database/Manager.php',
        'Luminova\\Database\\QueryBuilder' => __DIR__ . '/../../..' . '/system/Database/QueryBuilder.php',
        'Luminova\\Database\\Scheme' => __DIR__ . '/../../..' . '/system/Database/Scheme.php',
        'Luminova\\Debugger\\PHPStanRules' => __DIR__ . '/../../..' . '/system/Debugger/PHPStanRules.php',
        'Luminova\\Debugger\\Tracer' => __DIR__ . '/../../..' . '/system/Debugger/Tracer.php',
        'Luminova\\Email\\Clients\\NovaMailer' => __DIR__ . '/../../..' . '/system/Email/Clients/NovaMailer.php',
        'Luminova\\Email\\Clients\\PHPMailer' => __DIR__ . '/../../..' . '/system/Email/Clients/PHPMailer.php',
        'Luminova\\Email\\Clients\\SwiftMailer' => __DIR__ . '/../../..' . '/system/Email/Clients/SwiftMailer.php',
        'Luminova\\Email\\Exceptions\\MailerException' => __DIR__ . '/../../..' . '/system/Email/Exceptions/MailerException.php',
        'Luminova\\Email\\Helpers\\Helper' => __DIR__ . '/../../..' . '/system/Email/Helpers/Helper.php',
        'Luminova\\Email\\Mailer' => __DIR__ . '/../../..' . '/system/Email/Mailer.php',
        'Luminova\\Errors\\Error' => __DIR__ . '/../../..' . '/system/Errors/Error.php',
        'Luminova\\Errors\\ErrorStack' => __DIR__ . '/../../..' . '/system/Errors/ErrorStack.php',
        'Luminova\\Exceptions\\AppException' => __DIR__ . '/../../..' . '/system/Exceptions/AppException.php',
        'Luminova\\Exceptions\\BadMethodCallException' => __DIR__ . '/../../..' . '/system/Exceptions/BadMethodCallException.php',
        'Luminova\\Exceptions\\ClassException' => __DIR__ . '/../../..' . '/system/Exceptions/ClassException.php',
        'Luminova\\Exceptions\\DatabaseException' => __DIR__ . '/../../..' . '/system/Exceptions/DatabaseException.php',
        'Luminova\\Exceptions\\DatabaseLimitException' => __DIR__ . '/../../..' . '/system/Exceptions/DatabaseLimitException.php',
        'Luminova\\Exceptions\\DateTimeException' => __DIR__ . '/../../..' . '/system/Exceptions/DateTimeException.php',
        'Luminova\\Exceptions\\EncryptionException' => __DIR__ . '/../../..' . '/system/Exceptions/EncryptionException.php',
        'Luminova\\Exceptions\\ErrorException' => __DIR__ . '/../../..' . '/system/Exceptions/ErrorException.php',
        'Luminova\\Exceptions\\FileException' => __DIR__ . '/../../..' . '/system/Exceptions/FileException.php',
        'Luminova\\Exceptions\\InvalidArgumentException' => __DIR__ . '/../../..' . '/system/Exceptions/InvalidArgumentException.php',
        'Luminova\\Exceptions\\InvalidException' => __DIR__ . '/../../..' . '/system/Exceptions/InvalidException.php',
        'Luminova\\Exceptions\\InvalidObjectException' => __DIR__ . '/../../..' . '/system/Exceptions/InvalidObjectException.php',
        'Luminova\\Exceptions\\LuminovaException' => __DIR__ . '/../../..' . '/system/Exceptions/LuminovaException.php',
        'Luminova\\Exceptions\\NotFoundException' => __DIR__ . '/../../..' . '/system/Exceptions/NotFoundException.php',
        'Luminova\\Exceptions\\RuntimeException' => __DIR__ . '/../../..' . '/system/Exceptions/RuntimeException.php',
        'Luminova\\Exceptions\\SecurityException' => __DIR__ . '/../../..' . '/system/Exceptions/SecurityException.php',
        'Luminova\\Exceptions\\StorageException' => __DIR__ . '/../../..' . '/system/Exceptions/StorageException.php',
        'Luminova\\Exceptions\\ValidationException' => __DIR__ . '/../../..' . '/system/Exceptions/ValidationException.php',
        'Luminova\\Exceptions\\ViewNotFoundException' => __DIR__ . '/../../..' . '/system/Exceptions/ViewNotFoundException.php',
        'Luminova\\Functions\\Escaper' => __DIR__ . '/../../..' . '/system/Functions/Escaper.php',
        'Luminova\\Functions\\IPAddress' => __DIR__ . '/../../..' . '/system/Functions/IPAddress.php',
        'Luminova\\Functions\\Maths' => __DIR__ . '/../../..' . '/system/Functions/Maths.php',
        'Luminova\\Functions\\NormalizerTrait' => __DIR__ . '/../../..' . '/system/Functions/NormalizerTrait.php',
        'Luminova\\Functions\\TorDetector' => __DIR__ . '/../../..' . '/system/Functions/TorDetector.php',
        'Luminova\\Http\\Client\\Curl' => __DIR__ . '/../../..' . '/system/Http/Client/Curl.php',
        'Luminova\\Http\\Client\\Guzzle' => __DIR__ . '/../../..' . '/system/Http/Client/Guzzle.php',
        'Luminova\\Http\\Encoder' => __DIR__ . '/../../..' . '/system/Http/Encoder.php',
        'Luminova\\Http\\Exceptions\\ClientException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/ClientException.php',
        'Luminova\\Http\\Exceptions\\ConnectException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/ConnectException.php',
        'Luminova\\Http\\Exceptions\\RequestException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/RequestException.php',
        'Luminova\\Http\\Exceptions\\ServerException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/ServerException.php',
        'Luminova\\Http\\File' => __DIR__ . '/../../..' . '/system/Http/File.php',
        'Luminova\\Http\\Header' => __DIR__ . '/../../..' . '/system/Http/Header.php',
        'Luminova\\Http\\Message\\Response' => __DIR__ . '/../../..' . '/system/Http/Message/Response.php',
        'Luminova\\Http\\Network' => __DIR__ . '/../../..' . '/system/Http/Network.php',
        'Luminova\\Http\\NetworkRequest' => __DIR__ . '/../../..' . '/system/Http/NetworkRequest.php',
        'Luminova\\Http\\NetworkResponse' => __DIR__ . '/../../..' . '/system/Http/NetworkResponse.php',
        'Luminova\\Http\\Request' => __DIR__ . '/../../..' . '/system/Http/Request.php',
        'Luminova\\Http\\Server' => __DIR__ . '/../../..' . '/system/Http/Server.php',
        'Luminova\\Http\\StatusCodes' => __DIR__ . '/../../..' . '/system/Http/StatusCodes.php',
        'Luminova\\Http\\UserAgent' => __DIR__ . '/../../..' . '/system/Http/UserAgent.php',
        'Luminova\\Interface\\AsyncClientInterface' => __DIR__ . '/../../..' . '/system/Interface/AsyncClientInterface.php',
        'Luminova\\Interface\\CookieInterface' => __DIR__ . '/../../..' . '/system/Interface/CookieInterface.php',
        'Luminova\\Interface\\DatabaseInterface' => __DIR__ . '/../../..' . '/system/Interface/DatabaseInterface.php',
        'Luminova\\Interface\\EncryptionInterface' => __DIR__ . '/../../..' . '/system/Interface/EncryptionInterface.php',
        'Luminova\\Interface\\HttpClientInterface' => __DIR__ . '/../../..' . '/system/Interface/HttpClientInterface.php',
        'Luminova\\Interface\\MailClientInterface' => __DIR__ . '/../../..' . '/system/Interface/MailClientInterface.php',
        'Luminova\\Interface\\NetworkClientInterface' => __DIR__ . '/../../..' . '/system/Interface/NetworkClientInterface.php',
        'Luminova\\Interface\\NetworkInterface' => __DIR__ . '/../../..' . '/system/Interface/NetworkInterface.php',
        'Luminova\\Interface\\ServicesInterface' => __DIR__ . '/../../..' . '/system/Interface/ServicesInterface.php',
        'Luminova\\Interface\\SessionInterface' => __DIR__ . '/../../..' . '/system/Interface/SessionInterface.php',
        'Luminova\\Interface\\ValidationInterface' => __DIR__ . '/../../..' . '/system/Interface/ValidationInterface.php',
        'Luminova\\Languages\\Translator' => __DIR__ . '/../../..' . '/system/Languages/Translator.php',
        'Luminova\\Library\\Modules' => __DIR__ . '/../../..' . '/system/Library/Modules.php',
        'Luminova\\Logger\\Logger' => __DIR__ . '/../../..' . '/system/Logger/Logger.php',
        'Luminova\\Logger\\LoggerAware' => __DIR__ . '/../../..' . '/system/Logger/LoggerAware.php',
        'Luminova\\Logger\\NovaLogger' => __DIR__ . '/../../..' . '/system/Logger/NovaLogger.php',
        'Luminova\\Models\\PushMessage' => __DIR__ . '/../../..' . '/system/Models/PushMessage.php',
        'Luminova\\Notifications\\FirebasePusher' => __DIR__ . '/../../..' . '/system/Notifications/FirebasePusher.php',
        'Luminova\\Notifications\\FirebaseRealtime' => __DIR__ . '/../../..' . '/system/Notifications/FirebaseRealtime.php',
        'Luminova\\Routing\\Bootstrap' => __DIR__ . '/../../..' . '/system/Routing/Bootstrap.php',
        'Luminova\\Routing\\Router' => __DIR__ . '/../../..' . '/system/Routing/Router.php',
        'Luminova\\Routing\\RouterException' => __DIR__ . '/../../..' . '/system/Routing/RouterException.php',
        'Luminova\\Routing\\Segments' => __DIR__ . '/../../..' . '/system/Routing/Segments.php',
        'Luminova\\Security\\Crypter' => __DIR__ . '/../../..' . '/system/Security/Crypter.php',
        'Luminova\\Security\\Csrf' => __DIR__ . '/../../..' . '/system/Security/Csrf.php',
        'Luminova\\Security\\Encryption\\OpenSSL' => __DIR__ . '/../../..' . '/system/Security/Encryption/OpenSSL.php',
        'Luminova\\Security\\Encryption\\Sodium' => __DIR__ . '/../../..' . '/system/Security/Encryption/Sodium.php',
        'Luminova\\Security\\InputValidator' => __DIR__ . '/../../..' . '/system/Security/InputValidator.php',
        'Luminova\\Seo\\Schema' => __DIR__ . '/../../..' . '/system/Seo/Schema.php',
        'Luminova\\Seo\\Sitemap' => __DIR__ . '/../../..' . '/system/Seo/Sitemap.php',
        'Luminova\\Sessions\\CookieManager' => __DIR__ . '/../../..' . '/system/Sessions/CookieManager.php',
        'Luminova\\Sessions\\Session' => __DIR__ . '/../../..' . '/system/Sessions/Session.php',
        'Luminova\\Sessions\\SessionManager' => __DIR__ . '/../../..' . '/system/Sessions/SessionManager.php',
        'Luminova\\Storages\\Storage' => __DIR__ . '/../../..' . '/system/Storages/Storage.php',
        'Luminova\\Storages\\StorageAdapters' => __DIR__ . '/../../..' . '/system/Storages/StorageAdapters.php',
        'Luminova\\Storages\\Uploader' => __DIR__ . '/../../..' . '/system/Storages/Uploader.php',
        'Luminova\\Template\\Helper' => __DIR__ . '/../../..' . '/system/Template/Helper.php',
        'Luminova\\Template\\Layout' => __DIR__ . '/../../..' . '/system/Template/Layout.php',
        'Luminova\\Template\\Smarty' => __DIR__ . '/../../..' . '/system/Template/Smarty.php',
        'Luminova\\Template\\TemplateTrait' => __DIR__ . '/../../..' . '/system/Template/TemplateTrait.php',
        'Luminova\\Template\\Twig' => __DIR__ . '/../../..' . '/system/Template/Twig.php',
        'Luminova\\Template\\ViewResponse' => __DIR__ . '/../../..' . '/system/Template/ViewResponse.php',
        'Luminova\\Time\\Task' => __DIR__ . '/../../..' . '/system/Time/Task.php',
        'Luminova\\Time\\Time' => __DIR__ . '/../../..' . '/system/Time/Time.php',
        'Luminova\\Time\\Timestamp' => __DIR__ . '/../../..' . '/system/Time/Timestamp.php',
        'Luminova\\Utils\\Queue' => __DIR__ . '/../../..' . '/system/Utils/Queue.php',
        'Psr\\Cache\\CacheException' => __DIR__ . '/..' . '/psr/cache/src/CacheException.php',
        'Psr\\Cache\\CacheItemInterface' => __DIR__ . '/..' . '/psr/cache/src/CacheItemInterface.php',
        'Psr\\Cache\\CacheItemPoolInterface' => __DIR__ . '/..' . '/psr/cache/src/CacheItemPoolInterface.php',
        'Psr\\Cache\\InvalidArgumentException' => __DIR__ . '/..' . '/psr/cache/src/InvalidArgumentException.php',
        'Psr\\Log\\AbstractLogger' => __DIR__ . '/..' . '/psr/log/Psr/Log/AbstractLogger.php',
        'Psr\\Log\\InvalidArgumentException' => __DIR__ . '/..' . '/psr/log/Psr/Log/InvalidArgumentException.php',
        'Psr\\Log\\LogLevel' => __DIR__ . '/..' . '/psr/log/Psr/Log/LogLevel.php',
        'Psr\\Log\\LoggerAwareInterface' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerAwareInterface.php',
        'Psr\\Log\\LoggerAwareTrait' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerAwareTrait.php',
        'Psr\\Log\\LoggerInterface' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerInterface.php',
        'Psr\\Log\\LoggerTrait' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerTrait.php',
        'Psr\\Log\\NullLogger' => __DIR__ . '/..' . '/psr/log/Psr/Log/NullLogger.php',
        'Psr\\Log\\Test\\DummyTest' => __DIR__ . '/..' . '/psr/log/Psr/Log/Test/DummyTest.php',
        'Psr\\Log\\Test\\LoggerInterfaceTest' => __DIR__ . '/..' . '/psr/log/Psr/Log/Test/LoggerInterfaceTest.php',
        'Psr\\Log\\Test\\TestLogger' => __DIR__ . '/..' . '/psr/log/Psr/Log/Test/TestLogger.php',
        'Psr\\SimpleCache\\CacheException' => __DIR__ . '/..' . '/psr/simple-cache/src/CacheException.php',
        'Psr\\SimpleCache\\CacheInterface' => __DIR__ . '/..' . '/psr/simple-cache/src/CacheInterface.php',
        'Psr\\SimpleCache\\InvalidArgumentException' => __DIR__ . '/..' . '/psr/simple-cache/src/InvalidArgumentException.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitce7d6b0379bc9a35d3afd6dd2b0e131e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitce7d6b0379bc9a35d3afd6dd2b0e131e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitce7d6b0379bc9a35d3afd6dd2b0e131e::$classMap;

        }, null, ClassLoader::class);
    }
}
