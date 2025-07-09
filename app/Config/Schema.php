<?php 
/**
 * Luminova Framework Schema Configuration
 *
 * This file defines metadata used for SEO and structured data.
 * 
 * Fill these values with details about your application, website, or company.
 * Luminova uses this configuration to build schema.org JSON-LD objects
 * that improve search engine visibility.
 *
 * @package   Luminova
 * @author    Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license   See LICENSE file
 * @link      https://luminova.ng
 * @see       https://luminova.ng/docs/0.0.0/configs/structured-data
 */
use function \Luminova\Funcs\{locale, href, asset};

return [
    'id'          => href(null, true),
    'url'         => href('/', true),
    'name'        => APP_NAME,
    'language'    => 'en-US',
    'locale'      => locale(),
    'title'       => 'Your Website Title',
    'description' => 'Your Website Description',

    'image' => [
        'url'    => 'app-square-icon.png',
        'width'  => 512,
        'height' => 512,
        'type'   => 'image/png',
        'assets' => asset('images', true),
    ],

    'organization' => [
        'name'        => 'Your Company Name',
        'email'       => 'support@' . APP_HOSTNAME,
        'description' => 'Your Company Description',
        'brands'      => [],
        'url'         => APP_URL,
        'links'       => [],
        'logo'        => [
            'url'    => 'company-square-logo.png',
            'width'  => 512,
            'height' => 512,
        ],
    ],

    'twitter'     => [],
    'facebook'    => [],
    'keywords'    => [],
    'breadcrumbs' => [],
    'global.entities' => [],
    'search' => [
        'query' => '/search?q={search_term_string}',
        'input' => 'search_term_string',
    ],
];