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

final class Browser 
{
    /**
     * Known patterns or name for common robots.
     * Associative array where keys are robot names and values are regular expression patterns or name.
     *
     * @var array<string,string> $robotPatterns 
     */
    public static array $robotPatterns = [
        'Googlebot' => '/Googlebot/i',
        'Bingbot' => '/bingbot/i',
        'Yahoo! Slurp' => '/Slurp/i',
        //add more as needed
    ];

    /**
     * Common keywords/patterns found in mobile user agents.
     * An array of strings representing common keywords or patterns found in mobile user agents.
     *
     * @var array<string> $mobileKeywords 
     */
    public static array $mobileKeywords = [
        'Mobile', 
        'Android', 
        'BlackBerry', 
        'iPhone', 
        'iPad', 
        'iPod', 
        'Windows Phone', 
        'Opera Mini',
        'Mobile Safari',
        //add more as needed
    ];

    /**
     * Common keywords/patterns found in browser user agents.
     * An array of strings representing common keywords or patterns found in browser user agents.
     *
     * @var array<string> $browsers
     */
    public static array $browsers = [
        'Chrome', 
        'Firefox', 
        'Safari', 
        'Edge', 
        'Opera', 
        'MSIE', 
        'Trident',
        //add more as needed
    ];
}