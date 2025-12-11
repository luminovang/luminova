<?php 
/**
 * Default production (xxx) error page (e.g, `404`, `500` etc..).
 *
 * This file serves as a fallback when no custom 4xx error template exists in:
 *   - /resources/Views/
 *   - /app/Modules/<module>/resources/Views/
 *
 * > It is recommended to create your own template, as modifying this file 
 * > may be overwritten in future updates.
 */
$title ??= 'Unknown Error';
$message ??= '';
$description ??= $title;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="./favicon.png">
    <title><?= htmlspecialchars("{$description} - {$title}", ENT_QUOTES);?></title>
    <style><?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'debug.css')); ?></style>
</head>
<body id="e_all">
    <div class="container text-center main-container">
        <h1 class="headline"><?= htmlspecialchars($description, ENT_QUOTES);?></h1>
        <p class="entry"><?= htmlspecialchars($message, ENT_QUOTES);?></p>
    </div>
    <div class="footer">
        <div class="container">
            <p>
                Displayed at <?= date('H:i:sA'); ?> &mdash;
                PHP: <?= PHP_VERSION ?>  &mdash;
                Environment: <?= ENVIRONMENT; ?>
            </p>
        </div>
    </div>
</body>
</html>