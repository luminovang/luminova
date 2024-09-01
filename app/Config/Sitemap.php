<?php 
/**
 * Luminova Framework sitemap configuration.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng/docs/0.0.0/configs/sitemap
 */
namespace App\Config;

use \Luminova\Base\BaseConfig;

final class Sitemap extends BaseConfig
{ 
    /**
     * The maximum limit of URLs to scan when generating the sitemap.
     *
     * Set this variable to limit the number of URLs scanned during the sitemap generation process.
     * A value of 0 (zero) indicates no limit, meaning all accessible URLs will be scanned.
     * If you encounter performance issues or memory constraints, consider setting a reasonable limit.
     *
     * @var int $maxScan
     */
    public int $maxScan = 0;

    /**
     * Sets the allowed scan start URI prefix for sitemap generation.
     *
     * This option allows you to restrict sitemap generation to URLs that match a specific prefix.
     * The prefix is appended to your start URL.
     * To scan all URLs starting from the start url set this variable to blank string.
     *
     * @var string $scanUrlPrefix
     *
     * @example If your start URL is `http://localhost/example.com/` and the prefix is `blog`,
     *          the sitemap will only include URLs that match `http://localhost/example.com/blog/*`.
     */
    public string $scanUrlPrefix = '';

    /**
     * URLs, URL patterns, or full URLs to ignore when generating a sitemap.
     *
     * This array defines specific URLs, URL patterns, or full URLs that should be ignored
     * when generating a sitemap. Ignored URLs will not be included in the sitemap XML output.
     *
     * Each element in the array can be:
     * - A full URL to completely exclude from the sitemap.
     * - A URL pattern using wildcard characters (`*`) to match and exclude multiple URLs.
     *   Example: `✸/admin/login/✸` will match any URL containing `✸/admin/login/✸` and exclude them.
     *
     * @var array<int,string> $ignoreUrls
    */
    public array $ignoreUrls = [
        '*/api/*'
    ];

    /**
     * URL patterns associated with views for determining last modified timestamps in a sitemap.
     *
     * This array defines URL patterns corresponding to views within your application. When generating
     * a sitemap, these patterns are used to identify views and retrieve their last modified timestamps.
     *
     * To utilize this feature, register URL patterns that match your web routing patterns. Use the array
     * key to represent the view file name associated with each pattern, without the extension [.php, or .tpl].
     *
     * @example Suppose the URL https://example.com/blog/blog-id corresponds to the `viewBlogs.php` file
     *          located in `resources/views/`. Your route pattern might be:
     *          `$router->get('/blog/([a-zA-Z0-9-]+)', 'BlogController::blog');`.
     *          You would register this pattern as: `['viewBlogs' => '/blog/([a-zA-Z0-9-]+)']`.
     *
     * @var array<string,string> $viewUrlPatterns
     */
    public array $viewUrlPatterns = [];
}