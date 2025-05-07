<?php 
/**
 * Luminova Framework sitemap configuration.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng/docs/0.0.0/configs/sitemap
 * @link https://www.sitemaps.org/protocol.html
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
     * @var int $maxScan The maximum number of URLs to scan during the sitemap generation process.
     */
    public int $maxScan = 0;

    /**
     * The number of seconds to wait before moving to the next URL during sitemap generation.
     * 
     * This value determines the delay for downloading content from each URL, impacting the overall speed
     * of the sitemap generation process. The minimum allowed value is 1 second.
     *
     * @var int $scanSpeed The delay time in seconds for each URL scan.
     * @since 3.3.4
     */
    public int $scanSpeed = 5;

    /**
     * Sets the sitemap scrap script's maximum execution time.
     * 
     * If the provided timeout exceeds the current limit it adjust the time.
     * 
     * @var int $maxExecutionTime The maximum execution time in seconds (0 for no limit).
     * @since 3.5.3
     */
    public int $maxExecutionTime = 300;

    /**
     * Sets the allowed scan start URI prefix for sitemap generation.
     *
     * This option allows you to restrict sitemap generation to URLs that match a specific prefix.
     * The prefix is appended to your start URL.
     * To scan all URLs starting from the start url set this variable to blank string.
     *
     * @var string $scanUrlPrefix A string specifying the start URL scan.
     *
     * @example If your start URL is `http://localhost/example.com/` and the prefix is `blog`,
     *          the sitemap will only include URLs that match `http://localhost/example.com/blog/*`.
     */
    public string $scanUrlPrefix = '';

    /**
     * Indicates how frequently the page is likely to change in the sitemap.
     *
     * This property provides general information to search engines about the expected 
     * frequency of changes to the associated URL. While this value does not guarantee 
     * how often search engines will crawl the page, it helps them prioritize their crawling 
     * strategy based on the indicated frequency. 
     * 
     * Valid values include:
     * - `always`: The page is updated continuously.
     * - `hourly`: The page is updated at least once an hour.
     * - `daily`: The page is updated at least once a day.
     * - `weekly`: The page is updated at least once a week.
     * - `monthly`: The page is updated at least once a month.
     * - `yearly`: The page is updated at least once a year.
     * - `never`: The page is not expected to change.
     * 
     * If set to `null`, no frequency information will be provided for the page.
     *
     * @var ?string $changeFrequently Set to one of the valid frequency values to indicate how often the page is likely to change, or `null` to disable.
     */
    public ?string $changeFrequently = null;

    /**
     * Determines whether to include a static `.html` version of URLs in the sitemap XML.
     *
     * When set to `true`, this property ensures that each URL entry in the sitemap 
     * is accompanied by a corresponding `.html` static version. This is useful for sites that 
     * have static HTML alternatives for certain pages or sections, allowing search engines 
     * to index both the dynamic and static versions. 
     * 
     * Example:
     * - If the URL is `https://example.com/page`, the static `.html` version will be included 
     *   as `https://example.com/page.html` in the sitemap if this setting is enabled.
     *
     * @var bool $includeStaticHtml Set to `true` to enable static URL inclusion, or `false` to disable it.
     * 
     * > **Note:** By default the start URL will not include the `.html` (e.g, `https://example.com/`).
     */
    public bool $includeStaticHtml = false;

    /**
     * List of URL patterns to skip when generating static `.html` versions in the sitemap.
     *
     * This array defines specific URL patterns that should not have static `.html` 
     * versions included in the sitemap XML. When a URL matches any of these patterns, 
     * the sitemap generator will exclude the static `.html` version for that URL. 
     * This is useful for excluding certain sections of the site, such as dynamic content 
     * that does not require a static HTML counterpart (e.g., documentation pages, forums, 
     * or external links).
     *
     * Supported patterns:
     * - Wildcard (`*`) is used to match any characters in the URL.
     * 
     * Examples:
     * - `'✸/foo/bar/✸'`: Excludes all URLs under the `docs/edit` path.
     *
     * @var array<string,string> $skipStaticHtml An array of URL patterns to exclude from static `.html` generation.
     */
    public array $skipStaticHtml = [
        '*/api/*'
    ];

    /**
     * URLs, URL patterns, or full URLs to ignore when generating a sitemap.
     *
     * This array defines specific URLs, URL patterns, or full URLs that should be ignored
     * when generating a sitemap. Ignored URLs will not be included in the sitemap XML output.
     * 
     * If pattern starts with '@', treat as raw regex otherwise convert glob pattern to regex.
     *
     * Each element in the array can be:
     * - A full URL to completely exclude from the sitemap.
     * - A URL pattern using wildcard characters (`*`) to match and exclude multiple URLs.
     *
     * @var array<int,string> $ignoreUrls An array of URL patterns to exclude from sitemap generation.
     * 
     * @example - Patterns:
     * 
     * - `✸/admin/login/✸` will match any URL containing `https://example.com/any/admin/login/✸` and exclude them.
     * - `@(^|.*\/)?scheme:\/\/.*` will match any URL containing `scheme://✸` and exclude them.
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
     * @var array<string,string> $viewUrlPatterns An array of URL route patterns to extract last modify timestamp.
     */
    public array $viewUrlPatterns = [];
}