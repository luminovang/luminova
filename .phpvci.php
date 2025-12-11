<?php
/**
 * Luminova Shared Module Autoload Configuration (PHPVCI)
 *
 * Defines the shared framework installation settings used by the bootloader
 * to resolve and load framework modules from a PHPVCI installation.
 *
 * Configuration options:
 * - enabled: Enables or disables shared framework loading.
 * - version: Required framework version constraint.
 * - paths.target: Target framework release directory.
 *
 * The following files and directories remain local:
 * `system/plugins/*`, `system/Boot.php`, `app/*`, `routes/*`,
 * `resource/*`, and other application-related files.
 *
 * Ensure the target release satisfies the configured version constraint.
 * Loading an incompatible framework release may cause runtime errors.
 *
 * @link https://luminova.ng/docs/0.0.0/boot/phpvci
 * @link https://github.com/phpvci/admin
 * @link https://github.com/phpvci/phpvci
 *
 * @return array{
 *     enabled: bool,
 *     version: string,
 *     paths: array{
 *         target: string|callable():string
 *     }
 * }
 *
 * **Dynamic Resolution:**
 * The `paths.target` support callable values, allowing
 * framework releases to be resolved at runtime.
 *
 * This is useful for applications serving multiple API versions through
 * versioned URIs, such as `https://example.com/api/v1/`.
 *
 * When using dynamic resolution, ensure the version constraint accepts
 * the resolved framework release. You can use a broad constraint such as
 * `^3` or `>=3.9` when appropriate.
 *
 * @example - Static Configuration:
 * ```php
 * return [
 *     'enabled' => true,
 *     'version' => '^3.9',
 *     'paths' => [
 *         'target' => '/opt/phpvci.packages/releases/3.9.1',
 *     ],
 * ];
 * ```
 *
 * @example - Dynamic Configuration:
 * ```php
 * return [
 *     'enabled' => true,
 *     'version' => '^3.8',
 *     'paths' => [
 *         'target' => static function (): string {
 *             $version = match (true) {
 *                 str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/v2/') => '3.9.1',
 *                 str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/v1/') => '3.8.0',
 *                 default => '3.9.1',
 *             };
 *
 *             return "/opt/phpvci.packages/releases/{$version}";
 *         },
 *     ],
 * ];
 * ```
 */
return [
    /**
     * Enable shared framework installation.
     * 
     * @var bool
     */
    'enabled' => false,

    /**
     * Required framework version constraint.
     *
     * @var string
     */
    'version' => '>=3.9',

    /**
     * Shared framework installation paths.
     * 
     * @var array{target:string|callable():string}
     */
    'paths' => [
        /**
         * Target framework release directory.
         *
         * Accepts an absolute directory path or a callable returning one.
         *
         * @var string|callable():string
         */
        'target' => '/opt/phpvci.packages/releases/3.9.1',
    ],
];