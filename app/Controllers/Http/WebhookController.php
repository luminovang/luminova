<?php 
/**
 * Luminova Framework Webhook example
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Http;

use \Luminova\Http\Webhook;
use \Luminova\Base\Controller;
use \Luminova\Attributes\Route;
use \Luminova\Attributes\Prefix;
use function \Luminova\Funcs\{root, response};

#[Prefix('/webhook/(:base)', exclude: ['api'])]
class WebhookController extends Controller 
{
    private ?Webhook $webhook = null;

    protected function onCreate(): void 
    {
        $signature = $this->request->header->get('X-Signature');
        $this->webhook = Webhook::listen($signature)
            ->setAcceptEvents('app.test')
            ->setAlgo('sha256')
            ->setSecret('your-secret-key');

        // For testing only
        $this->webhook->setAllowedOrigins([
            'localhost'
        ])->setAllowEmptyOrigin(true);
    }

    /**
     * Handle incoming webhook request for test.
     * 
     * @return int Return status code.
     */
    #[Route('/webhook/test', methods: ['POST'])]
    public function test(): int
    {
        return $this->webhook->unpack(function(mixed $payload): int {
            $path = root('/writeable/', 'webhook.test.txt');

            @file_put_contents($path, var_export($payload, true));

            return response()->json([
                'ok' => true
            ]);
        });
    }
}