<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitce7d6b0379bc9a35d3afd6dd2b0e131e
{
    public static $files = array (
        '95fdf39a8122bf72f14ebe8df7aa8d5b' => __DIR__ . '/../../..' . '/libraries/sys/functions.php',
        '4cd8dfe243dbbe1b839b58622aac1bb3' => __DIR__ . '/../../..' . '/libraries/sys/helpers.php',
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
        'App\\Controllers\\Config\\Config' => __DIR__ . '/../../..' . '/app/Controllers/Config/Config.php',
        'App\\Controllers\\Config\\Cookie' => __DIR__ . '/../../..' . '/app/Controllers/Config/Cookie.php',
        'App\\Controllers\\Config\\IPConfig' => __DIR__ . '/../../..' . '/app/Controllers/Config/IPConfig.php',
        'App\\Controllers\\Config\\Paths' => __DIR__ . '/../../..' . '/app/Controllers/Config/Paths.php',
        'App\\Controllers\\Config\\Session' => __DIR__ . '/../../..' . '/app/Controllers/Config/Session.php',
        'App\\Controllers\\Config\\Template' => __DIR__ . '/../../..' . '/app/Controllers/Config/Template.php',
        'App\\Controllers\\Utils\\Func' => __DIR__ . '/../../..' . '/app/Controllers/Utils/Func.php',
        'App\\Controllers\\Welcome' => __DIR__ . '/../../..' . '/app/Controllers/Welcome.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Luminova\\Application\\Application' => __DIR__ . '/../../..' . '/system/Application/Application.php',
        'Luminova\\Application\\Factory' => __DIR__ . '/../../..' . '/system/Application/Factory.php',
        'Luminova\\Application\\Paths' => __DIR__ . '/../../..' . '/system/Application/Paths.php',
        'Luminova\\Application\\Services' => __DIR__ . '/../../..' . '/system/Application/Services.php',
        'Luminova\\Arrays\\ArrayCountable' => __DIR__ . '/../../..' . '/system/Arrays/ArrayCountable.php',
        'Luminova\\Arrays\\ArrayInput' => __DIR__ . '/../../..' . '/system/Arrays/ArrayInput.php',
        'Luminova\\Arrays\\Arrays' => __DIR__ . '/../../..' . '/system/Arrays/Arrays.php',
        'Luminova\\Base\\BaseApplication' => __DIR__ . '/../../..' . '/system/Base/BaseApplication.php',
        'Luminova\\Base\\BaseCommand' => __DIR__ . '/../../..' . '/system/Base/BaseCommand.php',
        'Luminova\\Base\\BaseConfig' => __DIR__ . '/../../..' . '/system/Base/BaseConfig.php',
        'Luminova\\Base\\BaseController' => __DIR__ . '/../../..' . '/system/Base/BaseController.php',
        'Luminova\\Base\\BaseFunction' => __DIR__ . '/../../..' . '/system/Base/BaseFunction.php',
        'Luminova\\Base\\BaseModel' => __DIR__ . '/../../..' . '/system/Base/BaseModel.php',
        'Luminova\\Base\\BasePaths' => __DIR__ . '/../../..' . '/system/Base/BasePaths.php',
        'Luminova\\Base\\BaseViewController' => __DIR__ . '/../../..' . '/system/Base/BaseViewController.php',
        'Luminova\\Cache\\Cache' => __DIR__ . '/../../..' . '/system/Cache/Cache.php',
        'Luminova\\Cache\\Compress' => __DIR__ . '/../../..' . '/system/Cache/Compress.php',
        'Luminova\\Cache\\FileCache' => __DIR__ . '/../../..' . '/system/Cache/FileCache.php',
        'Luminova\\Cache\\MemoryCache' => __DIR__ . '/../../..' . '/system/Cache/MemoryCache.php',
        'Luminova\\Cache\\Optimizer' => __DIR__ . '/../../..' . '/system/Cache/Optimizer.php',
        'Luminova\\Command\\Colors' => __DIR__ . '/../../..' . '/system/Command/Colors.php',
        'Luminova\\Command\\Commands' => __DIR__ . '/../../..' . '/system/Command/Commands.php',
        'Luminova\\Command\\Console' => __DIR__ . '/../../..' . '/system/Command/Console.php',
        'Luminova\\Command\\Novakit\\AvailableCommands' => __DIR__ . '/../../..' . '/system/Command/Novakit/AvailableCommands.php',
        'Luminova\\Command\\Novakit\\Database' => __DIR__ . '/../../..' . '/system/Command/Novakit/Database.php',
        'Luminova\\Command\\Novakit\\Generators' => __DIR__ . '/../../..' . '/system/Command/Novakit/Generators.php',
        'Luminova\\Command\\Novakit\\Help' => __DIR__ . '/../../..' . '/system/Command/Novakit/Help.php',
        'Luminova\\Command\\Novakit\\Lists' => __DIR__ . '/../../..' . '/system/Command/Novakit/Lists.php',
        'Luminova\\Command\\Novakit\\Server' => __DIR__ . '/../../..' . '/system/Command/Novakit/Server.php',
        'Luminova\\Command\\Terminal' => __DIR__ . '/../../..' . '/system/Command/Terminal.php',
        'Luminova\\Command\\TerminalGenerator' => __DIR__ . '/../../..' . '/system/Command/TerminalGenerator.php',
        'Luminova\\Command\\TextUtils' => __DIR__ . '/../../..' . '/system/Command/TextUtils.php',
        'Luminova\\Composer\\BaseComposer' => __DIR__ . '/../../..' . '/system/Composer/BaseComposer.php',
        'Luminova\\Composer\\Builder' => __DIR__ . '/../../..' . '/system/Composer/Builder.php',
        'Luminova\\Composer\\Updater' => __DIR__ . '/../../..' . '/system/Composer/Updater.php',
        'Luminova\\Config\\Configuration' => __DIR__ . '/../../..' . '/system/Config/Configuration.php',
        'Luminova\\Config\\Database' => __DIR__ . '/../../..' . '/system/Config/Database.php',
        'Luminova\\Config\\DotEnv' => __DIR__ . '/../../..' . '/system/Config/DotEnv.php',
        'Luminova\\Controllers\\Controller' => __DIR__ . '/../../..' . '/system/Controllers/Controller.php',
        'Luminova\\Controllers\\ViewController' => __DIR__ . '/../../..' . '/system/Controllers/ViewController.php',
        'Luminova\\Cookies\\Cookie' => __DIR__ . '/../../..' . '/system/Cookies/Cookie.php',
        'Luminova\\Cookies\\CookieInterface' => __DIR__ . '/../../..' . '/system/Cookies/CookieInterface.php',
        'Luminova\\Cookies\\Exception\\CookieException' => __DIR__ . '/../../..' . '/system/Cookies/Exception/CookieException.php',
        'Luminova\\Database\\Columns' => __DIR__ . '/../../..' . '/system/Database/Columns.php',
        'Luminova\\Database\\Connection' => __DIR__ . '/../../..' . '/system/Database/Connection.php',
        'Luminova\\Database\\Drivers\\DriversInterface' => __DIR__ . '/../../..' . '/system/Database/Drivers/DriversInterface.php',
        'Luminova\\Database\\Drivers\\MySqlDriver' => __DIR__ . '/../../..' . '/system/Database/Drivers/MySqlDriver.php',
        'Luminova\\Database\\Drivers\\PdoDriver' => __DIR__ . '/../../..' . '/system/Database/Drivers/PdoDriver.php',
        'Luminova\\Database\\Query' => __DIR__ . '/../../..' . '/system/Database/Query.php',
        'Luminova\\Database\\Results\\Statements' => __DIR__ . '/../../..' . '/system/Database/Results/Statements.php',
        'Luminova\\Debugger\\PHPStanRules' => __DIR__ . '/../../..' . '/system/Debugger/PHPStanRules.php',
        'Luminova\\Email\\Clients\\MailClientInterface' => __DIR__ . '/../../..' . '/system/Email/Clients/MailClientInterface.php',
        'Luminova\\Email\\Clients\\NovaMailer' => __DIR__ . '/../../..' . '/system/Email/Clients/NovaMailer.php',
        'Luminova\\Email\\Clients\\PHPMailer' => __DIR__ . '/../../..' . '/system/Email/Clients/PHPMailer.php',
        'Luminova\\Email\\Clients\\SwiftMailer' => __DIR__ . '/../../..' . '/system/Email/Clients/SwiftMailer.php',
        'Luminova\\Email\\Exceptions\\MailerException' => __DIR__ . '/../../..' . '/system/Email/Exceptions/MailerException.php',
        'Luminova\\Email\\Helpers\\Helper' => __DIR__ . '/../../..' . '/system/Email/Helpers/Helper.php',
        'Luminova\\Email\\Mailer' => __DIR__ . '/../../..' . '/system/Email/Mailer.php',
        'Luminova\\Errors\\Codes' => __DIR__ . '/../../..' . '/system/Errors/Codes.php',
        'Luminova\\Errors\\Error' => __DIR__ . '/../../..' . '/system/Errors/Error.php',
        'Luminova\\Exceptions\\AppException' => __DIR__ . '/../../..' . '/system/Exceptions/AppException.php',
        'Luminova\\Exceptions\\BadMethodCallException' => __DIR__ . '/../../..' . '/system/Exceptions/BadMethodCallException.php',
        'Luminova\\Exceptions\\ClassException' => __DIR__ . '/../../..' . '/system/Exceptions/ClassException.php',
        'Luminova\\Exceptions\\DatabaseException' => __DIR__ . '/../../..' . '/system/Exceptions/DatabaseException.php',
        'Luminova\\Exceptions\\ErrorException' => __DIR__ . '/../../..' . '/system/Exceptions/ErrorException.php',
        'Luminova\\Exceptions\\FileException' => __DIR__ . '/../../..' . '/system/Exceptions/FileException.php',
        'Luminova\\Exceptions\\InvalidException' => __DIR__ . '/../../..' . '/system/Exceptions/InvalidException.php',
        'Luminova\\Exceptions\\InvalidObjectException' => __DIR__ . '/../../..' . '/system/Exceptions/InvalidObjectException.php',
        'Luminova\\Exceptions\\LuminovaException' => __DIR__ . '/../../..' . '/system/Exceptions/LuminovaException.php',
        'Luminova\\Exceptions\\NotFoundException' => __DIR__ . '/../../..' . '/system/Exceptions/NotFoundException.php',
        'Luminova\\Exceptions\\RuntimeException' => __DIR__ . '/../../..' . '/system/Exceptions/RuntimeException.php',
        'Luminova\\Exceptions\\ValidationException' => __DIR__ . '/../../..' . '/system/Exceptions/ValidationException.php',
        'Luminova\\Exceptions\\ViewNotFoundException' => __DIR__ . '/../../..' . '/system/Exceptions/ViewNotFoundException.php',
        'Luminova\\Functions\\Document' => __DIR__ . '/../../..' . '/system/Functions/Document.php',
        'Luminova\\Functions\\Escaper' => __DIR__ . '/../../..' . '/system/Functions/Escaper.php',
        'Luminova\\Functions\\Files' => __DIR__ . '/../../..' . '/system/Functions/Files.php',
        'Luminova\\Functions\\FunctionTrait' => __DIR__ . '/../../..' . '/system/Functions/FunctionTrait.php',
        'Luminova\\Functions\\Functions' => __DIR__ . '/../../..' . '/system/Functions/Functions.php',
        'Luminova\\Functions\\IPAddress' => __DIR__ . '/../../..' . '/system/Functions/IPAddress.php',
        'Luminova\\Functions\\MathTrait' => __DIR__ . '/../../..' . '/system/Functions/MathTrait.php',
        'Luminova\\Functions\\StringTrait' => __DIR__ . '/../../..' . '/system/Functions/StringTrait.php',
        'Luminova\\Functions\\TorDetector' => __DIR__ . '/../../..' . '/system/Functions/TorDetector.php',
        'Luminova\\Http\\AsyncClientInterface' => __DIR__ . '/../../..' . '/system/Http/AsyncClientInterface.php',
        'Luminova\\Http\\Client\\Curl' => __DIR__ . '/../../..' . '/system/Http/Client/Curl.php',
        'Luminova\\Http\\Client\\Guzzle' => __DIR__ . '/../../..' . '/system/Http/Client/Guzzle.php',
        'Luminova\\Http\\CurlAsyncClient' => __DIR__ . '/../../..' . '/system/Http/CurlAsyncClient.php',
        'Luminova\\Http\\Exceptions\\ClientException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/ClientException.php',
        'Luminova\\Http\\Exceptions\\ConnectException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/ConnectException.php',
        'Luminova\\Http\\Exceptions\\RequestException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/RequestException.php',
        'Luminova\\Http\\Exceptions\\ServerException' => __DIR__ . '/../../..' . '/system/Http/Exceptions/ServerException.php',
        'Luminova\\Http\\GuzzleAsyncClient' => __DIR__ . '/../../..' . '/system/Http/GuzzleAsyncClient.php',
        'Luminova\\Http\\Header' => __DIR__ . '/../../..' . '/system/Http/Header.php',
        'Luminova\\Http\\Network' => __DIR__ . '/../../..' . '/system/Http/Network.php',
        'Luminova\\Http\\NetworkAsync' => __DIR__ . '/../../..' . '/system/Http/NetworkAsync.php',
        'Luminova\\Http\\NetworkClientInterface' => __DIR__ . '/../../..' . '/system/Http/NetworkClientInterface.php',
        'Luminova\\Http\\NetworkRequest' => __DIR__ . '/../../..' . '/system/Http/NetworkRequest.php',
        'Luminova\\Http\\NetworkResponse' => __DIR__ . '/../../..' . '/system/Http/NetworkResponse.php',
        'Luminova\\Http\\Request' => __DIR__ . '/../../..' . '/system/Http/Request.php',
        'Luminova\\Languages\\Translator' => __DIR__ . '/../../..' . '/system/Languages/Translator.php',
        'Luminova\\Library\\Importer' => __DIR__ . '/../../..' . '/system/Library/Importer.php',
        'Luminova\\Logger\\Logger' => __DIR__ . '/../../..' . '/system/Logger/Logger.php',
        'Luminova\\Logger\\LoggerAware' => __DIR__ . '/../../..' . '/system/Logger/LoggerAware.php',
        'Luminova\\Logger\\NovaLogger' => __DIR__ . '/../../..' . '/system/Logger/NovaLogger.php',
        'Luminova\\Models\\Model' => __DIR__ . '/../../..' . '/system/Models/Model.php',
        'Luminova\\Models\\PushMessage' => __DIR__ . '/../../..' . '/system/Models/PushMessage.php',
        'Luminova\\Notifications\\FirebasePusher' => __DIR__ . '/../../..' . '/system/Notifications/FirebasePusher.php',
        'Luminova\\Notifications\\FirebaseRealtime' => __DIR__ . '/../../..' . '/system/Notifications/FirebaseRealtime.php',
        'Luminova\\Routing\\Bootstrap' => __DIR__ . '/../../..' . '/system/Routing/Bootstrap.php',
        'Luminova\\Routing\\Router' => __DIR__ . '/../../..' . '/system/Routing/Router.php',
        'Luminova\\Security\\Csrf' => __DIR__ . '/../../..' . '/system/Security/Csrf.php',
        'Luminova\\Security\\Encryption\\AES' => __DIR__ . '/../../..' . '/system/Security/Encryption/AES.php',
        'Luminova\\Security\\Encryption\\EncryptionInterface' => __DIR__ . '/../../..' . '/system/Security/Encryption/EncryptionInterface.php',
        'Luminova\\Security\\InputValidator' => __DIR__ . '/../../..' . '/system/Security/InputValidator.php',
        'Luminova\\Security\\ValidatorInterface' => __DIR__ . '/../../..' . '/system/Security/ValidatorInterface.php',
        'Luminova\\Seo\\Meta' => __DIR__ . '/../../..' . '/system/Seo/Meta.php',
        'Luminova\\Sessions\\CookieManager' => __DIR__ . '/../../..' . '/system/Sessions/CookieManager.php',
        'Luminova\\Sessions\\Session' => __DIR__ . '/../../..' . '/system/Sessions/Session.php',
        'Luminova\\Sessions\\SessionInterface' => __DIR__ . '/../../..' . '/system/Sessions/SessionInterface.php',
        'Luminova\\Sessions\\SessionManager' => __DIR__ . '/../../..' . '/system/Sessions/SessionManager.php',
        'Luminova\\Storage\\Helper' => __DIR__ . '/../../..' . '/system/Storage/Helper.php',
        'Luminova\\Storage\\S3' => __DIR__ . '/../../..' . '/system/Storage/S3.php',
        'Luminova\\Storage\\Uploader' => __DIR__ . '/../../..' . '/system/Storage/Uploader.php',
        'Luminova\\Template\\Smarty' => __DIR__ . '/../../..' . '/system/Template/Smarty.php',
        'Luminova\\Template\\Template' => __DIR__ . '/../../..' . '/system/Template/Template.php',
        'Luminova\\Template\\TemplateTrait' => __DIR__ . '/../../..' . '/system/Template/TemplateTrait.php',
        'Luminova\\Time\\Task' => __DIR__ . '/../../..' . '/system/Time/Task.php',
        'Luminova\\Time\\Time' => __DIR__ . '/../../..' . '/system/Time/Time.php',
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
