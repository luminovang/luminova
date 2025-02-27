<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace App\Config;

use \Luminova\Base\BaseConfig;

final class AI extends BaseConfig
{
    /**
     * The AI handler class.
     * Currently, only `OpenAI` is supported.
     * 
     * @var string $handler
     */
    public string $handler = 'OpenAI';

    /**
     * The API secret key for authenticating OpenAI requests.
     * 
     * @var string $apiKey
     * @see https://platform.openai.com/
     */
    public string $apiKey = '';

    /**
     * The OpenAI Organization ID.
     * This is required if your OpenAI account belongs to an organization.
     * 
     * @var string|null $organization (e.g., `org-xxxx`)
     * @see https://platform.openai.com/docs/api-reference/authentication
     * @see https://platform.openai.com/settings/organization/general
     */
    public ?string $organization = null;

    /**
     * The OpenAI Project ID.
     * This helps organize API usage under a specific project.
     * 
     * @var string|null $project
     */
    public ?string $project = null;

    /**
     * The OpenAI API version to use.
     * 
     * @var string $version
     */
    public string $version = 'v1';
}