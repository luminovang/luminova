<?php
/**
 * Sends fatal errors to a remote server in production environments.
 * To enable, specify the endpoint URL in `logger.remote.logs` within the environment configuration (.env).
 *
 * @var \Luminova\Errors\ErrorHandler $stack
 */
use Luminova\Http\Network;
use Luminova\Errors\ErrorHandler;
use Luminova\Exceptions\AppException;

include_once __DIR__ . '/tracing.php';

$endpoint = env('logger.remote.logs');

if ($endpoint) {
    // Default error details
    $title = 'Unknown Error';
    $heading = 'An unknown error occurred';
    $details = 'No additional details available.';
    $tracer = 'No debug tracing available';

    if ($stack instanceof ErrorHandler) {
        ob_start();
        getDebugTracing($stack->getBacktrace());
        $tracer = ob_get_clean();

        // Error details from stack
        $title = htmlspecialchars($stack->getName(), ENT_QUOTES);
        $heading = sprintf('%s #%d', $title, $stack->getCode());
        $details = htmlspecialchars($stack->getMessage(), ENT_QUOTES);
    }

    $payload = [
        'title'    => sprintf('%s (v%.1f) Error Occurred: %s', APP_NAME, APP_VERSION, $title),
        'host'     => APP_HOSTNAME,
        'heading'  => $heading,
        'details'  => $details,
        'tracer'   => $tracer,
        'version'  => APP_VERSION,
    ];

    // Fiber for asynchronous background execution
    $fiber = new Fiber(function () use ($endpoint, $payload) {
        $network = new Network();
        try {
            $response = $network->post($endpoint, [
                'body' => $payload
            ]);

            if ($response->getStatusCode() !== 200) {
                logger('error', sprintf(
                    'Failed to send error to remote server: %s | Response: %s',
                    $payload['details'],
                    $response->getContents()
                ));
            }
        } catch (AppException $e) {
            logger('exception', sprintf('Network Exception: %s', $e->getMessage()));
        } catch (Throwable $fe) {
            logger('exception', sprintf('Unexpected Exception: %s', $fe->getMessage()));
        }
    });

    // Start the Fiber and handle potential exceptions
    try {
        $fiber->start();
    } catch (Throwable $e) {
        logger('exception', sprintf('Error starting Fiber: %s', $e->getMessage()), [], true);
    }
}