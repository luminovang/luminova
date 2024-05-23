<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace App\Controllers\Config;

use \Luminova\Base\BaseConfig;

final class Browser extends BaseConfig
{
    /**
     * An associative array of strings representing common keywords or patterns found in robots user agent string.
     * Where array key is the robot pattern or keyword and the value is the name.
     *
     * @var array<string,string> $robotPatterns
     */
    public array $robotPatterns = [
        'Googlebot'          => 'Googlebot',
        'bingbot'            => 'Bingbot',
        'Slurp'              => 'Yahoo! Slurp',
        'msnbot'             => 'MSNBot',
        'yahoo'              => 'Yahoo',
        'yandex'             => 'YandexBot',
        'adsbot-google'      => 'AdsBot Google',
        'feedfetcher-google' => 'Feedfetcher Google',
        // add more as needed
    ];

    /**
     * An associative array of strings representing common keywords found in mobile device user agents.
     * Where array key is the mobile keyword and the value is the name.
     *
     * @var array<string,string> $mobileKeywords 
     * NOTE: The order is important
     */
    public array $mobileKeywords = [
        'android'         => 'Android',
        'blackberry'      => 'BlackBerry',
        'iphone'          => 'iOS',
        'ipad'            => 'iOS',
        'ipod'            => 'iOS',
        'windows phone'   => 'Windows Phone',
        'operamini'       => 'Opera Mini',
        'opera mini'      => 'Opera Mini',
        'Safari'          => 'Safari',
        'Mobile Safari'   => 'Safari',
        'mobile'          => 'Generic Mobile',
        'smartphone'      => 'Generic Mobile',
        'cellphone'       => 'Generic Mobile',
        //add more as needed
    ];

    /**
     * An array of strings representing common keywords or patterns found in browser user agents.
     *
     * @var string[] $browsers
     * 
     * > This will be taken in consideration when you call method `$userAgent->isTrusted()`.
     * > Its also a good authentication when you are developing a hybrid mobile app, 
     * to ensure only your webview can access.
    */
    public array $browsers = [
        'Chrome',
        'Safari',
        'Firefox',
        'Mozilla',
        'Edge',
        'Opera',
        'MSIE',
        'Trident',
        'Edg',
        'OPR',
        'Flock',
        'Spartan',
        'Chimera',
        'Phoenix',
        'Firebird',
        'Lynx',
        'Links',
        'IBrowse',
        'Ubuntu',
        'Vivaldi',
    ];
}