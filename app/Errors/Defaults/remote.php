<?php
/**
 * Sends fatal errors to a remote server in production environments.
 * To enable, specify the endpoint URL in `logger.remote.logs` within the environment configuration (.env).
 */
use Luminova\Http\Client\Novio;
use function Luminova\Funcs\logger;
use Luminova\Foundation\Error\Message;
use Luminova\Exceptions\LuminovaException;

/**
 * Send error to remote logging endpoint.
 *
 * @param Message|null $error
 */
(function (mixed $error): void {
    $endpoint = env('logger.remote.logs');

    if (!$endpoint) {
        return;
    }

    $title   = 'Unknown Error';
    $heading = 'An unknown error occurred';
    $details = 'No additional details available.';
    $tracer  = 'No debug tracing available';

    if ($error instanceof Message) {
        include_once __DIR__ . '/tracing.php';

        ob_start();
        __get_debug_tracing($error->getBacktrace());
        $traceOutput = ob_get_clean();

        if ($traceOutput) {
            $tracer = $traceOutput;
        }

        $title   = htmlspecialchars($error->getName(), ENT_QUOTES, 'UTF-8');
        $heading = sprintf('%s #%d', $title, $error->getCode());
        $details = htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8');
    }

    $payload = [
        'title'   => sprintf('%s (v%.1f) Error Occurred: %s', APP_NAME, APP_VERSION, $title),
        'host'    => APP_HOSTNAME,
        'heading' => $heading,
        'details' => $details,
        'tracer'  => $tracer,
        'version' => APP_VERSION,
        'time'    => date('c'),
    ];

    try {
        $client = new Novio();

        $response = $client->request('POST', $endpoint, [
            'body'    => $payload,
            'timeout' => 2.0,
        ]);

        if ($response->getStatusCode() !== 200) {
            logger('error', sprintf(
                'Remote log failed [%d]: %s',
                $response->getStatusCode(),
                $payload['details']
            ));
        }

    } catch (LuminovaException $e) {
        logger('exception', sprintf('Network Exception: %s', $e->getMessage()));
    } catch (Throwable $e) {
        logger('exception', sprintf('Unexpected Exception: %s', $e->getMessage()));
    }
})($error ?? null);