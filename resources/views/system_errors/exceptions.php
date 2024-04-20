<?php 
use \Luminova\Storage\Helper;
use \Luminova\Debugger\Tracer;
$parts = explode(" File:", $exception->getMessage());
?>
<!doctype html>
<html lang="<?= str_replace('_', '-', locale());?>">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" type="image/png" href="<?= href('favicon.png');?>">
    <title><?= escape($title ?? $exception::class) ?></title>
    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?> </style>
    <script>
        function toggle(id) {
            event.preventDefault();
            var element = document.getElementById(id);
            if (element.style.display === "none") {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1><?= escape(($title ?? $exception::class) . ($exception->getCode() ? ' #' . $exception->getCode() : '')); ?></h1>
            <p>
                <?= nl2br(escape($parts[0]??'')) ?>
                <a href="https://www.duckduckgo.com/?q=<?= urlencode(preg_replace('#\'.*\'|".*"#Us', '', $parts[0]??'')) ?>" rel="noreferrer" target="_blank">Search Online &rarr;</a>
            </p>
            <p>File: <?= escape($parts[1]??'');?></p>
        </div>
    </div>

    <?php if (SHOW_DEBUG_BACKTRACE) : ?>
    <div class="container main-container">
        <div class="tab-content">
            <div class="content" id="backtrace">
                <h3>BACKTRACE</h3>
                <ol class="trace">
                <?php foreach ($trace as $index => $row) : ?>
                    <li>
                        <p>
                            <?php if (isset($row['file']) && is_file($row['file'])) : ?>
                                <?php
                                if (isset($row['function']) && in_array($row['function'], ['include', 'include_once', 'require', 'require_once'], true)) {
                                    echo escape($row['function'] . ' ' . trim($row['file']));
                                } else {
                                    echo escape(trim($row['file']) . ' : ' . $row['line']);
                                }
                                ?>
                            <?php else: ?>
                                {PHP internal code}
                            <?php endif; ?>

                            <?php if (isset($row['class'])) : ?>
                                &nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= escape($row['class'] . $row['type'] . $row['function']) ?>
                                <?php if (! empty($row['args'])) : ?>
                                    <?php $argsId = uniqid('error', true) . 'args' . $index ?>
                                    ( <a href="#" onclick="return toggle('<?= escape($argsId, 'attr') ?>');">arguments</a> )
                                    <div style="display:none;" id="<?= escape($argsId, 'attr') ?>">
                                        <table cellspacing="0">

                                        <?php
                                        $params = null;
                                        if (substr($row['function'], -1) !== '}') {
                                            $mirror = isset($row['class']) ? new ReflectionMethod($row['class'], $row['function']) : new ReflectionFunction($row['function']);
                                            $params = $mirror->getParameters();
                                        }

                                        foreach ($row['args'] as $key => $value) : ?>
                                            <tr>
                                                <td><code><?= escape(isset($params[$key]) ? '$' . $params[$key]->name : "#{$key}") ?></code></td>
                                                <td><pre><?= escape(print_r($value, true)) ?></pre></td>
                                            </tr>
                                        <?php endforeach ?>

                                        </table>
                                    </div>
                                <?php else : ?>
                                    ()
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (! isset($row['class']) && isset($row['function'])) : ?>
                                &nbsp;&nbsp;&mdash;&nbsp;&nbsp;    <?= escape($row['function']) ?>()
                            <?php endif; ?>
                        </p>

                        <?php if (isset($row['file']) && is_file($row['file']) && isset($row['class'])) : ?>
                            <div class="source">
                                <?= Tracer::highlight($row['file'], $row['line']) ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ol>
            </div>

            <div class="content" id="server">
                <h3>SERVER</h3>
                <?php foreach (['_SERVER', '_SESSION'] as $var) : ?>
                    <?php
                    if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var])) {
                        continue;
                    } ?>

                    <h3>$<?= escape($var) ?></h3>

                    <table>
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($GLOBALS[$var] as $key => $value) : ?>
                            <tr>
                                <td><?= escape($key) ?></td>
                                <td>
                                    <?php if (is_string($value)) : ?>
                                        <?= escape($value) ?>
                                    <?php else: ?>
                                        <pre><?= escape(print_r($value, true)) ?></pre>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endforeach ?>

       
                <?php $constants = get_defined_constants(true); ?>
                <?php if (! empty($constants['user'])) : ?>
                    <h3>CONSTANTS</h3>

                    <table>
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($constants['user'] as $key => $value) : ?>
                            <tr>
                                <td><?= escape($key) ?></td>
                                <td>
                                    <?php if (is_string($value)) : ?>
                                        <?= escape($value) ?>
                                    <?php elseif(is_bool($value)): ?>
                                        <?= $value === true ? 'true' : 'false';?>
                                    <?php elseif(is_int($value)): ?>
                                        <?= $value;?>
                                    <?php else: ?>
                                        <pre><?= escape(print_r($value, true)) ?></pre>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="content" id="request">
                <h3>REQUEST</h3>
                <?php $request = new \Luminova\Http\Request(); ?>
                <table>
                    <tbody>
                        <tr>
                            <td style="width: 10em">URI</td>
                            <td><?= escape($request->getUri()) ?></td>
                        </tr>
                        <tr>
                            <td>HTTP Method</td>
                            <td><?= escape(strtoupper($request->getMethod())) ?></td>
                        </tr>
                        <tr>
                            <td>IP Address</td>
                            <td><?= escape(ip_address()) ?></td>
                        </tr>
                        <tr>
                            <td style="width: 10em">Is AJAX Request?</td>
                            <td><?= $request->isAJAX() ? 'yes' : 'no' ?></td>
                        </tr>
                        <tr>
                            <td>Is CLI Request?</td>
                            <td><?= is_command() ? 'yes' : 'no' ?></td>
                        </tr>
                        <tr>
                            <td>Is Secure Request?</td>
                            <td><?= $request->isSecure() ? 'yes' : 'no' ?></td>
                        </tr>
                        <tr>
                            <td>User Agent</td>
                            <td><?= escape($request->getUserAgent()) ?></td>
                        </tr>

                    </tbody>
                </table>


                <?php $empty = true; ?>
                <?php foreach (['_GET', '_POST', '_COOKIE'] as $var) : ?>
                    <?php
                    if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var])) {
                        continue;
                    } ?>

                    <?php $empty = false; ?>

                    <h3>$<?= escape($var) ?></h3>

                    <table style="width: 100%">
                        <thead>
                            <tr>
                                <th width="25%">Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($GLOBALS[$var] as $key => $value) : ?>
                            <tr>
                                <td><?= escape($key) ?></td>
                                <td>
                                    <?php if (is_string($value)) : ?>
                                        <?= escape($value) ?>
                                    <?php else: ?>
                                        <pre><?= escape(print_r($value, true)) ?></pre>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endforeach ?>

                <?php if ($empty) : ?>

                    <div class="alert">
                        No $_GET, $_POST, or $_COOKIE Information to show.
                    </div>

                <?php endif; ?>

                <?php $headers = $request->getHeaders(); ?>
                <?php if (! empty($headers)) : ?>

                    <h3>Headers</h3>

                    <table>
                        <thead>
                            <tr>
                                <th width="25%">Header</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($headers as $name => $value) : ?>
                            <tr>
                                <td><?= escape($name, 'html') ?></td>
                                <td><?= escape($value, 'html') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>
            </div>

       
            <div class="content" id="files">
                <h3>FILES</h3>
                <?php $files = get_included_files(); ?>

                <ol>
                <?php foreach ($files as $file) :?>
                    <li><?= escape(trim($file)) ?></li>
                <?php endforeach ?>
                </ol>
            </div>

            <div class="content" id="memory">
                <h3>MEMORY</h3>
                <table>
                    <tbody>
                        <tr>
                            <td>Memory Usage</td>
                            <td><?= escape(Helper::byteToUnit(memory_get_usage(true), true)) ?></td>
                        </tr>
                        <tr>
                            <td style="width: 12em">Peak Memory Usage:</td>
                            <td><?= escape(Helper::byteToUnit(memory_get_peak_usage(true), true)) ?></td>
                        </tr>
                        <tr>
                            <td>Memory Limit:</td>
                            <td><?= escape(ini_get('memory_limit')) ?></td>
                        </tr>
                    </tbody>
                </table>

            </div>

        </div> 
    </div>
    <?php endif; ?>

    <div class="footer">
        <div class="container">

            <p>
                Displayed at <?= date('H:i:sA') ?> &mdash;
                PHP: <?= PHP_VERSION ?>  &mdash;
                Luminova: <?= \Luminova\Application\Foundation::VERSION ?> &mdash;
                Environment: <?= ENVIRONMENT ?>
            </p>

        </div>
    </div>
</body>
</html>