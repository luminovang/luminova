<?php 
/**
 * Luminova Framework AI configuration.
 *
 * @package   Luminova
 * @author    Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license   See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\Configuration;

final class AI extends Configuration
{
    /**
     * AI provider handler class.
     * 
     * Currently, only `OpenAI` is supported.
     *
     * @var string $handler
     */
    public string $handler = 'OpenAI';

    /**
     * API key for authenticating OpenAI requests.
     *
     * @var string $apiKey
     * @see https://platform.openai.com/
     */
    public string $apiKey = '';

    /**
     * OpenAI organization ID (if your account belongs to one).
     * 
     * Example: `org-xxxx`
     *
     * @var string|null $organization
     * @see https://platform.openai.com/docs/api-reference/authentication
     * @see https://platform.openai.com/settings/organization/general
     */
    public ?string $organization = null;

    /**
     * OpenAI project ID to group API usage.
     *
     * @var string|null $project
     */
    public ?string $project = null;

    /**
     * OpenAI API version to use.
     *
     * @var string $version
     */
    public string $version = 'v1';
}