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

use \Luminova\Base\Configuration;

/**
 * CLI novakit sitemap, broken links, site link-tree generator configuration
 */
final class Sitemap extends Configuration
{
    /**
     * Maximum number of URLs to scan when generating the sitemap.
     *
     * Set to `0` to scan all URLs. Use a lower number if you want to limit scanning
     * for performance reasons.
     *
     * @var int $maxScan The limit of URLs to scan. 0 = no limit.
     *
     * @example - Example:
     * ```php
     * $config->maxScan = 500; // scan up to 500 URLs
     * ```
     */
    public int $maxScan = 0;

    /**
     * Delay between scanning each URL, in seconds.
     *
     * Controls how fast the sitemap generator moves through URLs.
     * Minimum value: 1 second.
     *
     * @var int $scanSpeed Delay in seconds between each URL scan.
     *
     * @example - Example:
     * ```php
     * $config->scanSpeed = 3; // wait 3 seconds between URLs
     * ```
     */
    public int $scanSpeed = 5;

    /**
     * Maximum execution time for the sitemap scraping script, in seconds.
     *
     * Set to `0` for no time limit. The generator may extend PHP's max execution time if needed.
     *
     * @var int $maxExecutionTime Maximum time in seconds (0 = unlimited)
     *
     * @example - Example:
     * ```php
     * $config->maxExecutionTime = 600; // allow up to 10 minutes
     * ```
     */
    public int $maxExecutionTime = 300;

    /**
     * Optional URL prefix to start scanning from.
     *
     * Only URLs that start with this prefix will be included. Leave empty to scan all URLs
     * from the start URL.
     *
     * @var string $scanUrlPrefix Start URL prefix for scanning
     *
     * @example - Example:
     * ```php
     * $config->scanUrlPrefix = 'blog'; // scan only URLs starting with /blog
     * ```
     */
    public string $scanUrlPrefix = '';

    /**
     * How often the page is expected to change.
     *
     * Helps search engines prioritize crawling.
     * Valid values: 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'
     * Set to `null` to omit this info.
     *
     * @var ?string $changeFrequently Expected page update frequency or null
     *
     * @example - Example:
     * ```php
     * $config->changeFrequently = 'daily'; // page updates daily
     * ```
     */
    public ?string $changeFrequently = null;

    /**
     * Include static `.html` versions of URLs in the sitemap.
     *
     * Useful for sites with static HTML pages alongside dynamic ones.
     *
     * @var bool $includeStaticHtml Enable or disable static HTML URL inclusion
     *
     * @example - Example:
     * ```php
     * $config->includeStaticHtml = true;
     * // example.com/page will include example.com/page.html
     * ```
     */
    public bool $includeStaticHtml = false;

    /**
     * Skip generating static `.html` versions for matching URL patterns.
     *
     * Use wildcard `*` to match multiple URLs.
     *
     * @var array<int,string> $skipStaticHtml Patterns of URLs to exclude
     *
     * @example - Example: 
     * ```php
     * $config->skipStaticHtml = ['*\/api\/*', '*\/docs\/*'];
     * ```
     */
    public array $skipStaticHtml = ['*/api/*'];

    /**
     * URLs or patterns to ignore when generating the sitemap.
     *
     * Use `@` to indicate a raw regex pattern, otherwise wildcard patterns are allowed.
     *
     * @var array<int,string> $ignoreUrls URLs or patterns to ignore
     *
     * @example - Example:
     * ```php
     * $config->ignoreUrls = [
     *     '*\/admin\/login/*',        // wildcard pattern
     *     '@(^|.*\/)?scheme:\/\/.*' // raw regex
     * ];
     * ```
     */
    public array $ignoreUrls = ['*/api/*'];

    /**
     * URL patterns mapped to views for determining last modified timestamps.
     *
     * Use view file names (without extension) as keys and route patterns as values.
     *
     * @var array<string,string> $viewUrlPatterns Patterns to extract last modify timestamp
     *
     * @example - Example:
     * Suppose the URL https://example.com/blog/blog-id corresponds to the `blog.php` file
     * located in `/resources/Views/blog.php`. 
     *
     * Your route pattern might look like:
     * - `Method-Based`: `Router::get('/blog/([a-zA-Z0-9-]+)', 'BlogController::blog');`.
     * - `Attribute-Based`: `#[Route('/blog/([a-zA-Z0-9-]+)', method: ['GET'])]`.
     * - `Handler`: `return $this->view('blog', ...)`, `return view('blog', ...)`
     *
     * You would register this pattern as:
     *
     * ```php
     * $config->viewUrlPatterns = [
     *     'blog' => '/blog/([a-zA-Z0-9-]+)'
     * ];
     * ```
     */
    public array $viewUrlPatterns = [];

    /**
     * XPath selector to extract page descriptions when building a LinkTree.
     *
     * Should point to an element containing the description, like a `<p>` tag.
     *
     * @var string|null $linkTreeDescriptionSelector XPath selector string or null
     *
     * @example - Example:
     * ```php
     * $config->linkTreeDescriptionSelector = '//p[@aria-label="Subheading for this page"]';
     * ```
     */
    public ?string $linkTreeDescriptionSelector = null;
}