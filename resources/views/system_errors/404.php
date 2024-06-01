<!doctype html>
<html lang="<?= str_replace('_', '-', locale());?>">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="./favicon.png">
    <title>404 View Error - <?= escape($title ?? '');?></title>
    <style><?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')); ?></style>
</head>
<body id="e_all">
    <div class="container text-center main-container">
        <h1 class="headline">404</h1>
        <p class="lead">Error View Not Found</p>
    </div>
    <div class="footer">
        <div class="container">

            <p>
                Displayed at <?= date('H:i:sA'); ?> &mdash;
                PHP: <?= PHP_VERSION ?>  &mdash;
                Luminova: <?= \Luminova\Application\Foundation::VERSION; ?> &mdash;
                Environment: <?= ENVIRONMENT; ?>
            </p>

        </div>
    </div>
</body>
</html>