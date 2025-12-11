<?php
/**
 * Sends fatal errors via email in production environments.
 * To enable, specify the recipient email in `logger.mail.logs`
 * within the environment configuration (.env).
 */
use Luminova\Components\Email\Mailer;
use function Luminova\Funcs\logger;
use Luminova\Foundation\Error\Message;
use Luminova\Exceptions\MailerException;

/**
 * Send fatal error email if configured.
 *
 * @param Message|null $error
 */
(function (mixed $error): void {

    $recipient = env('logger.mail.logs');

    if (!$recipient) {
        return;
    }

    $title   = 'Unknown Error';
    $heading = 'An unknown error occurred';
    $details = 'No additional details available.';
    $tracer  = '<p>No debug tracing available</p>';

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

    $subject = sprintf(
        '%s (v%.1f) Error Occurred: %s',
        APP_NAME,
        APP_VERSION,
        $title
    );

    $body = sprintf(
        '<body>
            <h1>%s</h1>
            <h3>Host: %s</h3>
            <p>%s</p>
            <br/>
            <div>%s</div>
        </body>',
        $heading,
        APP_HOSTNAME,
        $details,
        $tracer
    );

    try {
        $sent = Mailer::to($recipient)
            ->subject($subject)
            ->body($body)
            ->send();

        if (!$sent) {
            logger('error', "Failed to send error email: {$details}");
        }
    } catch (MailerException $e) {
        logger('exception', sprintf('Mailer Exception: %s', $e->getMessage()));
    } catch (Throwable $e) {
        logger('exception', sprintf('Unhandled mail error: %s', $e->getMessage()));
    }
})($error ?? null);