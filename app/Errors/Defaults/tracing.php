<?php 
use \Luminova\Debugger\Tracer;

if (!function_exists('__get_debug_tracing')) {
    function __get_debug_tracing(array $trace, ?string $file = null): void {
        krsort($trace);
?>
    <h3>BACKTRACE</h3>
    <ol class="trace">
        <?php foreach($trace as $index => $row): ?>
            <?php if(isset($row['function']) && $row['function'] === 'display'): ?>
                <?php if(isset($row['args']) && is_file($row['args'][0]->getFile())): ?>
                <li>
                    <p>
                        <?= htmlspecialchars($row['args'][0]->getFile() . ' : ' . $row['args'][0]->getLine(), ENT_QUOTES);?>
                    </p>
                    <div class="source">
                        <?= Tracer::highlight($row['args'][0]->getFile(), $row['args'][0]->getLine());?>
                    </div>
                </li>
                <?php endif; ?>
                <?php continue; ?>
            <?php endif; ?>

             <?php if(isset($row['file'])): ?>
                <?php if(!str_ends_with($row['file'], 'index.php') && $file && !str_ends_with($row['file'], $file)): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <li>
                    <p>
                        <?php if (is_file($row['file'])) : ?>
                            <?php if (isset($row['function']) && in_array($row['function'], ['include', 'include_once', 'require', 'require_once'], true)): ?>
                                <?= htmlspecialchars($row['function'] . ' ' . trim($row['file']), ENT_QUOTES);?>
                            <?php else: ?>
                                <?= htmlspecialchars(trim($row['file']) . ' : ' . $row['line'], ENT_QUOTES);?>
                            <?php endif;?>
                        <?php else: ?>
                            {PHP internal code}
                        <?php endif; ?>

                        <?php if(isset($row['class'])): ?>
                            &nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= htmlspecialchars(
                                $row['class'] . ($row['type'] ?? '') . ($row['function'] ?? ''), 
                                ENT_QUOTES
                            ); 
                            ?>
                            <?php if(!empty($row['args'])): ?>
                                <?php 
                                $argsId = uniqid("error-{$index}-", true);
                                $params = [];

                                if (!str_ends_with($row['function'] ?? '', '}')) {
                                    $params = (new ReflectionMethod($row['class'], $row['function'] ?? null))  ->getParameters();
                                }
                                ?>

                                ( <a href="#" onclick="return toggle('<?= $argsId ?>');">arguments</a> )
                                <div style="display:none;" id="<?= $argsId ?>">
                                    <table cellspacing="0">
                                    <?php foreach($row['args'] as $key => $value): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars(isset($params[$key]) 
                                                ? '$' . $params[$key]->name 
                                                : "#{$key}", 
                                                ENT_QUOTES); ?></code></td>
                                            <td><pre><?= print_r($value, true) ?></pre></td>
                                        </tr>
                                    <?php endforeach ?>
                                    </table>
                                </div>
                            <?php else: ?>
                                ()
                            <?php endif; ?>
                        <?php elseif(isset($row['function'])): ?>
                            &nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= htmlspecialchars(
                                $row['function'],
                                ENT_QUOTES
                            ); ?>()
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
} ?>