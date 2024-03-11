<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" type="image/png" href="./favicon.png">
    <title>Error</title>

    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
    </style>
</head>
<body>

    <div class="container text-center">

        <h1 class="headline"><?= $name ?></h1>

        <p class="lead"><?= $message ?></p>

    </div>

</body>

</html>