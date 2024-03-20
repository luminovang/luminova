<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname(dirname($vendorDir));

return array(
    'App\\Controllers\\Application' => $baseDir . '/app/Controllers/Application.php',
    'App\\Controllers\\Config\\Config' => $baseDir . '/app/Controllers/Config/Config.php',
    'App\\Controllers\\Config\\Cookie' => $baseDir . '/app/Controllers/Config/Cookie.php',
    'App\\Controllers\\Config\\Files' => $baseDir . '/app/Controllers/Config/Files.php',
    'App\\Controllers\\Config\\IPConfig' => $baseDir . '/app/Controllers/Config/IPConfig.php',
    'App\\Controllers\\Config\\Paths' => $baseDir . '/app/Controllers/Config/Paths.php',
    'App\\Controllers\\Config\\Servers' => $baseDir . '/app/Controllers/Config/Servers.php',
    'App\\Controllers\\Config\\Services' => $baseDir . '/app/Controllers/Config/Services.php',
    'App\\Controllers\\Config\\Session' => $baseDir . '/app/Controllers/Config/Session.php',
    'App\\Controllers\\Config\\Template' => $baseDir . '/app/Controllers/Config/Template.php',
    'App\\Controllers\\Utils\\Func' => $baseDir . '/app/Controllers/Utils/Func.php',
    'App\\Controllers\\Welcome' => $baseDir . '/app/Controllers/Welcome.php',
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'Luminova\\Application\\Application' => $baseDir . '/system/Application/Application.php',
    'Luminova\\Application\\Factory' => $baseDir . '/system/Application/Factory.php',
    'Luminova\\Application\\Paths' => $baseDir . '/system/Application/Paths.php',
    'Luminova\\Application\\Services' => $baseDir . '/system/Application/Services.php',
    'Luminova\\Arrays\\ArrayCountable' => $baseDir . '/system/Arrays/ArrayCountable.php',
    'Luminova\\Arrays\\ArrayInput' => $baseDir . '/system/Arrays/ArrayInput.php',
    'Luminova\\Arrays\\Arrays' => $baseDir . '/system/Arrays/Arrays.php',
    'Luminova\\Base\\BaseApplication' => $baseDir . '/system/Base/BaseApplication.php',
    'Luminova\\Base\\BaseCommand' => $baseDir . '/system/Base/BaseCommand.php',
    'Luminova\\Base\\BaseConfig' => $baseDir . '/system/Base/BaseConfig.php',
    'Luminova\\Base\\BaseController' => $baseDir . '/system/Base/BaseController.php',
    'Luminova\\Base\\BaseFunction' => $baseDir . '/system/Base/BaseFunction.php',
    'Luminova\\Base\\BaseModel' => $baseDir . '/system/Base/BaseModel.php',
    'Luminova\\Base\\BasePaths' => $baseDir . '/system/Base/BasePaths.php',
    'Luminova\\Base\\BaseServers' => $baseDir . '/system/Base/BaseServers.php',
    'Luminova\\Base\\BaseServices' => $baseDir . '/system/Base/BaseServices.php',
    'Luminova\\Base\\BaseViewController' => $baseDir . '/system/Base/BaseViewController.php',
    'Luminova\\Cache\\Cache' => $baseDir . '/system/Cache/Cache.php',
    'Luminova\\Cache\\Compress' => $baseDir . '/system/Cache/Compress.php',
    'Luminova\\Cache\\FileCache' => $baseDir . '/system/Cache/FileCache.php',
    'Luminova\\Cache\\MemoryCache' => $baseDir . '/system/Cache/MemoryCache.php',
    'Luminova\\Cache\\PageViewCache' => $baseDir . '/system/Cache/PageViewCache.php',
    'Luminova\\Command\\Colors' => $baseDir . '/system/Command/Colors.php',
    'Luminova\\Command\\Console' => $baseDir . '/system/Command/Console.php',
    'Luminova\\Command\\Executor' => $baseDir . '/system/Command/Executor.php',
    'Luminova\\Command\\Novakit\\Builder' => $baseDir . '/system/Command/Novakit/Builder.php',
    'Luminova\\Command\\Novakit\\Commands' => $baseDir . '/system/Command/Novakit/Commands.php',
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
    'Luminova\\Config\\Database' => $baseDir . '/system/Config/Database.php',
    'Luminova\\Config\\DotEnv' => $baseDir . '/system/Config/DotEnv.php',
    'Luminova\\Config\\PHPStanRules' => $baseDir . '/system/Config/PHPStanRules.php',
    'Luminova\\Config\\SystemPaths' => $baseDir . '/system/Config/SystemPaths.php',
    'Luminova\\Controllers\\Controller' => $baseDir . '/system/Controllers/Controller.php',
    'Luminova\\Cookies\\Cookie' => $baseDir . '/system/Cookies/Cookie.php',
    'Luminova\\Cookies\\CookieInterface' => $baseDir . '/system/Cookies/CookieInterface.php',
    'Luminova\\Cookies\\Exception\\CookieException' => $baseDir . '/system/Cookies/Exception/CookieException.php',
    'Luminova\\Database\\Columns' => $baseDir . '/system/Database/Columns.php',
    'Luminova\\Database\\Connection' => $baseDir . '/system/Database/Connection.php',
    'Luminova\\Database\\Drivers\\DriversInterface' => $baseDir . '/system/Database/Drivers/DriversInterface.php',
    'Luminova\\Database\\Drivers\\MySqliDriver' => $baseDir . '/system/Database/Drivers/MySqliDriver.php',
    'Luminova\\Database\\Drivers\\PdoDriver' => $baseDir . '/system/Database/Drivers/PdoDriver.php',
    'Luminova\\Database\\Query' => $baseDir . '/system/Database/Query.php',
    'Luminova\\Database\\Results\\Statements' => $baseDir . '/system/Database/Results/Statements.php',
    'Luminova\\Debugger\\PHPStanRules' => $baseDir . '/system/Debugger/PHPStanRules.php',
    'Luminova\\Email\\Clients\\MailClientInterface' => $baseDir . '/system/Email/Clients/MailClientInterface.php',
    'Luminova\\Email\\Clients\\NovaMailer' => $baseDir . '/system/Email/Clients/NovaMailer.php',
    'Luminova\\Email\\Clients\\PHPMailer' => $baseDir . '/system/Email/Clients/PHPMailer.php',
    'Luminova\\Email\\Clients\\SwiftMailer' => $baseDir . '/system/Email/Clients/SwiftMailer.php',
    'Luminova\\Email\\Exceptions\\MailerException' => $baseDir . '/system/Email/Exceptions/MailerException.php',
    'Luminova\\Email\\Helpers\\Helper' => $baseDir . '/system/Email/Helpers/Helper.php',
    'Luminova\\Email\\Mailer' => $baseDir . '/system/Email/Mailer.php',
    'Luminova\\Errors\\Codes' => $baseDir . '/system/Errors/Codes.php',
    'Luminova\\Errors\\Error' => $baseDir . '/system/Errors/Error.php',
    'Luminova\\Exceptions\\AppException' => $baseDir . '/system/Exceptions/AppException.php',
    'Luminova\\Exceptions\\BadMethodCallException' => $baseDir . '/system/Exceptions/BadMethodCallException.php',
    'Luminova\\Exceptions\\ClassException' => $baseDir . '/system/Exceptions/ClassException.php',
    'Luminova\\Exceptions\\DatabaseException' => $baseDir . '/system/Exceptions/DatabaseException.php',
    'Luminova\\Exceptions\\DatabaseLimitException' => $baseDir . '/system/Exceptions/DatabaseLimitException.php',
    'Luminova\\Exceptions\\ErrorException' => $baseDir . '/system/Exceptions/ErrorException.php',
    'Luminova\\Exceptions\\FileException' => $baseDir . '/system/Exceptions/FileException.php',
    'Luminova\\Exceptions\\InvalidException' => $baseDir . '/system/Exceptions/InvalidException.php',
    'Luminova\\Exceptions\\InvalidObjectException' => $baseDir . '/system/Exceptions/InvalidObjectException.php',
    'Luminova\\Exceptions\\LuminovaException' => $baseDir . '/system/Exceptions/LuminovaException.php',
    'Luminova\\Exceptions\\NotFoundException' => $baseDir . '/system/Exceptions/NotFoundException.php',
    'Luminova\\Exceptions\\RuntimeException' => $baseDir . '/system/Exceptions/RuntimeException.php',
    'Luminova\\Exceptions\\ValidationException' => $baseDir . '/system/Exceptions/ValidationException.php',
    'Luminova\\Exceptions\\ViewNotFoundException' => $baseDir . '/system/Exceptions/ViewNotFoundException.php',
    'Luminova\\Functions\\Document' => $baseDir . '/system/Functions/Document.php',
    'Luminova\\Functions\\Escaper' => $baseDir . '/system/Functions/Escaper.php',
    'Luminova\\Functions\\Files' => $baseDir . '/system/Functions/Files.php',
    'Luminova\\Functions\\FunctionTrait' => $baseDir . '/system/Functions/FunctionTrait.php',
    'Luminova\\Functions\\Functions' => $baseDir . '/system/Functions/Functions.php',
    'Luminova\\Functions\\IPAddress' => $baseDir . '/system/Functions/IPAddress.php',
    'Luminova\\Functions\\MathTrait' => $baseDir . '/system/Functions/MathTrait.php',
    'Luminova\\Functions\\StringTrait' => $baseDir . '/system/Functions/StringTrait.php',
    'Luminova\\Functions\\TorDetector' => $baseDir . '/system/Functions/TorDetector.php',
    'Luminova\\Http\\AsyncClientInterface' => $baseDir . '/system/Http/AsyncClientInterface.php',
    'Luminova\\Http\\Client\\Curl' => $baseDir . '/system/Http/Client/Curl.php',
    'Luminova\\Http\\Client\\Guzzle' => $baseDir . '/system/Http/Client/Guzzle.php',
    'Luminova\\Http\\CurlAsyncClient' => $baseDir . '/system/Http/CurlAsyncClient.php',
    'Luminova\\Http\\Exceptions\\ClientException' => $baseDir . '/system/Http/Exceptions/ClientException.php',
    'Luminova\\Http\\Exceptions\\ConnectException' => $baseDir . '/system/Http/Exceptions/ConnectException.php',
    'Luminova\\Http\\Exceptions\\RequestException' => $baseDir . '/system/Http/Exceptions/RequestException.php',
    'Luminova\\Http\\Exceptions\\ServerException' => $baseDir . '/system/Http/Exceptions/ServerException.php',
    'Luminova\\Http\\GuzzleAsyncClient' => $baseDir . '/system/Http/GuzzleAsyncClient.php',
    'Luminova\\Http\\Header' => $baseDir . '/system/Http/Header.php',
    'Luminova\\Http\\Network' => $baseDir . '/system/Http/Network.php',
    'Luminova\\Http\\NetworkAsync' => $baseDir . '/system/Http/NetworkAsync.php',
    'Luminova\\Http\\NetworkClientInterface' => $baseDir . '/system/Http/NetworkClientInterface.php',
    'Luminova\\Http\\NetworkRequest' => $baseDir . '/system/Http/NetworkRequest.php',
    'Luminova\\Http\\NetworkResponse' => $baseDir . '/system/Http/NetworkResponse.php',
    'Luminova\\Http\\Request' => $baseDir . '/system/Http/Request.php',
    'Luminova\\Interface\\ServicesInterface' => $baseDir . '/system/Interface/ServicesInterface.php',
    'Luminova\\Languages\\Translator' => $baseDir . '/system/Languages/Translator.php',
    'Luminova\\Library\\Importer' => $baseDir . '/system/Library/Importer.php',
    'Luminova\\Logger\\Logger' => $baseDir . '/system/Logger/Logger.php',
    'Luminova\\Logger\\LoggerAware' => $baseDir . '/system/Logger/LoggerAware.php',
    'Luminova\\Logger\\NovaLogger' => $baseDir . '/system/Logger/NovaLogger.php',
    'Luminova\\Models\\Model' => $baseDir . '/system/Models/Model.php',
    'Luminova\\Models\\PushMessage' => $baseDir . '/system/Models/PushMessage.php',
    'Luminova\\Notifications\\FirebasePusher' => $baseDir . '/system/Notifications/FirebasePusher.php',
    'Luminova\\Notifications\\FirebaseRealtime' => $baseDir . '/system/Notifications/FirebaseRealtime.php',
    'Luminova\\Routing\\Bootstrap' => $baseDir . '/system/Routing/Bootstrap.php',
    'Luminova\\Routing\\Router' => $baseDir . '/system/Routing/Router.php',
    'Luminova\\Security\\Csrf' => $baseDir . '/system/Security/Csrf.php',
    'Luminova\\Security\\Encryption\\AES' => $baseDir . '/system/Security/Encryption/AES.php',
    'Luminova\\Security\\Encryption\\EncryptionInterface' => $baseDir . '/system/Security/Encryption/EncryptionInterface.php',
    'Luminova\\Security\\InputValidator' => $baseDir . '/system/Security/InputValidator.php',
    'Luminova\\Security\\ValidatorInterface' => $baseDir . '/system/Security/ValidatorInterface.php',
    'Luminova\\Seo\\Meta' => $baseDir . '/system/Seo/Meta.php',
    'Luminova\\Sessions\\CookieManager' => $baseDir . '/system/Sessions/CookieManager.php',
    'Luminova\\Sessions\\Session' => $baseDir . '/system/Sessions/Session.php',
    'Luminova\\Sessions\\SessionInterface' => $baseDir . '/system/Sessions/SessionInterface.php',
    'Luminova\\Sessions\\SessionManager' => $baseDir . '/system/Sessions/SessionManager.php',
    'Luminova\\Storage\\Helper' => $baseDir . '/system/Storage/Helper.php',
    'Luminova\\Storage\\S3' => $baseDir . '/system/Storage/S3.php',
    'Luminova\\Storage\\Uploader' => $baseDir . '/system/Storage/Uploader.php',
    'Luminova\\Template\\Smarty' => $baseDir . '/system/Template/Smarty.php',
    'Luminova\\Template\\Template' => $baseDir . '/system/Template/Template.php',
    'Luminova\\Template\\TemplateTrait' => $baseDir . '/system/Template/TemplateTrait.php',
    'Luminova\\Template\\ViewModel' => $baseDir . '/system/Template/ViewModel.php',
    'Luminova\\Time\\Task' => $baseDir . '/system/Time/Task.php',
    'Luminova\\Time\\Time' => $baseDir . '/system/Time/Time.php',
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
