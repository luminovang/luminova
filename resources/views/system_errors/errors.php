<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="./favicon.png">
    <title>Error - <?= htmlspecialchars($stack->getName(), ENT_QUOTES); ?></title>
    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
    </style>
</head>
<body id="e_all">
    <div class="container text-center main-container">
        <h1 class="headline"><?= htmlspecialchars($stack->getName(), ENT_QUOTES); ?> (code: <?= $stack->getCode(); ?>)</h1>
        <?php if (defined('PRODUCTION') && !PRODUCTION) : ?>
            <div class="error-details">
                <h2>Error Details:</h2>
                <p class="lead"><?= nl2br(htmlspecialchars($stack->getMessage(), ENT_QUOTES)); ?></p>
                <p class="lead">File: <?= htmlspecialchars($stack->getFile(), ENT_QUOTES); ?>, Line: <?= $stack->getLine(); ?></p>
            </div>
        <?php else: ?>
            <h2>Origin is unreachable</h2>
            <p class="lead" style="margin-bottom: 20px;">An error is preventing your website from loading properly.</p>
            <p class="lead" style="margin-bottom: 20px;">If you are the owner of this website, please check the error log for more information.</p>
        <?php endif; ?>
    </div>
    <div class="footer">
        <div class="container">
            <p>
                Displayed at <?= date('H:i:sA'); ?> &mdash;
                PHP: <?= PHP_VERSION; ?> &mdash;
                Luminova: <?= defined('\Luminova\Application\Foundation::VERSION') ? \Luminova\Application\Foundation::VERSION : '1.0.0'; ?> &mdash;
                Environment: <?= defined('ENVIRONMENT') ? ENVIRONMENT : 'Unknown'; ?>
            </p>
        </div>
    </div>
</body>
</html>