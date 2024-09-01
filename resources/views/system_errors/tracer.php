<?php 
use \Luminova\Functions\Maths;
use \Luminova\Debugger\Tracer;
use \Luminova\Http\Request;

function onErrorShowDebugTracer(array $trace, ?array $timelines = null): void{
?>
<div class="tracer-container container main-container">
    <div class="tab-content">
        <div class="trace-tab-buttons">
            <ul>
                <li class="trace-trigger active" data-target="#backtrace">
                    <span>BACKTRACE</span>
                </li>
                <?php if($timelines): ?>
                    <li class="trace-trigger" data-target="#timeline">
                        <span>TIMELINE</span>
                    </li>
                <?php endif;?>
                <li class="trace-trigger" data-target="#server">
                    <span>SERVER</span>
                </li>
                <li class="trace-trigger" data-target="#request">
                    <span>REQUEST</span>
                </li>
                <li class="trace-trigger" data-target="#files">
                    <span>FILES</span>
                </li>
                <li class="trace-trigger" data-target="#memory">
                    <span>memory</span>
                </li>
            </ul>
        </div>
        <div class="trace-tab-contents">

            <div class="content active" id="backtrace">
                <h3>BACKTRACE</h3>
                <ol class="trace">
                    <?php foreach ($trace as $index => $row) : ?>
                        <?php if(isset($row['function']) && $row['function'] === 'display'): ?>
                            <?php if(isset($row['args']) && is_file($row['args'][0]->getFile())): ?>
                            <li>
                                <p><?= htmlspecialchars($row['args'][0]->getFile() . ' : ' . $row['args'][0]->getLine(), ENT_QUOTES);?></p>
                                <div class="source">
                                    <?= Tracer::highlight($row['args'][0]->getFile(), $row['args'][0]->getLine());?>
                                </div>
                            </li>
                            <?php endif; ?>
                        <?php elseif (isset($row['file']) && is_file($row['file']) && isset($row['class'])) : ?>
                            <li>
                                <p>
                                    <?php if (isset($row['file']) && is_file($row['file'])) : ?>
                                        <?php
                                        if (isset($row['function']) && in_array($row['function'], ['include', 'include_once', 'require', 'require_once'], true)) {
                                            echo htmlspecialchars($row['function'] . ' ' . trim($row['file']), ENT_QUOTES);
                                        } else {
                                            echo htmlspecialchars(trim($row['file']) . ' : ' . $row['line'], ENT_QUOTES);
                                        }
                                        ?>
                                    <?php else: ?>
                                        {PHP internal code}
                                    <?php endif; ?>

                                    <?php if (isset($row['class'])) : ?>
                                        &nbsp;&nbsp;&mdash;&nbsp;&nbsp;<?= htmlspecialchars($row['class'] . $row['type'] . $row['function'], ENT_QUOTES) ?>
                                        <?php if (! empty($row['args'])) : ?>
                                            <?php $argsId = uniqid('error', true) . 'args' . $index ?>
                                            ( <a href="#" onclick="return toggle('<?= htmlspecialchars($argsId, ENT_QUOTES) ?>');">arguments</a> )
                                            <div style="display:none;" id="<?= htmlspecialchars($argsId, ENT_QUOTES) ?>">
                                                <table cellspacing="0">

                                                <?php
                                                $params = null;
                                                if (!str_ends_with($row['function'], '}')) {
                                                    $func = isset($row['class']) ? new ReflectionMethod($row['class'], $row['function']) : new ReflectionFunction($row['function']);
                                                    $params = $func->getParameters();
                                                }

                                                foreach ($row['args'] as $key => $value) : ?>
                                                    <tr>
                                                        <td><code><?= htmlspecialchars(isset($params[$key]) ? '$' . $params[$key]->name : "#{$key}", ENT_QUOTES) ?></code></td>
                                                        <td><pre><?= print_r($value, true) ?></pre></td>
                                                    </tr>
                                                <?php endforeach ?>

                                                </table>
                                            </div>
                                        <?php else : ?>
                                            ()
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if(!isset($row['class']) && isset($row['function'])):?>
                                        &nbsp;&nbsp;&mdash;&nbsp;&nbsp; <?= htmlspecialchars($row['function']??'NULL', ENT_QUOTES) ?>()
                                    <?php endif; ?>
                                </p>
                                <div class="source">
                                    <?= Tracer::highlight($row['file'], $row['line']);?>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>

            <div class="content" id="timeline">
                <?php foreach($timelines as $tl):
                    if(str_ends_with($tl, 'thrown')){
                        continue;
                    }
                ?>
                    <p class="entry text-timeline"><?= htmlspecialchars($tl, ENT_QUOTES); ?></p>
                <?php endforeach; ?>
            </div>

            <div class="content" id="server">
                <h3>SERVER</h3>
                <?php foreach (['_SERVER', '_SESSION'] as $var) : ?>
                    <?php
                        if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var])) {
                            continue;
                        } 
                    ?>

                    <h3>$<?= htmlspecialchars($var ?? 'NULL', ENT_QUOTES) ?></h3>

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
                                <td><?= htmlspecialchars($key ?? 'NULL', ENT_QUOTES) ?></td>
                                <td>
                                    <?php if (is_string($value)) : ?>
                                        <?= htmlspecialchars($value, ENT_QUOTES) ?>
                                    <?php else: ?>
                                        <pre><?= print_r($value, true) ?></pre>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endforeach ?>

        
                <?php $constants = get_defined_constants(true); ?>
                <?php if (!empty($constants['user'])) : ?>
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
                                <td><?= htmlspecialchars((string) $key, ENT_QUOTES) ?></td>
                                <td>
                                    <?php if (is_string($value)) : ?>
                                        <?= htmlspecialchars($value, ENT_QUOTES) ?>
                                    <?php elseif(is_bool($value)): ?>
                                        <?= $value ? 'true' : 'false';?>
                                    <?php elseif(is_int($value)): ?>
                                        <?= $value;?>
                                    <?php else: ?>
                                        <pre><?= print_r($value, true) ?></pre>
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
                <?php $request = new Request(); ?>
                <table>
                    <tbody>
                        <tr>
                            <td style="width: 10em">URI</td>
                            <td><?= htmlspecialchars($request->getUri(), ENT_QUOTES) ?></td>
                        </tr>
                        <tr>
                            <td>HTTP Method</td>
                            <td><?= htmlspecialchars($request->getMethod(), ENT_QUOTES) ?></td>
                        </tr>
                        <tr>
                            <td>IP Address</td>
                            <td><?= htmlspecialchars(ip_address(), ENT_QUOTES) ?></td>
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
                            <td><?= htmlspecialchars($request->getUserAgent()->toString(), ENT_QUOTES) ?></td>
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

                    <h3>$<?= htmlspecialchars($var ?? 'NULL', ENT_QUOTES) ?></h3>

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
                                <td><?= htmlspecialchars($key ?? 'NULL', ENT_QUOTES) ?></td>
                                <td>
                                    <?php if (is_string($value)) : ?>
                                        <?= htmlspecialchars($value, ENT_QUOTES) ?>
                                    <?php else: ?>
                                        <pre><?= print_r($value, true) ?></pre>
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
                                <td><?= htmlspecialchars($name ?? 'NULL', ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($value ?? 'NULL', ENT_QUOTES) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>
            </div>
        
            <div class="content" id="files">
                <h3>FILES</h3>
                <ul style="margin: 0;padding: 0;">
                    <?php 
                        foreach ( get_included_files() as $index => $file){
                            $filtered = filter_paths($file);
                            $category = 'Others';
                            $color = '#eee';
                
                            if (str_starts_with($filtered, 'system')) {
                                if (str_starts_with($filtered, 'system/plugins')) {
                                    $category = 'ThirdParty';
                                    $color = '#d99a06';
                                } else {
                                    $category = 'Module';
                                    $color = '#04ac17';
                                }
                            }
                
                            $bgColor = ($index % 2 === 0) ? '#282727' : 'transparent';
                            echo "<li style='list-style: none; background-color: {$bgColor}; padding: .5rem;'>"
                                . "[<span style='color: {$color}'>{$category}</span>] "
                                . "<a style='color:#44a1f2' href='vscode://file{$file}'>/{$file}</a>"
                                . "</li>";
                        
                        }
                    ?>
                </ul>
            </div>

            <div class="content" id="memory">
                <h3>MEMORY</h3>
                <table>
                    <tbody>
                        <tr>
                            <td>Memory Usage</td>
                            <td><?= htmlspecialchars(Maths::toUnit(memory_get_usage(true), true), ENT_QUOTES) ?></td>
                        </tr>
                        <tr>
                            <td style="width: 12em">Peak Memory Usage:</td>
                            <td><?= htmlspecialchars(Maths::toUnit(memory_get_peak_usage(true), true), ENT_QUOTES) ?></td>
                        </tr>
                        <tr>
                            <td>Memory Limit:</td>
                            <td><?= htmlspecialchars((string) ini_get('memory_limit'), ENT_QUOTES) ?></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div> 
</div>
<script>
document.querySelectorAll('.trace-trigger').forEach(function(trigger) {
    trigger.addEventListener('click', function() {
        document.querySelectorAll('.trace-trigger').forEach(function(t) {
            t.classList.remove('active');
        });
        document.querySelectorAll('.trace-tab-contents .content').forEach(function(content) {
            content.classList.remove('active');
        });

        this.classList.add('active');
        const target = document.querySelector(this.getAttribute('data-target'));
        if (target) {
            target.classList.add('active');
        }
    });
});
</script>
<?php }