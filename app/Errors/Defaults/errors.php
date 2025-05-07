<?php 
$lines = explode(PHP_EOL, $stack->getMessage());
$messages = explode(' called in ', $stack->getMessage());
$message = $stack->getFilteredMessage();
$searchable = urlencode($message . ' PHP Luminova Framework');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="./favicon.png">
    <title>Error Occurred - <?= htmlspecialchars($stack->getName(), ENT_QUOTES); ?></title>
    <style><?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__  . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'debug.css')) ?></style>
    <script>function toggle(id){ event.preventDefault(); var element=document.getElementById(id); if (element.style.display==="none"){ element.style.display="block";} else{ element.style.display="none";}} </script>
</head>
<body id="e_all">
    <div class="container text-center main-container">
        <h1 class="headline"><?= htmlspecialchars($stack->getName(), ENT_QUOTES); ?> #<?= $stack->getCode(); ?></h1>
        <?php if (defined('PRODUCTION') && !PRODUCTION): ?>
            <div class="error-details">
                <h2>Error Details:</h2>
                <p class="entry"><?= htmlspecialchars($message, ENT_QUOTES); ?>. Thrown in: <?= htmlspecialchars(filter_paths($stack->getFile()), ENT_QUOTES); ?>, Line: <?= $stack->getLine(); ?></p>
                <?php if(isset($lines[2]) || isset($messages[1])): ?>
                    <p class="entry text-warning">Caller: <?= htmlspecialchars($lines[2] ?? $messages[1] ?? '', ENT_QUOTES); ?></p>
                <?php endif;?>
                <button class="button" type="button" onclick="return toggle('stack-tracer');">Stack tracer &#128269;</button>
                <a class="button" href="https://www.duckduckgo.com/?q=<?= $searchable; ?>" rel="noreferrer" target="_blank">Search Online &rarr;</a>
                <a class="button" href="https://luminova.ng/forum/search?q=<?= $searchable;?>" rel="noreferrer" target="_blank">Search Forum &#128270;</a>
                <a class="button" href="https://github.com/luminovang/luminova/issues/new?labels=bug&title=<?= $searchable;?>" rel="noreferrer" target="_blank">Open Issue &#128030;</a>
                <div id="stack-tracer" style="display:none;">
                    <?php 
                        if (SHOW_DEBUG_BACKTRACE) : 
                            include_once __DIR__ . DIRECTORY_SEPARATOR . 'tracer.php';
                            onErrorShowDebugTracer($stack->getBacktrace(), array_slice($lines, 3));
                        endif;
                    ?>
                </div>
            </div>
        <?php else: ?>
            <h2>Origin is unreachable</h2>
            <p class="entry" style="margin-bottom: 20px;">An error is preventing website from loading properly.</p>
            <p class="entry" style="margin-bottom: 20px;">If you are the owner of this website, please check the error log for more information.</p>
        <?php endif; ?>
    </div>
    <div class="footer">
        <div class="container">
            <p>
                Displayed at <?= date('H:i:sA'); ?> &mdash;
                PHP: <?= PHP_VERSION; ?> &mdash;
                Luminova: <?= defined('\Luminova\Luminova::VERSION') ? \Luminova\Luminova::VERSION : '1.0.0'; ?> &mdash;
                Environment: <?= defined('ENVIRONMENT') ? ENVIRONMENT : 'Unknown'; ?>
            </p>
        </div>
    </div>
</body>
</html>