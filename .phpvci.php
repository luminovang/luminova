<?php
/**
 * Luminova shared Module Autoload Configuration (phpvci)
 *
 * Defines the shared framework installation paths and version constraint
 * used by the bootloader.
 *
 * Configuration options:
 * - enabled: Enables or disables the shared framework installation.
 * - version: Required framework version constraint.
 * - paths.target: Target framework release directory.
 *
 * The following files and directories remain local:
 * `system/plugins/*`, `system/Boot.php`, `app/*`, `routes/*`,
 * `resource/*`, and all other application-related files.
 *
 * Ensure the target release satisfies the version constraint. An
 * incompatible framework version may cause runtime errors.
 *
 * @link https://luminova.ng/docs/0.0.0/boot/phpvci
 * @link https://github.com/phpvci/admin
 * @link https://github.com/phpvci/phpvci
 *
 * @return array{
 *     enabled: bool,
 *     version: string,
 *     paths: array{
 *         target: string
 *     }
 * }
 */
return [
    /**
     * Enable shared framework installation.
     */
    'enabled' => false,

    /**
     * Required framework version constraint.
     */
    'version' => '>=3.9',

    /**
     * Shared framework installation paths.
     */
    'paths' => [
        'target' => '/opt/phpvci.packages/releases/3.9.1',
    ],
];