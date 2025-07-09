<?php 
use \Luminova\Common\Maths;
use \Luminova\Debugger\Performance;
use \Luminova\Http\Request;
use function \Luminova\Funcs\{ip_address, is_command, shared};
include_once __DIR__ . '/tracing.php';

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
                <?= getDebugTracing($trace); ?>
            </div>

            <?php if($timelines): ?>
                <div class="content" id="timeline">
                    <?php foreach($timelines as $tl): ?>
                        <?php if(str_ends_with($tl, 'thrown')){continue;}?>
                        <p class="entry text-timeline"><?= htmlspecialchars($tl, ENT_QUOTES); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif;?>

            <div class="content" id="server">
                <h3>SERVER</h3>
                <?php foreach(['_SERVER', '_SESSION'] as $var): ?>
                    <?php if(empty($GLOBALS[$var]) || !is_array($GLOBALS[$var])) {continue;} ?>

                    <h3>$<?= htmlspecialchars($var ?? 'NULL', ENT_QUOTES) ?></h3>

                    <table>
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($GLOBALS[$var] as $key => $value): ?>
                            <tr>
                                <td><?= htmlspecialchars($key ?? 'NULL', ENT_QUOTES) ?></td>
                                <td>
                                    <?php if(is_string($value)): ?>
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

                <?php if(($constants = get_defined_constants(true)) !== []): ?>
                    <h3>CONSTANTS</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($constants['user'] as $key => $value): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $key, ENT_QUOTES) ?></td>
                                <td>
                                    <?php if(is_string($value)): ?>
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
                <?php $request = Request::getInstance(); ?>
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
                            <td>Is CLI Command?</td>
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
                <?php foreach(['_GET', '_POST', '_COOKIE'] as $var):?>
                    <?php if(empty($GLOBALS[$var]) || !is_array($GLOBALS[$var])) { continue;} ?>

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
                        <?php foreach($GLOBALS[$var] as $key => $value) : ?>
                            <tr>
                                <td><?= htmlspecialchars($key ?? 'NULL', ENT_QUOTES) ?></td>
                                <td>
                                    <?php if(is_string($value)) : ?>
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

                <?php if($empty): ?>
                    <div class="alert">
                        No $_GET, $_POST, or $_COOKIE Information to show.
                    </div>
                <?php endif; ?>

                <?php if(($headers = $request->getHeaders()) !== []): ?>
                    <h3>Headers</h3>

                    <table>
                        <thead>
                            <tr>
                                <th width="25%">Header</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($headers as $name => $value): ?>
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
                <ul style="list-style-type: none; padding: 0; margin: 0;">
                    <?= Performance::fileInfo()[1];?>
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
                        <?php if(defined('IS_UP') && ($dbTime = shared('__DB_QUERY_EXECUTION_TIME__', default: 0)) > 0): ?>
                        <tr>
                            <td>Last Database Executions:</td>
                            <td><?= htmlspecialchars(($dbTime < 1) ? sprintf('%.2f ms', $dbTime * 1000) : sprintf('%.4f s', $dbTime), ENT_QUOTES) ?></td>
                        </tr>
                        <?php endif;?>
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