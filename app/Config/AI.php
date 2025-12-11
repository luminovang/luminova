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

use \Luminova\Base\Configuration;

final class AI extends Configuration
{
    /**
     * The AI client handler to use.
     *
     * Determines which AI provider implementation will handle requests.
     * 
     * Supported handlers include: 
     * 
     * - `OpenAI`    - {@see Luminova\AI\Client\OpenAI}
     * - `Ollama`    - {@see Luminova\AI\Client\Ollama}
     * - `Anthropic` - {@see Luminova\AI\Client\Anthropic}
     *
     * @var string $handler
     */
    public string $handler = 'Ollama';

    /**
     * The default AI client model to use.
     *
     * @var string|null $model
     * @see https://luminova.ng/docs/0.0.0/ai-client/model
     */
    public ?string $model = 'qwen3:8b';

    /**
     * Base API endpoint for the selected AI provider.
     *
     * Examples:
     * - Ollama (local): `http://localhost:11434/api/`
     * - OpenAI: `https://api.openai.com/v1/`
     * - Anthropic: `https://api.anthropic.com/v1/`
     *
     * @var string $baseUrl
     */
    public string $baseUrl = 'http://localhost:11434/api/';

    /**
     * API key used to authenticate requests to the provider.
     *
     * Not required for local Ollama instances unless using a proxy or
     * hosted Ollama service.
     *
     * @var string $apiKey
     * 
     * @see https://platform.openai.com/
     * @see https://ollama.com
     * @see https://docs.anthropic.com/
     */
    public string $apiKey = '';

    /**
     * OpenAI organization identifier.
     *
     * Required only if the API key belongs to an organization account.
     *
     * Example: `org-xxxx`
     *
     * @var string|null $organization
     * 
     * @see https://platform.openai.com/docs/api-reference/authentication
     */
    public ?string $organization = null;

    /**
     * OpenAI project identifier.
     *
     * Used to group API usage and billing under a specific project.
     *
     * @var string|null $project
     */
    public ?string $project = null;

    /**
     * API version identifier sent as a header when required.
     *
     * Examples:
     * - OpenAI: `v1`
     * - Anthropic: `2023-06-01`
     *
     * Not used by Ollama.
     *
     * @var string $version
     */
    public string $version = 'v1';

    /**
     * Optional Anthropic beta feature flags.
     *
     * These are sent using the `anthropic-beta` header when interacting
     * with the Anthropic API.
     *
     * Example:
     * `interleaved-thinking-2025-05-14`
     *
     * @var string|null $betaFeatures
     */
    public ?string $betaFeatures = null;
}