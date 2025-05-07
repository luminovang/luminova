<?php
/**
 * Sends fatal errors via email in production environments.
 * To enable, specify the recipient email in `logger.mail.logs` within the environment configuration (.env).
 *
 * @var \Luminova\Errors\ErrorHandler|null $stack
 */

use Luminova\Errors\ErrorHandler;
use Luminova\Email\Mailer;
use Luminova\Exceptions\MailerException;

include_once __DIR__ . '/tracing.php';

$recipient = env('logger.mail.logs');

if ($recipient) {
    // Default error details
    $title = 'Unknown Error';
    $heading = 'An unknown error occurred';
    $details = 'No additional details available.';
    $tracer = '<p>No debug tracing available</p>';

    if ($stack instanceof ErrorHandler) {
        ob_start();
        getDebugTracing($stack->getBacktrace());
        $tracer = ob_get_clean();

        // Error details from stack
        $title = htmlspecialchars($stack->getName(), ENT_QUOTES);
        $heading = sprintf('%s #%d', $title, $stack->getCode());
        $details = htmlspecialchars($stack->getMessage(), ENT_QUOTES);
    }

    $subject = sprintf('%s (v%.1f) Error Occurred: %s', APP_NAME, APP_VERSION, $title);
    $body = sprintf(
        '<body><h1>%s</h1><h3>Host: %s</h3><p>%s</p><br/><div>%s</div></body>',
        $heading,
        APP_HOSTNAME,
        $details,
        $tracer
    );

    // Fiber for asynchronous background email sending
    $fiber = new Fiber(function () use ($recipient, $subject, $body, $details) {
        try {
            if (!Mailer::to($recipient)->subject($subject)->body($body)->send()) {
                logger('error', "Failed to send email: $details", []);
            }
        } catch (MailerException $e) {
            logger('exception', sprintf('Mailer Exception: %s', $e->getMessage()));
        } catch (Throwable $fe) {
            logger('exception', sprintf('Fiber Exception: %s', $fe->getMessage()));
        }
    });

    // Start the Fiber and handle errors
    try {
        $fiber->start();
    } catch (Throwable $e) {
        logger('exception', sprintf('Error starting Fiber: %s', $e->getMessage()), [], true);
    }
}