<?php 
use \Luminova\Debugger\Tracer;

function getDebugTracing(array $trace): void {
?>
<h3>BACKTRACE</h3>
<ol class="trace">
    <?php foreach($trace as $index => $row): ?>
        <?php if(isset($row['function']) && $row['function'] === 'display'): ?>
            <?php if(isset($row['args']) && is_file($row['args'][0]->getFile())): ?>
            <li>
                <p><?= htmlspecialchars($row['args'][0]->getFile() . ' : ' . $row['args'][0]->getLine(), ENT_QUOTES);?></p>
                <div class="source">
                    <?= Tracer::highlight($row['args'][0]->getFile(), $row['args'][0]->getLine());?>
                </div>
            </li>
            <?php endif; ?>
        <?php elseif(isset($row['file']) && is_file($row['file']) && isset($row['class'])): ?>
            <li>
                <p>
                    <?php if (isset($row['file']) && is_file($row['file'])) : ?>
                        <?php if (isset($row['function']) && in_array($row['function'], ['include', 'include_once', 'require', 'require_once'], true)): ?>
                            <?= htmlspecialchars($row['function'] . ' ' . trim($row['file']), ENT_QUOTES);?>
                        <?php else: ?>
                            <?= htmlspecialchars(trim($row['file']) . ' : ' . $row['line'], ENT_QUOTES);?>
                        <?php endif;?>
                    <?php else: ?>
                        {PHP internal code}
                    <?php endif; ?>

                    <?php if(isset($row['class'])): ?>
                        &nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= htmlspecialchars($row['class'] . $row['type'] . $row['function'], ENT_QUOTES) ?>
                        <?php if(!empty($row['args'])): ?>
                            <?php $argsId = uniqid('error', true) . 'args' . $index ?>
                            ( <a href="#" onclick="return toggle('<?= htmlspecialchars($argsId, ENT_QUOTES) ?>');">arguments</a> )
                            <div style="display:none;" id="<?= htmlspecialchars($argsId, ENT_QUOTES) ?>">
                                <table cellspacing="0">

                                <?php
                                    $params = null;
                                    if (!str_ends_with($row['function'], '}')) {
                                        $params = (isset($row['class']) 
                                            ? new ReflectionMethod($row['class'], $row['function']) 
                                            : new ReflectionFunction($row['function']))->getParameters();
                                    }
                                ?>

                                <?php foreach($row['args'] as $key => $value): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars(isset($params[$key]) ? '$' . $params[$key]->name : "#{$key}", ENT_QUOTES) ?></code></td>
                                        <td><pre><?= print_r($value, true) ?></pre></td>
                                    </tr>
                                <?php endforeach ?>

                                </table>
                            </div>
                        <?php else: ?>
                            ()
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if(!isset($row['class']) && isset($row['function'])):?>
                        &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?= htmlspecialchars($row['function'] ?? 'NULL', ENT_QUOTES) ?>()
                    <?php endif; ?>
                </p>
                <div class="source">
                    <?= Tracer::highlight($row['file'], $row['line']);?>
                </div>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ol>
<?php }