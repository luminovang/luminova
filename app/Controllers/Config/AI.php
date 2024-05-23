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

final class AI extends BaseConfig
{
    /**
     * The `AI` handler class (default: `OpenAI`).
     * 
     * @var string $handler
     * > Currently only OpenAI is supported
    */
    public string $handler = 'OpenAI';

    /**
     * Set your OpenAI api secret key.
     * 
     * @var string $apiKey
     * @see https://platform.openai.com/
    */
    public string $apiKey = '';

    /**
     * Set your OpenAI Organization ID.
     * 
     * @param string $organization org-xxxx
     * @see https://platform.openai.com/docs/api-reference/authentication
     * @see https://platform.openai.com/settings/organization/general
    */
    public ?string $organization = null;

    /**
     * Set your OpenAI Project ID.
     * 
     * @param string $project
    */
    public ?string $project = null;

    /**
     * Set your OpenAI API version.
     * 
     * @param string $version
    */
    public string $version = 'v1';
}