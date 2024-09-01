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
use \Luminova\Command\Terminal;
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
     * CLI helper instance.
     * 
     * @var Terminal|null $terminal
     */
    private static ?Terminal $terminal = null;

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
     * @param array|null $context Additional information to pass to CLI profiling (default: null).
     * 
     * @return void
     */
    public static function stop(?string $style = null, ?array $context = null): void
    {
        self::$endTime = microtime(true);
        self::$endMemory = memory_get_usage();

        if(Foundation::isCommand()){
            self::showCommandPerformanceMetrics($context);
            return;
        }

        self::$request ??= new Request();
        $info = [
            'Framework' => Foundation::copyright(),
            'PHP Version' => PHP_VERSION,
            'IP Address' => self::esc(ip_address()),
            'Environment' => ENVIRONMENT,
            'Project Id' => PROJECT_ID,
            'Server Software' => self::esc($_SERVER['SERVER_SOFTWARE'] ?? 'Not Set'),
            'UserAgent' => self::esc(self::$request->getUserAgent()->toString()),
            'Request Method' => self::esc(self::$request->getMethod()),
            'Request URL' => self::esc(self::$request->getUri()),
            'Request Origin' => self::esc(self::$request->getOrigin()),
            'Request Referrer' => self::esc(self::$request->getUserAgent()->getReferrer()),
            'Is Secure Request' => (self::$request->isSecure() ? 'YES' : 'NO'),
            'Is AJAX' => (self::$request->isAJAX() ? 'YES' : 'NO')
        ];

        if(Foundation::isApiContext()){
            self::logApiPerformanceMetrics($info);
            return;
        }

        self::showWebPerformanceMetrics($style, $info);
    }

    /**
     * Log performance metrics when making API calls.
     * 
     * @param array<string,mixed> $info The performance basic information.
     * @return void
    */
    private static function logApiPerformanceMetrics(array $info): void 
    {
        $metrics = self::metrics(false);
        $logData = [
            'metrics' => $metrics,
            'info' => $info,
            'included_files_summary' => [],
            'included_files' => []
        ];

        // Categorize included files and prepare data for logging
        [$categories, $files] = self::fileInfo('api');
       
        // Log the summary of included files by category
        $logData['included_files_summary'] = [
            'Total Framework Modules' => $categories['Module'],
            'Total Third Party Modules' => $categories['ThirdParty'],
            'Other Modules Including Controllers' => $categories['Others']
        ];

        $logData['included_files'] = $files;

        // Log the complete data
        @logger('metrics', json_encode($logData, JSON_PRETTY_PRINT));
    }

    /**
     * Display performance metrics when making CLI Command request.
     * 
     * @param array|null $context The additional command information passed by router.
     * 
     * @return void
    */
    private static function showCommandPerformanceMetrics(?array $context = null): void 
    {
       
        self::$terminal ??= new Terminal();
        self::$terminal->newLine();
        self::$terminal->writeln('Command Performance Profiling');
        self::$terminal->writeln(self::metrics(false), 'green');

        if (self::$terminal->prompt('Show More Details?', ['yes', 'no']) !== 'yes') {
            return;
        }

        // Display basic system information
        $info = [
            'Framework' => Foundation::copyright(),
            'NovaKit Version' => Foundation::NOVAKIT_VERSION,
            'PHP Version' => PHP_VERSION,
            'Environment' => ENVIRONMENT,
            'Project Id' => PROJECT_ID,
            'Server Software' => self::esc($_SERVER['SERVER_SOFTWARE'] ?? 'Not Set'),
            'Method' => 'CLI',
            'Group' => $context['commands']['group'] ?? 'NULL',
            'Command' => $context['commands']['command'] ?? 'NULL',
            'Executed' => $context['commands']['exe_string'] ?? 'No command executed'
        ];

        self::$terminal->table(
            ['Variable', 'Values'], 
            array_map(function($key, $value) {
                return [
                    'Variable' => $key,
                    'Values' => $value
                ];
            }, array_keys($info), $info)
        );

        if (!empty($context['commands']['options'])) {
            self::$terminal->writeln('Command Arguments:');
        
            // Map the arguments to table
            self::$terminal->table(
                ['Name', 'Value'], 
                array_map(function($key, $value) {
                    return [
                        'Name' => $key,
                        'Value' => (is_array($value) ? json_encode($value) : $value ?? 'NULL')
                    ];
                }, array_keys($context['commands']['options']), $context['commands']['options'])
            );
        }

        if (!empty($context['commands']['arguments'])) {
            self::$terminal->writeln('Command Parameters:');
      
            $rows = [];
            $arguments = $context['commands']['arguments'];
            $totalArgs = count($arguments);

            for ($index = 0; $index < $totalArgs; $index++) {
                $arg = $arguments[$index];

                if (str_contains($arg, '=')) {
                    [$key, $value] = explode('=', $arg, 2);
                } else {
                    // Process for key-next value when command param is executed `foo bar` instead of `foo=bar`
                    $key = $arg;
                    $value = $arguments[$index + 1] ?? 'NULL';
                    $index++; // Skip the next argument as it has been processed as value
                }

                $rows[] = [
                    'Param' => $key,
                    'Value' => $value ?? 'NULL'
                ];
            }

            self::$terminal->table(
                ['Param', 'Value'], 
                $rows
            );
        }

        // Display included files and categorize them
        self::$terminal->writeln('Included Files Summary:');
        [$categories, $files] = self::fileInfo('cli');
   
        // Display the summary of included files by category
        self::$terminal->table(['Origination', 'Total'], [
            [
                'Origination' => 'Total Framework Modules', 
                'Total' => $categories['Module']
            ],
            [
                'Origination' => 'Total Third Party Modules', 
                'Total' => $categories['ThirdParty']
            ],
            [
                'Origination' => 'Other Modules Including Controllers', 
                'Total' => $categories['Others']
            ],
        ]);

        self::$terminal->writeln('Included Files:');
        // Display detailed list of included files
        self::$terminal->table(['Category', 'File'], $files);
    }

    /**
     * Display performance metrics for a web request.
     *
     * @param string|null $style Optional styling for metric details.
     * @param array<string,mixed> $info The performance basic information.
     *
     * @return void
     */
    private static function showWebPerformanceMetrics(string|null $style, array $info): void 
    {
        $style ??= 'position: fixed; bottom: 0; z-index: 9000; width: 100%; max-height: 400px; background-color: #000; color: #0d930d; padding: .5rem 1rem; margin: 0; left: 0; right: 0; box-sizing: border-box; overflow: auto;';
        $metrics = self::metrics();
        $detailsHtml = '';

        foreach ($info as $label => $value) {
            $detailsHtml .= "<tr><td><strong>{$label}:</strong></td><td>{$value}</td></tr>";
        }

        $whatIncluded = self::whatIncluded(true);
        $contents = <<<JS
        <script data-luminova="debug-js-profiling">function lmvToggleProfilingDetails() {var d = document.getElementById('lmv-debug-details');var b = document.getElementById('lmv-toggle-button');var h = d.style.display === 'none';d.style.display = h ? 'block' : 'none';b.textContent = h ? 'Hide Details' : 'Show Details';}</script>
        JS;

        $contents .= <<<HTML
        <div id="lmv-debug-container" style="{$style}">
            <div id="lmv-header-details" style="height: 35px; display: flex; line-height: 30px; font-weight: bold;">
                <p style="margin: 0;">{$metrics}</p>
                <button type="button" id="lmv-toggle-button" onclick="lmvToggleProfilingDetails()" style="line-height: 30px; font-size: 15px; border-radius: 8px; position: absolute; right: 1rem; background-color: #e9e9ed; color: #000; border: 1px solid #201f1f; cursor: pointer;">Show Details</button>
            </div>
            <div id="lmv-debug-details" style="display: none; padding-top: 10px; color: #fff; height: 300px; overflow:auto;">
                <table style="width: 100%; margin-bottom: 1rem;">
                    <tbody>
                        {$detailsHtml}
                    </tbody>
                </table>
                {$whatIncluded}
            </div>
        </div>
        HTML;

        echo $contents;
    }

    /**
     * Calculate and return the performance metrics.
     *
     * @param bool $html Weather reporting should include html output (default: true).
     * 
     * @return string Formatted performance metrics including execution time, memory usage, and number of files loaded.
     */
    public static function metrics(bool $html = true): string
    {
        $execution = self::$endTime - self::$startTime;
        $executionFormatted = ($execution < 1) ? sprintf('%.2f ms', $execution * 1000) : sprintf('%.4f s', $execution);
        $filesLoaded = count(get_included_files());
        $dbTime = shared('__DB_QUERY_EXECUTION_TIME__', null, 0);
        $dbTimeFormatted = ($dbTime < 1) ? sprintf('%.2f ms', $dbTime * 1000) : sprintf('%.4f s', $dbTime);
        $memoryUsage = (self::$endMemory - self::$startMemory) / 1024;
        $separator = $html ? '<span style="color:#eecfcf;margin: 0 1rem;">|</span>' : ' | ';

        return sprintf(
            "Execution Time: %s%sDatabase Query Time: %s%sMemory Usage: %.2f KB%sFiles Loaded: %d",
            $executionFormatted,
            $separator,
            $dbTimeFormatted,
            $separator,
            $memoryUsage,
            $separator,
            $filesLoaded
        );
    }

    /**
     * Load all included files.
     * 
     * @param string $context The context to load the files for.
     * 
     * @return array<int,array|string> Return all included files.
    */
    private static function fileInfo(string $context = 'web'): array 
    {
        $files = get_included_files();
        $categories = [
            'Module' => 0, 
            'ThirdParty' => 0, 
            'Others' => 0
        ];
        $list = [];
        $html = '';

        if($context === 'web'){
            $ide = env('debug.coding.ide', 'vscode');
            $scheme = match ($ide) {
                'phpstorm' => 'phpstorm://open?url=file:',
                'sublime' => 'sublimetext://open?url=file:',
                'vscode' => 'vscode://file',
                default => "{$ide}://file",
            };
        }

        foreach ($files as $index => $file) {
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

            $categories[$category]++;
            
            if($context === 'web'){
                $bgColor = ($index % 2 === 0) ? '#282727' : '#111010';
                $html .= "<li style='background-color: {$bgColor}; padding: .5rem;'>"
                    . "[<span style='color: {$color}'>{$category}</span>] "
                    . "<a style='color:#44a1f2' href='{$scheme}{$file}'>/{$filtered}</a>"
                    . "</li>";
            }elseif($context === 'api' || $context === 'cli'){
                $list[] = [
                    'Category' => $category, 
                    'File' => $filtered
                ];
            }
        }

        return [
            $categories, ($context === 'web' ? $html : $list)
        ];
    }

    /**
     * Display a list of included files.
     *
     * @param bool $forProfiling Whether to indicate profiling context (default: false).
     *
     * @return string Return html contents.
     */
    public static function whatIncluded(bool $forProfiling = false): string
    {
        $content = '<h2>Included Files Summary:</h2>';

        if ($forProfiling) {
            $content .= <<<HTML
            <p style="background-color: #932727; color: #fff; padding: .5rem; border-radius: 6px;">
                <strong>Note:</strong> Some modules like (<code>IP, UserAgent, Browser</code>) may not be included in the total count as they are used for profiling only.
            </p>
            HTML;
        }
        
        [$categories, $html] = self::fileInfo('web');

        // Output counters
        $content .= <<<HTML
        <table style="width:100%; margin-bottom:1rem;">
            <tbody>
                <tr><td><strong>Total Framework Modules:</strong></td><td style='text-align:center;'>[<span style='color:#04ac17'>{$categories['Module']}</span>]</td></tr>
                <tr><td><strong>Total Third Party Modules:</strong></td><td style='text-align:center;'>[<span style='color:#d99a06'>{$categories['ThirdParty']}</span>]</td></tr>
                <tr><td><strong>Other Modules Including Controllers:</strong></td><td style='text-align:center;'>[<span style='color:#eee'>{$categories['Others']}</span>]</td></tr>
            </tbody>
        </table>
        <h2>Included Files:</h2>
        <ol id='lmv-included-files' style='list-style-type: none; padding: 0; margin: 0;'>{$html}</ol>
        HTML;

        return $content;
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