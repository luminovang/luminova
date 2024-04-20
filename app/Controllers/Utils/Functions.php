<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Utils;

use \Luminova\Base\BaseFunction;
use \Luminova\Functions\IPAddress;
use \Luminova\Functions\Files;
use \Luminova\Functions\TorDetector;
/**
 * Functions methods.
 *
 * @method static TorDetector         tor()       @return TorDetector
 * @method static Files               file()      @return Files
 * @method static IPAddress           ip()        @return IPAddress
 * @method static Maths               math()      @return Maths
 * @method static array|string        escape($input, $context, $encoding)      @return array|string
*/
class Functions extends BaseFunction
{
}