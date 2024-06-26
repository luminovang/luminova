<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname(dirname($vendorDir));

return array(
    'App\\Controllers\\Application' => $baseDir . '/app/Controllers/Application.php',
    'App\\Controllers\\Config\\AI' => $baseDir . '/app/Controllers/Config/AI.php',
    'App\\Controllers\\Config\\Apis' => $baseDir . '/app/Controllers/Config/Apis.php',
    'App\\Controllers\\Config\\Browser' => $baseDir . '/app/Controllers/Config/Browser.php',
    'App\\Controllers\\Config\\Cookie' => $baseDir . '/app/Controllers/Config/Cookie.php',
    'App\\Controllers\\Config\\Cron' => $baseDir . '/app/Controllers/Config/Cron.php',
    'App\\Controllers\\Config\\Database' => $baseDir . '/app/Controllers/Config/Database.php',
    'App\\Controllers\\Config\\Encryption' => $baseDir . '/app/Controllers/Config/Encryption.php',
    'App\\Controllers\\Config\\Files' => $baseDir . '/app/Controllers/Config/Files.php',
    'App\\Controllers\\Config\\IPConfig' => $baseDir . '/app/Controllers/Config/IPConfig.php',
    'App\\Controllers\\Config\\Preference' => $baseDir . '/app/Controllers/Config/Preference.php',
    'App\\Controllers\\Config\\Security' => $baseDir . '/app/Controllers/Config/Security.php',
    'App\\Controllers\\Config\\Services' => $baseDir . '/app/Controllers/Config/Services.php',
    'App\\Controllers\\Config\\Session' => $baseDir . '/app/Controllers/Config/Session.php',
    'App\\Controllers\\Config\\Sitemap' => $baseDir . '/app/Controllers/Config/Sitemap.php',
    'App\\Controllers\\Config\\Template' => $baseDir . '/app/Controllers/Config/Template.php',
    'App\\Controllers\\Config\\Templates\\Smarty\\Classes' => $baseDir . '/app/Controllers/Config/Templates/Smarty/Classes.php',
    'App\\Controllers\\Config\\Templates\\Smarty\\Modifiers' => $baseDir . '/app/Controllers/Config/Templates/Smarty/Modifiers.php',
    'App\\Controllers\\Config\\Templates\\Twig\\Extensions' => $baseDir . '/app/Controllers/Config/Templates/Twig/Extensions.php',
    'App\\Controllers\\Config\\Templates\\Twig\\Filters' => $baseDir . '/app/Controllers/Config/Templates/Twig/Filters.php',
    'App\\Controllers\\Config\\Templates\\Twig\\Functions' => $baseDir . '/app/Controllers/Config/Templates/Twig/Functions.php',
    'App\\Controllers\\Config\\Templates\\Twig\\Globals' => $baseDir . '/app/Controllers/Config/Templates/Twig/Globals.php',
    'App\\Controllers\\Config\\Templates\\Twig\\NodeVisitors' => $baseDir . '/app/Controllers/Config/Templates/Twig/NodeVisitors.php',
    'App\\Controllers\\Config\\Templates\\Twig\\Operators' => $baseDir . '/app/Controllers/Config/Templates/Twig/Operators.php',
    'App\\Controllers\\Config\\Templates\\Twig\\Rot13Provider' => $baseDir . '/app/Controllers/Config/Templates/Twig/Rot13Provider.php',
    'App\\Controllers\\Config\\Templates\\Twig\\Tests' => $baseDir . '/app/Controllers/Config/Templates/Twig/Tests.php',
    'App\\Controllers\\Config\\Templates\\Twig\\TokenParsers' => $baseDir . '/app/Controllers/Config/Templates/Twig/TokenParsers.php',
    'App\\Controllers\\Errors\\ViewErrors' => $baseDir . '/app/Controllers/Errors/ViewErrors.php',
    'App\\Controllers\\Utils\\Functions' => $baseDir . '/app/Controllers/Utils/Functions.php',
    'App\\Controllers\\Welcome' => $baseDir . '/app/Controllers/Welcome.php',
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'Luminova\\Ai\\Model' => $baseDir . '/system/Ai/Model.php',
    'Luminova\\Ai\\Models\\OpenAI' => $baseDir . '/system/Ai/Models/OpenAI.php',
    'Luminova\\Application\\Caller' => $baseDir . '/system/Application/Caller.php',
    'Luminova\\Application\\Factory' => $baseDir . '/system/Application/Factory.php',
    'Luminova\\Application\\Foundation' => $baseDir . '/system/Application/Foundation.php',
    'Luminova\\Application\\Functions' => $baseDir . '/system/Application/Functions.php',
    'Luminova\\Application\\Services' => $baseDir . '/system/Application/Services.php',
    'Luminova\\Arrays\\ArrayCountable' => $baseDir . '/system/Arrays/ArrayCountable.php',
    'Luminova\\Arrays\\ArrayInput' => $baseDir . '/system/Arrays/ArrayInput.php',
    'Luminova\\Arrays\\ArrayObject' => $baseDir . '/system/Arrays/ArrayObject.php',
    'Luminova\\Arrays\\Arrays' => $baseDir . '/system/Arrays/Arrays.php',
    'Luminova\\Base\\BaseApplication' => $baseDir . '/system/Base/BaseApplication.php',
    'Luminova\\Base\\BaseCommand' => $baseDir . '/system/Base/BaseCommand.php',
    'Luminova\\Base\\BaseConfig' => $baseDir . '/system/Base/BaseConfig.php',
    'Luminova\\Base\\BaseConsole' => $baseDir . '/system/Base/BaseConsole.php',
    'Luminova\\Base\\BaseController' => $baseDir . '/system/Base/BaseController.php',
    'Luminova\\Base\\BaseCron' => $baseDir . '/system/Base/BaseCron.php',
    'Luminova\\Base\\BaseDatabase' => $baseDir . '/system/Base/BaseDatabase.php',
    'Luminova\\Base\\BaseFunction' => $baseDir . '/system/Base/BaseFunction.php',
    'Luminova\\Base\\BaseMailer' => $baseDir . '/system/Base/BaseMailer.php',
    'Luminova\\Base\\BaseModel' => $baseDir . '/system/Base/BaseModel.php',
    'Luminova\\Base\\BaseServices' => $baseDir . '/system/Base/BaseServices.php',
    'Luminova\\Base\\BaseViewController' => $baseDir . '/system/Base/BaseViewController.php',
    'Luminova\\Boot' => $baseDir . '/system/Boot.php',
    'Luminova\\Builder\\Csp' => $baseDir . '/system/Builder/Csp.php',
    'Luminova\\Builder\\Document' => $baseDir . '/system/Builder/Document.php',
    'Luminova\\Builder\\Html' => $baseDir . '/system/Builder/Html.php',
    'Luminova\\Builder\\Inputs' => $baseDir . '/system/Builder/Inputs.php',
    'Luminova\\Cache\\Cache' => $baseDir . '/system/Cache/Cache.php',
    'Luminova\\Cache\\FileCache' => $baseDir . '/system/Cache/FileCache.php',
    'Luminova\\Cache\\MemoryCache' => $baseDir . '/system/Cache/MemoryCache.php',
    'Luminova\\Cache\\PageMinifier' => $baseDir . '/system/Cache/PageMinifier.php',
    'Luminova\\Cache\\PageViewCache' => $baseDir . '/system/Cache/PageViewCache.php',
    'Luminova\\Command\\Colors' => $baseDir . '/system/Command/Colors.php',
    'Luminova\\Command\\Console' => $baseDir . '/system/Command/Console.php',
    'Luminova\\Command\\Novakit\\Builder' => $baseDir . '/system/Command/Novakit/Builder.php',
    'Luminova\\Command\\Novakit\\Commands' => $baseDir . '/system/Command/Novakit/Commands.php',
    'Luminova\\Command\\Novakit\\Context' => $baseDir . '/system/Command/Novakit/Context.php',
    'Luminova\\Command\\Novakit\\CronJobs' => $baseDir . '/system/Command/Novakit/CronJobs.php',
    'Luminova\\Command\\Novakit\\Database' => $baseDir . '/system/Command/Novakit/Database.php',
    'Luminova\\Command\\Novakit\\Generators' => $baseDir . '/system/Command/Novakit/Generators.php',
    'Luminova\\Command\\Novakit\\Help' => $baseDir . '/system/Command/Novakit/Help.php',
    'Luminova\\Command\\Novakit\\Lists' => $baseDir . '/system/Command/Novakit/Lists.php',
    'Luminova\\Command\\Novakit\\Server' => $baseDir . '/system/Command/Novakit/Server.php',
    'Luminova\\Command\\Novakit\\System' => $baseDir . '/system/Command/Novakit/System.php',
    'Luminova\\Command\\Terminal' => $baseDir . '/system/Command/Terminal.php',
    'Luminova\\Command\\TextUtils' => $baseDir . '/system/Command/TextUtils.php',
    'Luminova\\Composer\\BaseComposer' => $baseDir . '/system/Composer/BaseComposer.php',
    'Luminova\\Composer\\Builder' => $baseDir . '/system/Composer/Builder.php',
    'Luminova\\Composer\\Updater' => $baseDir . '/system/Composer/Updater.php',
    'Luminova\\Config\\DotEnv' => $baseDir . '/system/Config/DotEnv.php',
    'Luminova\\Config\\PHPStanRules' => $baseDir . '/system/Config/PHPStanRules.php',
    'Luminova\\Cookies\\Cookie' => $baseDir . '/system/Cookies/Cookie.php',
    'Luminova\\Database\\Builder' => $baseDir . '/system/Database/Builder.php',
    'Luminova\\Database\\Conn\\mysqliConn' => $baseDir . '/system/Database/Conn/mysqliConn.php',
    'Luminova\\Database\\Conn\\pdoConn' => $baseDir . '/system/Database/Conn/pdoConn.php',
    'Luminova\\Database\\Connection' => $baseDir . '/system/Database/Connection.php',
    'Luminova\\Database\\Drivers\\MySqliDriver' => $baseDir . '/system/Database/Drivers/MySqliDriver.php',
    'Luminova\\Database\\Drivers\\PdoDriver' => $baseDir . '/system/Database/Drivers/PdoDriver.php',
    'Luminova\\Database\\Manager' => $baseDir . '/system/Database/Manager.php',
    'Luminova\\Database\\Scheme' => $baseDir . '/system/Database/Scheme.php',
    'Luminova\\Debugger\\PHPStanRules' => $baseDir . '/system/Debugger/PHPStanRules.php',
    'Luminova\\Debugger\\Performance' => $baseDir . '/system/Debugger/Performance.php',
    'Luminova\\Debugger\\Tracer' => $baseDir . '/system/Debugger/Tracer.php',
    'Luminova\\Email\\Clients\\NovaMailer' => $baseDir . '/system/Email/Clients/NovaMailer.php',
    'Luminova\\Email\\Clients\\PHPMailer' => $baseDir . '/system/Email/Clients/PHPMailer.php',
    'Luminova\\Email\\Clients\\SwiftMailer' => $baseDir . '/system/Email/Clients/SwiftMailer.php',
    'Luminova\\Email\\Helpers\\Helper' => $baseDir . '/system/Email/Helpers/Helper.php',
    'Luminova\\Email\\Mailer' => $baseDir . '/system/Email/Mailer.php',
    'Luminova\\Errors\\ErrorStack' => $baseDir . '/system/Errors/ErrorStack.php',
    'Luminova\\Exceptions\\AppException' => $baseDir . '/system/Exceptions/AppException.php',
    'Luminova\\Exceptions\\BadMethodCallException' => $baseDir . '/system/Exceptions/BadMethodCallException.php',
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
    'Luminova\\Functions\\Escaper' => $baseDir . '/system/Functions/Escaper.php',
    'Luminova\\Functions\\IPAddress' => $baseDir . '/system/Functions/IPAddress.php',
    'Luminova\\Functions\\Maths' => $baseDir . '/system/Functions/Maths.php',
    'Luminova\\Functions\\Normalizer' => $baseDir . '/system/Functions/Normalizer.php',
    'Luminova\\Functions\\TorDetector' => $baseDir . '/system/Functions/TorDetector.php',
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
    'Luminova\\Interface\\HttpClientInterface' => $baseDir . '/system/Interface/HttpClientInterface.php',
    'Luminova\\Interface\\MailerInterface' => $baseDir . '/system/Interface/MailerInterface.php',
    'Luminova\\Interface\\NetworkInterface' => $baseDir . '/system/Interface/NetworkInterface.php',
    'Luminova\\Interface\\RoutableInterface' => $baseDir . '/system/Interface/RoutableInterface.php',
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
    'Luminova\\Routing\\Context' => $baseDir . '/system/Routing/Context.php',
    'Luminova\\Routing\\Router' => $baseDir . '/system/Routing/Router.php',
    'Luminova\\Routing\\Segments' => $baseDir . '/system/Routing/Segments.php',
    'Luminova\\Security\\Crypter' => $baseDir . '/system/Security/Crypter.php',
    'Luminova\\Security\\Csrf' => $baseDir . '/system/Security/Csrf.php',
    'Luminova\\Security\\Encryption\\OpenSSL' => $baseDir . '/system/Security/Encryption/OpenSSL.php',
    'Luminova\\Security\\Encryption\\Sodium' => $baseDir . '/system/Security/Encryption/Sodium.php',
    'Luminova\\Security\\InputValidator' => $baseDir . '/system/Security/InputValidator.php',
    'Luminova\\Seo\\Schema' => $baseDir . '/system/Seo/Schema.php',
    'Luminova\\Seo\\Sitemap' => $baseDir . '/system/Seo/Sitemap.php',
    'Luminova\\Sessions\\CookieManager' => $baseDir . '/system/Sessions/CookieManager.php',
    'Luminova\\Sessions\\Session' => $baseDir . '/system/Sessions/Session.php',
    'Luminova\\Sessions\\SessionManager' => $baseDir . '/system/Sessions/SessionManager.php',
    'Luminova\\Storages\\Adapters\\Adapters' => $baseDir . '/system/Storages/Adapters/Adapters.php',
    'Luminova\\Storages\\FileDelivery' => $baseDir . '/system/Storages/FileDelivery.php',
    'Luminova\\Storages\\FileManager' => $baseDir . '/system/Storages/FileManager.php',
    'Luminova\\Storages\\Storage' => $baseDir . '/system/Storages/Storage.php',
    'Luminova\\Storages\\Uploader' => $baseDir . '/system/Storages/Uploader.php',
    'Luminova\\Template\\Helper' => $baseDir . '/system/Template/Helper.php',
    'Luminova\\Template\\Layout' => $baseDir . '/system/Template/Layout.php',
    'Luminova\\Template\\Smarty' => $baseDir . '/system/Template/Smarty.php',
    'Luminova\\Template\\TemplateView' => $baseDir . '/system/Template/TemplateView.php',
    'Luminova\\Template\\Twig' => $baseDir . '/system/Template/Twig.php',
    'Luminova\\Template\\ViewResponse' => $baseDir . '/system/Template/ViewResponse.php',
    'Luminova\\Time\\CronInterval' => $baseDir . '/system/Time/CronInterval.php',
    'Luminova\\Time\\Task' => $baseDir . '/system/Time/Task.php',
    'Luminova\\Time\\Time' => $baseDir . '/system/Time/Time.php',
    'Luminova\\Time\\Timestamp' => $baseDir . '/system/Time/Timestamp.php',
    'Luminova\\Utils\\Queue' => $baseDir . '/system/Utils/Queue.php',
    'Psr\\Cache\\CacheException' => $vendorDir . '/psr/cache/src/CacheException.php',
    'Psr\\Cache\\CacheItemInterface' => $vendorDir . '/psr/cache/src/CacheItemInterface.php',
    'Psr\\Cache\\CacheItemPoolInterface' => $vendorDir . '/psr/cache/src/CacheItemPoolInterface.php',
    'Psr\\Cache\\InvalidArgumentException' => $vendorDir . '/psr/cache/src/InvalidArgumentException.php',
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
    'Psr\\SimpleCache\\CacheException' => $vendorDir . '/psr/simple-cache/src/CacheException.php',
    'Psr\\SimpleCache\\CacheInterface' => $vendorDir . '/psr/simple-cache/src/CacheInterface.php',
    'Psr\\SimpleCache\\InvalidArgumentException' => $vendorDir . '/psr/simple-cache/src/InvalidArgumentException.php',
);
