<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Critical Error Occurred</title>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta http-equiv="Cache-Control" content="no-store, max-age=0, no-cache"/>
    <link rel="shortcut icon" type="image/png" href="./favicon.png">
    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'maintenance.css')) ?>
    </style>
</head>
<body>
<article>
    <h1>Critical Error!</h1>
    <div class="container">
        <p>An error is causing application to shutdown.</p>
        <p><?= date('l, F jS, Y - g:i A'); ?></p>
        <p>Current Timezone: <?= date_default_timezone_get(); ?></p>
    </div>
</article>
</body>
</html>