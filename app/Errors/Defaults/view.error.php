<?php 
/**
 * @var \Luminova\Exceptions\AppException<\T>|null $error
 */
use \Luminova\Foundation\Error\Message;
use function \Luminova\Funcs\{href, filter_paths, get_class_name};

$message = $error->getDescription();
$searchable = urlencode($message . ' PHP Luminova Framework');
$eTitle = $error?->getName() ?? get_class_name($error::class);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta http-equiv="Cache-Control" content="no-store, max-age=0, no-cache"/>
    <link rel="shortcut icon" type="image/png" href="<?= href('favicon.png');?>">
    <title>Template Error - <?= htmlspecialchars($eTitle, ENT_QUOTES) ?></title>
    <style> <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'debug.css')) ?> </style>
    <script>function toggle(id){ event.preventDefault(); var element=document.getElementById(id); if (element.style.display==="none"){ element.style.display="block";} else{ element.style.display="none";}} </script>
</head>
<body>
    <div class="header">
        <div class="container mt-4 <?= SHOW_DEBUG_BACKTRACE ?: 'main-container';?>">
            <h1>
                <?= htmlspecialchars($eTitle, ENT_QUOTES); ?> #<?= $error->getCode(); ?>
                <span class="subtitle"><?= $title ? htmlspecialchars("($title)", ENT_QUOTES) : ''; ?></span>
            </h1>
            <p><?= nl2br(Message::prettify(rtrim($message, '.'))) ?>. Thrown in file: <?= filter_paths($error->getFile());?> on line: <?= $error->getLine();?></p>
            <p class="mt-2">
                <a class="button" href="https://www.duckduckgo.com/?q=<?= $searchable; ?>" rel="noreferrer" target="_blank">Search Online &rarr;</a>
                <a class="button" href="https://luminova.ng/forum/search?q=<?= $searchable;?>" rel="noreferrer" target="_blank">Search Forum &#128270;</a>
                <a class="button" href="https://github.com/luminovang/luminova/issues/new?labels=bug&title=<?= $searchable;?>" rel="noreferrer" target="_blank">Open Issue &#128030;</a>
            </p>
        </div>
    </div>

    <?php 
    if (SHOW_DEBUG_BACKTRACE) : 
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'tracer.php';
        onErrorShowDebugTracer($error->getBacktrace());
    endif;
    ?>

    <div class="footer">
        <div class="container">
            <p>
                Displayed at <?= date('H:i:sA') ?> &mdash;
                PHP: <?= PHP_VERSION ?>  &mdash;
                Luminova: <?= \Luminova\Luminova::VERSION ?> &mdash;
                Environment: <?= ENVIRONMENT ?>
            </p>
        </div>
    </div>
</body>
</html>