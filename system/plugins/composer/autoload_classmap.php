<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname(dirname($vendorDir));

return array(
    'App\\Application' => $baseDir . '/app/Application.php',
    'App\\Config\\AI' => $baseDir . '/app/Config/AI.php',
    'App\\Config\\Apis' => $baseDir . '/app/Config/Apis.php',
    'App\\Config\\Browser' => $baseDir . '/app/Config/Browser.php',
    'App\\Config\\ContentSecurityPolicy' => $baseDir . '/app/Config/ContentSecurityPolicy.php',
    'App\\Config\\Cookie' => $baseDir . '/app/Config/Cookie.php',
    'App\\Config\\Cron' => $baseDir . '/app/Config/Cron.php',
    'App\\Config\\Database' => $baseDir . '/app/Config/Database.php',
    'App\\Config\\Encryption' => $baseDir . '/app/Config/Encryption.php',
    'App\\Config\\Files' => $baseDir . '/app/Config/Files.php',
    'App\\Config\\IPConfig' => $baseDir . '/app/Config/IPConfig.php',
    'App\\Config\\Preference' => $baseDir . '/app/Config/Preference.php',
    'App\\Config\\Security' => $baseDir . '/app/Config/Security.php',
    'App\\Config\\Services' => $baseDir . '/app/Config/Services.php',
    'App\\Config\\Session' => $baseDir . '/app/Config/Session.php',
    'App\\Config\\Sitemap' => $baseDir . '/app/Config/Sitemap.php',
    'App\\Config\\Template' => $baseDir . '/app/Config/Template.php',
    'App\\Config\\Templates\\Smarty\\Classes' => $baseDir . '/app/Config/Templates/Smarty/Classes.php',
    'App\\Config\\Templates\\Smarty\\Modifiers' => $baseDir . '/app/Config/Templates/Smarty/Modifiers.php',
    'App\\Config\\Templates\\Twig\\Extensions' => $baseDir . '/app/Config/Templates/Twig/Extensions.php',
    'App\\Config\\Templates\\Twig\\Filters' => $baseDir . '/app/Config/Templates/Twig/Filters.php',
    'App\\Config\\Templates\\Twig\\Functions' => $baseDir . '/app/Config/Templates/Twig/Functions.php',
    'App\\Config\\Templates\\Twig\\Globals' => $baseDir . '/app/Config/Templates/Twig/Globals.php',
    'App\\Config\\Templates\\Twig\\NodeVisitors' => $baseDir . '/app/Config/Templates/Twig/NodeVisitors.php',
    'App\\Config\\Templates\\Twig\\Operators' => $baseDir . '/app/Config/Templates/Twig/Operators.php',
    'App\\Config\\Templates\\Twig\\Rot13Provider' => $baseDir . '/app/Config/Templates/Twig/Rot13Provider.php',
    'App\\Config\\Templates\\Twig\\Tests' => $baseDir . '/app/Config/Templates/Twig/Tests.php',
    'App\\Config\\Templates\\Twig\\TokenParsers' => $baseDir . '/app/Config/Templates/Twig/TokenParsers.php',
    'App\\Controllers\\Cli\\DemoCommand' => $baseDir . '/app/Controllers/Cli/DemoCommand.php',
    'App\\Controllers\\Errors\\ViewErrors' => $baseDir . '/app/Controllers/Errors/ViewErrors.php',
    'App\\Controllers\\Http\\DemoRequest' => $baseDir . '/app/Controllers/Http/DemoRequest.php',
    'App\\Controllers\\Http\\Welcome' => $baseDir . '/app/Controllers/Http/Welcome.php',
    'App\\Utils\\Functions' => $baseDir . '/app/Utils/Functions.php',
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'Luminova\\Ai\\Model' => $baseDir . '/system/Ai/Model.php',
    'Luminova\\Ai\\Models\\OpenAI' => $baseDir . '/system/Ai/Models/OpenAI.php',
    'Luminova\\Application\\Caller' => $baseDir . '/system/Application/Caller.php',
    'Luminova\\Application\\Factory' => $baseDir . '/system/Application/Factory.php',
    'Luminova\\Application\\Foundation' => $baseDir . '/system/Application/Foundation.php',
    'Luminova\\Application\\Services' => $baseDir . '/system/Application/Services.php',
    'Luminova\\Arrays\\ArrayUtil' => $baseDir . '/system/Arrays/ArrayUtil.php',
    'Luminova\\Arrays\\Lists' => $baseDir . '/system/Arrays/Lists.php',
    'Luminova\\Attributes\\Error' => $baseDir . '/system/Attributes/Error.php',
    'Luminova\\Attributes\\Generator' => $baseDir . '/system/Attributes/Generator.php',
    'Luminova\\Attributes\\Route' => $baseDir . '/system/Attributes/Route.php',
    'Luminova\\Base\\BaseCache' => $baseDir . '/system/Base/BaseCache.php',
    'Luminova\\Base\\BaseCallable' => $baseDir . '/system/Base/BaseCallable.php',
    'Luminova\\Base\\BaseCommand' => $baseDir . '/system/Base/BaseCommand.php',
    'Luminova\\Base\\BaseConfig' => $baseDir . '/system/Base/BaseConfig.php',
    'Luminova\\Base\\BaseConsole' => $baseDir . '/system/Base/BaseConsole.php',
    'Luminova\\Base\\BaseController' => $baseDir . '/system/Base/BaseController.php',
    'Luminova\\Base\\BaseMailer' => $baseDir . '/system/Base/BaseMailer.php',
    'Luminova\\Base\\BaseModel' => $baseDir . '/system/Base/BaseModel.php',
    'Luminova\\Base\\BaseViewController' => $baseDir . '/system/Base/BaseViewController.php',
    'Luminova\\Boot' => $baseDir . '/system/Boot.php',
    'Luminova\\Builder\\Document' => $baseDir . '/system/Builder/Document.php',
    'Luminova\\Builder\\Inputs' => $baseDir . '/system/Builder/Inputs.php',
    'Luminova\\Builder\\Xhtml' => $baseDir . '/system/Builder/Xhtml.php',
    'Luminova\\Cache\\FileCache' => $baseDir . '/system/Cache/FileCache.php',
    'Luminova\\Cache\\MemoryCache' => $baseDir . '/system/Cache/MemoryCache.php',
    'Luminova\\Cache\\ViewCache' => $baseDir . '/system/Cache/ViewCache.php',
    'Luminova\\Command\\Auth\\Handler' => $baseDir . '/system/Command/Auth/Handler.php',
    'Luminova\\Command\\Auth\\Session' => $baseDir . '/system/Command/Auth/Session.php',
    'Luminova\\Command\\Console' => $baseDir . '/system/Command/Console.php',
    'Luminova\\Command\\Novakit\\Builder' => $baseDir . '/system/Command/Novakit/Builder.php',
    'Luminova\\Command\\Novakit\\Commands' => $baseDir . '/system/Command/Novakit/Commands.php',
    'Luminova\\Command\\Novakit\\Context' => $baseDir . '/system/Command/Novakit/Context.php',
    'Luminova\\Command\\Novakit\\CronJobs' => $baseDir . '/system/Command/Novakit/CronJobs.php',
    'Luminova\\Command\\Novakit\\Database' => $baseDir . '/system/Command/Novakit/Database.php',
    'Luminova\\Command\\Novakit\\Generators' => $baseDir . '/system/Command/Novakit/Generators.php',
    'Luminova\\Command\\Novakit\\Lists' => $baseDir . '/system/Command/Novakit/Lists.php',
    'Luminova\\Command\\Novakit\\Logs' => $baseDir . '/system/Command/Novakit/Logs.php',
    'Luminova\\Command\\Novakit\\Server' => $baseDir . '/system/Command/Novakit/Server.php',
    'Luminova\\Command\\Novakit\\System' => $baseDir . '/system/Command/Novakit/System.php',
    'Luminova\\Command\\Novakit\\SystemHelp' => $baseDir . '/system/Command/Novakit/SystemHelp.php',
    'Luminova\\Command\\Terminal' => $baseDir . '/system/Command/Terminal.php',
    'Luminova\\Command\\Utils\\Color' => $baseDir . '/system/Command/Utils/Color.php',
    'Luminova\\Command\\Utils\\Text' => $baseDir . '/system/Command/Utils/Text.php',
    'Luminova\\Composer\\BaseComposer' => $baseDir . '/system/Composer/BaseComposer.php',
    'Luminova\\Composer\\Builder' => $baseDir . '/system/Composer/Builder.php',
    'Luminova\\Composer\\Updater' => $baseDir . '/system/Composer/Updater.php',
    'Luminova\\Config\\Environment' => $baseDir . '/system/Config/Environment.php',
    'Luminova\\Cookies\\Cookie' => $baseDir . '/system/Cookies/Cookie.php',
    'Luminova\\Core\\CoreApplication' => $baseDir . '/system/Core/CoreApplication.php',
    'Luminova\\Core\\CoreCronTasks' => $baseDir . '/system/Core/CoreCronTasks.php',
    'Luminova\\Core\\CoreDatabase' => $baseDir . '/system/Core/CoreDatabase.php',
    'Luminova\\Core\\CoreFunction' => $baseDir . '/system/Core/CoreFunction.php',
    'Luminova\\Core\\CoreServices' => $baseDir . '/system/Core/CoreServices.php',
    'Luminova\\Database\\Alter' => $baseDir . '/system/Database/Alter.php',
    'Luminova\\Database\\Builder' => $baseDir . '/system/Database/Builder.php',
    'Luminova\\Database\\Connection' => $baseDir . '/system/Database/Connection.php',
    'Luminova\\Database\\Drivers\\MySqliDriver' => $baseDir . '/system/Database/Drivers/MySqliDriver.php',
    'Luminova\\Database\\Drivers\\PdoDriver' => $baseDir . '/system/Database/Drivers/PdoDriver.php',
    'Luminova\\Database\\Manager' => $baseDir . '/system/Database/Manager.php',
    'Luminova\\Database\\Migration' => $baseDir . '/system/Database/Migration.php',
    'Luminova\\Database\\Schema' => $baseDir . '/system/Database/Schema.php',
    'Luminova\\Database\\Seeder' => $baseDir . '/system/Database/Seeder.php',
    'Luminova\\Database\\Table' => $baseDir . '/system/Database/Table.php',
    'Luminova\\Database\\TableTrait' => $baseDir . '/system/Database/TableTrait.php',
    'Luminova\\Debugger\\PHPStanRules' => $baseDir . '/system/Debugger/PHPStanRules.php',
    'Luminova\\Debugger\\Performance' => $baseDir . '/system/Debugger/Performance.php',
    'Luminova\\Debugger\\Tracer' => $baseDir . '/system/Debugger/Tracer.php',
    'Luminova\\Email\\Clients\\NovaMailer' => $baseDir . '/system/Email/Clients/NovaMailer.php',
    'Luminova\\Email\\Clients\\PHPMailer' => $baseDir . '/system/Email/Clients/PHPMailer.php',
    'Luminova\\Email\\Clients\\SwiftMailer' => $baseDir . '/system/Email/Clients/SwiftMailer.php',
    'Luminova\\Email\\Helpers\\Helper' => $baseDir . '/system/Email/Helpers/Helper.php',
    'Luminova\\Email\\Mailer' => $baseDir . '/system/Email/Mailer.php',
    'Luminova\\Errors\\ErrorHandler' => $baseDir . '/system/Errors/ErrorHandler.php',
    'Luminova\\Exceptions\\AppException' => $baseDir . '/system/Exceptions/AppException.php',
    'Luminova\\Exceptions\\BadMethodCallException' => $baseDir . '/system/Exceptions/BadMethodCallException.php',
    'Luminova\\Exceptions\\CacheException' => $baseDir . '/system/Exceptions/CacheException.php',
    'Luminova\\Exceptions\\ClassException' => $baseDir . '/system/Exceptions/ClassException.php',
    'Luminova\\Exceptions\\CookieException' => $baseDir . '/system/Exceptions/CookieException.php',
    'Luminova\\Exceptions\\DatabaseException' => $baseDir . '/system/Exceptions/DatabaseException.php',
    'Luminova\\Exceptions\\DatabaseLimitException' => $baseDir . '/system/Exceptions/DatabaseLimitException.php',
    'Luminova\\Exceptions\\DateTimeException' => $baseDir . '/system/Exceptions/DateTimeException.php',
    'Luminova\\Exceptions\\EncryptionException' => $baseDir . '/system/Exceptions/EncryptionException.php',
    'Luminova\\Exceptions\\ErrorException' => $baseDir . '/system/Exceptions/ErrorException.php',
    'Luminova\\Exceptions\\FileException' => $baseDir . '/system/Exceptions/FileException.php',
    'Luminova\\Exceptions\\Http\\ClientException' => $baseDir . '/system/Exceptions/Http/ClientException.php',
    'Luminova\\Exceptions\\Http\\ConnectException' => $baseDir . '/system/Exceptions/Http/ConnectException.php',
    'Luminova\\Exceptions\\Http\\RequestException' => $baseDir . '/system/Exceptions/Http/RequestException.php',
    'Luminova\\Exceptions\\Http\\ServerException' => $baseDir . '/system/Exceptions/Http/ServerException.php',
    'Luminova\\Exceptions\\InvalidArgumentException' => $baseDir . '/system/Exceptions/InvalidArgumentException.php',
    'Luminova\\Exceptions\\InvalidException' => $baseDir . '/system/Exceptions/InvalidException.php',
    'Luminova\\Exceptions\\InvalidObjectException' => $baseDir . '/system/Exceptions/InvalidObjectException.php',
    'Luminova\\Exceptions\\JsonException' => $baseDir . '/system/Exceptions/JsonException.php',
    'Luminova\\Exceptions\\MailerException' => $baseDir . '/system/Exceptions/MailerException.php',
    'Luminova\\Exceptions\\NotFoundException' => $baseDir . '/system/Exceptions/NotFoundException.php',
    'Luminova\\Exceptions\\RouterException' => $baseDir . '/system/Exceptions/RouterException.php',
    'Luminova\\Exceptions\\RuntimeException' => $baseDir . '/system/Exceptions/RuntimeException.php',
    'Luminova\\Exceptions\\SecurityException' => $baseDir . '/system/Exceptions/SecurityException.php',
    'Luminova\\Exceptions\\StorageException' => $baseDir . '/system/Exceptions/StorageException.php',
    'Luminova\\Exceptions\\ValidationException' => $baseDir . '/system/Exceptions/ValidationException.php',
    'Luminova\\Exceptions\\ViewNotFoundException' => $baseDir . '/system/Exceptions/ViewNotFoundException.php',
    'Luminova\\Functions\\Escape' => $baseDir . '/system/Functions/Escape.php',
    'Luminova\\Functions\\Func' => $baseDir . '/system/Functions/Func.php',
    'Luminova\\Functions\\IP' => $baseDir . '/system/Functions/IP.php',
    'Luminova\\Functions\\Maths' => $baseDir . '/system/Functions/Maths.php',
    'Luminova\\Functions\\Normalizer' => $baseDir . '/system/Functions/Normalizer.php',
    'Luminova\\Functions\\Tor' => $baseDir . '/system/Functions/Tor.php',
    'Luminova\\Http\\Client\\Curl' => $baseDir . '/system/Http/Client/Curl.php',
    'Luminova\\Http\\Client\\Guzzle' => $baseDir . '/system/Http/Client/Guzzle.php',
    'Luminova\\Http\\Encoder' => $baseDir . '/system/Http/Encoder.php',
    'Luminova\\Http\\File' => $baseDir . '/system/Http/File.php',
    'Luminova\\Http\\Header' => $baseDir . '/system/Http/Header.php',
    'Luminova\\Http\\HttpCode' => $baseDir . '/system/Http/HttpCode.php',
    'Luminova\\Http\\Message\\Response' => $baseDir . '/system/Http/Message/Response.php',
    'Luminova\\Http\\Network' => $baseDir . '/system/Http/Network.php',
    'Luminova\\Http\\Request' => $baseDir . '/system/Http/Request.php',
    'Luminova\\Http\\Server' => $baseDir . '/system/Http/Server.php',
    'Luminova\\Http\\UserAgent' => $baseDir . '/system/Http/UserAgent.php',
    'Luminova\\Interface\\AiInterface' => $baseDir . '/system/Interface/AiInterface.php',
    'Luminova\\Interface\\CallableInterface' => $baseDir . '/system/Interface/CallableInterface.php',
    'Luminova\\Interface\\ConnInterface' => $baseDir . '/system/Interface/ConnInterface.php',
    'Luminova\\Interface\\CookieInterface' => $baseDir . '/system/Interface/CookieInterface.php',
    'Luminova\\Interface\\DatabaseInterface' => $baseDir . '/system/Interface/DatabaseInterface.php',
    'Luminova\\Interface\\EncryptionInterface' => $baseDir . '/system/Interface/EncryptionInterface.php',
    'Luminova\\Interface\\ErrorHandlerInterface' => $baseDir . '/system/Interface/ErrorHandlerInterface.php',
    'Luminova\\Interface\\ExceptionInterface' => $baseDir . '/system/Interface/ExceptionInterface.php',
    'Luminova\\Interface\\MailerInterface' => $baseDir . '/system/Interface/MailerInterface.php',
    'Luminova\\Interface\\NetworkClientInterface' => $baseDir . '/system/Interface/NetworkClientInterface.php',
    'Luminova\\Interface\\NetworkInterface' => $baseDir . '/system/Interface/NetworkInterface.php',
    'Luminova\\Interface\\RouterInterface' => $baseDir . '/system/Interface/RouterInterface.php',
    'Luminova\\Interface\\ServicesInterface' => $baseDir . '/system/Interface/ServicesInterface.php',
    'Luminova\\Interface\\SessionManagerInterface' => $baseDir . '/system/Interface/SessionManagerInterface.php',
    'Luminova\\Interface\\ValidationInterface' => $baseDir . '/system/Interface/ValidationInterface.php',
    'Luminova\\Languages\\Translator' => $baseDir . '/system/Languages/Translator.php',
    'Luminova\\Library\\Modules' => $baseDir . '/system/Library/Modules.php',
    'Luminova\\Logger\\LogLevel' => $baseDir . '/system/Logger/LogLevel.php',
    'Luminova\\Logger\\Logger' => $baseDir . '/system/Logger/Logger.php',
    'Luminova\\Logger\\LoggerAware' => $baseDir . '/system/Logger/LoggerAware.php',
    'Luminova\\Logger\\NovaLogger' => $baseDir . '/system/Logger/NovaLogger.php',
    'Luminova\\Notifications\\Firebase\\Database' => $baseDir . '/system/Notifications/Firebase/Database.php',
    'Luminova\\Notifications\\Firebase\\Notification' => $baseDir . '/system/Notifications/Firebase/Notification.php',
    'Luminova\\Notifications\\Models\\Message' => $baseDir . '/system/Notifications/Models/Message.php',
    'Luminova\\Optimization\\Minification' => $baseDir . '/system/Optimization/Minification.php',
    'Luminova\\Routing\\Prefix' => $baseDir . '/system/Routing/Prefix.php',
    'Luminova\\Routing\\Router' => $baseDir . '/system/Routing/Router.php',
    'Luminova\\Routing\\Segments' => $baseDir . '/system/Routing/Segments.php',
    'Luminova\\Security\\Crypter' => $baseDir . '/system/Security/Crypter.php',
    'Luminova\\Security\\Csrf' => $baseDir . '/system/Security/Csrf.php',
    'Luminova\\Security\\Encryption\\OpenSSL' => $baseDir . '/system/Security/Encryption/OpenSSL.php',
    'Luminova\\Security\\Encryption\\Sodium' => $baseDir . '/system/Security/Encryption/Sodium.php',
    'Luminova\\Security\\Validation' => $baseDir . '/system/Security/Validation.php',
    'Luminova\\Seo\\Schema' => $baseDir . '/system/Seo/Schema.php',
    'Luminova\\Seo\\Sitemap' => $baseDir . '/system/Seo/Sitemap.php',
    'Luminova\\Sessions\\CookieManager' => $baseDir . '/system/Sessions/CookieManager.php',
    'Luminova\\Sessions\\Session' => $baseDir . '/system/Sessions/Session.php',
    'Luminova\\Sessions\\SessionManager' => $baseDir . '/system/Sessions/SessionManager.php',
    'Luminova\\Storages\\Adapters\\Adapters' => $baseDir . '/system/Storages/Adapters/Adapters.php',
    'Luminova\\Storages\\FileDelivery' => $baseDir . '/system/Storages/FileDelivery.php',
    'Luminova\\Storages\\FileManager' => $baseDir . '/system/Storages/FileManager.php',
    'Luminova\\Storages\\Storage' => $baseDir . '/system/Storages/Storage.php',
    'Luminova\\Storages\\Stream' => $baseDir . '/system/Storages/Stream.php',
    'Luminova\\Storages\\Uploader' => $baseDir . '/system/Storages/Uploader.php',
    'Luminova\\Template\\Helper' => $baseDir . '/system/Template/Helper.php',
    'Luminova\\Template\\Layout' => $baseDir . '/system/Template/Layout.php',
    'Luminova\\Template\\Response' => $baseDir . '/system/Template/Response.php',
    'Luminova\\Template\\Smarty' => $baseDir . '/system/Template/Smarty.php',
    'Luminova\\Template\\Twig' => $baseDir . '/system/Template/Twig.php',
    'Luminova\\Template\\View' => $baseDir . '/system/Template/View.php',
    'Luminova\\Time\\CronInterval' => $baseDir . '/system/Time/CronInterval.php',
    'Luminova\\Time\\Task' => $baseDir . '/system/Time/Task.php',
    'Luminova\\Time\\Time' => $baseDir . '/system/Time/Time.php',
    'Luminova\\Time\\Timestamp' => $baseDir . '/system/Time/Timestamp.php',
    'Luminova\\Utils\\Event' => $baseDir . '/system/Utils/Event.php',
    'Luminova\\Utils\\Process' => $baseDir . '/system/Utils/Process.php',
    'Luminova\\Utils\\Queue' => $baseDir . '/system/Utils/Queue.php',
    'Psr\\Log\\AbstractLogger' => $vendorDir . '/psr/log/Psr/Log/AbstractLogger.php',
    'Psr\\Log\\InvalidArgumentException' => $vendorDir . '/psr/log/Psr/Log/InvalidArgumentException.php',
    'Psr\\Log\\LogLevel' => $vendorDir . '/psr/log/Psr/Log/LogLevel.php',
    'Psr\\Log\\LoggerAwareInterface' => $vendorDir . '/psr/log/Psr/Log/LoggerAwareInterface.php',
    'Psr\\Log\\LoggerAwareTrait' => $vendorDir . '/psr/log/Psr/Log/LoggerAwareTrait.php',
    'Psr\\Log\\LoggerInterface' => $vendorDir . '/psr/log/Psr/Log/LoggerInterface.php',
    'Psr\\Log\\LoggerTrait' => $vendorDir . '/psr/log/Psr/Log/LoggerTrait.php',
    'Psr\\Log\\NullLogger' => $vendorDir . '/psr/log/Psr/Log/NullLogger.php',
    'Psr\\Log\\Test\\DummyTest' => $vendorDir . '/psr/log/Psr/Log/Test/DummyTest.php',
    'Psr\\Log\\Test\\LoggerInterfaceTest' => $vendorDir . '/psr/log/Psr/Log/Test/LoggerInterfaceTest.php',
    'Psr\\Log\\Test\\TestLogger' => $vendorDir . '/psr/log/Psr/Log/Test/TestLogger.php',
);
