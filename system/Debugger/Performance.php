<?php
/**
 * Performance class for measuring and displaying performance metrics.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace Luminova\Debugger; 

use \Luminova\Application\Foundation;
use \Luminova\Http\Request;

final class Performance
{
    /**
     * Start time of the performance measurement.
     * 
     * @var float $startTime 
     */
    private static float $startTime;

    /**
     * Start memory usage of the performance measurement.
     * 
     * @var int $startMemory 
     */
    private static int $startMemory;

    /**
     * End time of the performance measurement.
     * 
     * @var float $endTime 
     */
    private static float $endTime;

    /**
     * End memory usage of the performance measurement.
     * 
     * @var int $endMemory
     */
    private static int $endMemory;

    /**
     * HTTP Request instance.
     * 
     * @var Request|null $request
     */
    private static ?Request $request = null;

    /**
     * Start measuring time and memory usage.
     *
     * @return void
     */
    public static function start(): void
    {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
    }

    /**
     * Stop measuring time and memory usage, and display performance metrics.
     * 
     * @param string|null $style Optional CSS style rules for the profiling container.
     * 
     * @return void
     */
    public static function stop(?string $style = null): void
    {
        self::$endTime = microtime(true);
        self::$endMemory = memory_get_usage();
        self::$request ??= new Request();
        $style ??= 'position: fixed;bottom: 0;z-index: 9000;width: 100%;max-height: 400px;background-color: #000;color: #0d930d;padding: .5rem 1rem;margin: 0;left: 0;right: 0; box-sizing: border-box;overflow: auto;';
        $script = <<<JS
        <script>
        function lmvToggleProfilingDetails() {
            var details = document.getElementById('lmv-debug-details');
            var button = document.getElementById('lmv-toggle-button');
            if (details.style.display === 'none') {
                details.style.display = 'block';
                button.textContent = 'Hide Details';
            } else {
                details.style.display = 'none';
                button.textContent = 'Show Details';
            }
        }
        </script>
        JS;
        
        echo $script;
        echo '<div id="lmv-debug-container" style="' . $style . '">';
        echo '<div id="lmv-header-details" style="height: 35px;display: flex;line-height: 30px;font-weight: bold;">';
        echo '<p style="display: inline-block;margin: 0;">' . self::metrics() . '</p>';
        echo '<button type="button" id="lmv-toggle-button" onclick="lmvToggleProfilingDetails()" style="line-height: 30px;font-size: 15px;border-radius: 8px;position: absolute;right: 1rem;background-color: #e9e9ed;color: #000;border: 1px solid #201f1f;cursor: pointer;">Show Details</button>';
        echo '</div><div id="lmv-debug-details" style="display: none; padding-top: 10px; color: #fff;height: 300px;overflow:auto;">';
        echo '<table style="width:100%;margin-bottom:1rem"><tbody>';
        echo '<tr><td><strong>Framework:</strong></td><td>' . Foundation::copyright() . '</td></tr>';
        echo '<tr><td><strong>PHP Version:</strong></td><td>' . PHP_VERSION . '</td></tr>';
        echo '<tr><td><strong>IP Address:</strong></td><td>' . self::esc(ip_address()) . '</td></tr>';
        echo '<tr><td><strong>Environment:</strong></td><td>' . ENVIRONMENT . '</td></tr>';
        echo '<tr><td><strong>Project Id:</strong></td><td>' . PROJECT_ID . '</td></tr>';
        echo '<tr><td><strong>Server Software:</strong></td><td>' . self::esc($_SERVER['SERVER_SOFTWARE'] ?? 'Not Set') . '</td></tr>';
        echo '<tr><td><strong>UserAgent:</strong></td><td>' . self::esc(self::$request->getUserAgent()->toString()) . '</td></tr>';
        echo '<tr><td><strong>Request Method:</strong></td><td>' . self::esc(self::$request->getMethod()) . '</td></tr>';
        echo '<tr><td><strong>Request URL:</strong></td><td>' . self::esc(self::$request->getUri()) . '</td></tr>';
        echo '<tr><td><strong>Request Origin:</strong></td><td>' . self::esc(self::$request->getOrigin()) . '</td></tr>';
        echo '<tr><td><strong>Request Referrer:</strong></td><td>' . self::esc(self::$request->getUserAgent()->getReferrer()) . '</td></tr>';
        echo '</tbody></table>';
        echo self::whatIncluded(true);
        echo '</div></div>';
    }

    /**
     * Calculate and return the performance metrics.
     *
     * @return string Formatted performance metrics including execution time, memory usage, and number of files loaded.
     */
    public static function metrics(): string
    {
        $execution = self::$endTime - self::$startTime;
        $format = ($execution < 1) ? '%.2f milliseconds' : '%.4f seconds';
        $time = ($execution < 1) ? $execution * 1000 : $execution;

        $files = get_included_files();
        $span = '<span style="color:#eecfcf;margin: 0 1rem;">|</span>';
        return sprintf(
            "Execution Time: {$format} {$span} Memory Usage: %.2f KB {$span} Files Loaded: %d",
            $time,
            (self::$endMemory - self::$startMemory) / 1024,
            count($files)
        );
    }

    /**
     * Display a list of included files.
     *
     * @param bool $forProfiling Whether to indicate profiling context (default: false).
     *
     * @return void
     */
    public static function whatIncluded(bool $forProfiling = false): void
    {
        $files = get_included_files();
        $ide = env('debug.coding.ide', 'vscode');
        $scheme = match ($ide) {
            'phpstorm' => 'phpstorm://open?url=file:',
            'sublime' => 'sublimetext://open?url=file:',
            'vscode' => 'vscode://file',
            default => "{$ide}://file",
        };

        echo "<h2>Included Files:</h2>";

        if ($forProfiling) {
            echo '<p style="background-color: #932727; color: #fff; padding: .5rem; border-radius: 6px;">'
            . '<strong>Note:</strong> Some modules like (<code>IP, UserAgent, Browser</code>) may not be included in the total count as they are used for profiling only.'
            . '</p>';
        }

        $modules = $plugins = $others = 0;
        $html = '';
        
        foreach ($files as $index => $file) {
            $filtered = filter_paths($file);
            $type = 'Others';
            $color = '#eee';

            if (str_starts_with($filtered, 'system')) {
                if (str_starts_with($filtered, 'system/plugins')) {
                    $plugins++;
                    $type = 'ThirdParty';
                    $color = '#d99a06';
                } else {
                    $modules++;
                    $type = 'Module';
                    $color = '#04ac17';
                }
            } else {
                $others++;
            }

            $bgColor = ($index % 2 === 0) ? '#282727' : '#111010';
            $html .= "<li style='background-color: {$bgColor}; padding: .5rem;'>"
                . "[<span style='color: {$color}'>{$type}</span>] "
                . "<a style='color:#44a1f2' href='{$scheme}{$file}'>/{$filtered}</a>"
                . "</li>";
        }

        // Output counters
        echo '<table style="width:100%; margin-bottom:1rem;">'
        . '<tbody>'
        . "<tr><td><strong>Total Framework Modules:</strong></td><td style='text-align:center;'>[<span style='color:#04ac17'>{$modules}</span>]</td></tr>"
        . "<tr><td><strong>Total Third Party Modules:</strong></td><td style='text-align:center;'>[<span style='color:#d99a06'>{$plugins}</span>]</td></tr>"
        . "<tr><td><strong>Other Modules Including Controllers:</strong></td><td style='text-align:center;'>[<span style='color:#eee'>{$others}</span>]</td></tr>"
        . '</tbody></table>';

        // Output list of files
        echo "<ol id='lmv-included-files' style='list-style-type: none; padding: 0; margin: 0;'>{$html}</ol>";
    }

    /**
     * Sef escape characters.
     * 
     * @param mixed $input The input string to be escaped.
     * 
     * @return string The escaped string.
    */
    private static function esc(mixed $input): string 
    {
        if($input === null){
            return '';
        }

        return htmlspecialchars($input);
    }
}