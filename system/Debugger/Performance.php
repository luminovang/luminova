<?php
/**
 * Performance class for measuring and displaying performance metrics.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova\Debugger; 

use \Luminova\Luminova;
use \Luminova\Functions\Maths;
use \Luminova\Functions\IP;
use \Luminova\Command\Terminal;
use \Luminova\Http\Request;
use \Luminova\Logger\Logger;
use function \Luminova\Funcs\{
    filter_paths,
    shared
};

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
     * List of included time after execution ended.
     * 
     * @var array $filesLoaded
     */
    private static ?array $filesLoaded = null;

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
        $isApi = Luminova::isApiPrefix();
        if($isApi && !env('debug.api.performance.profiling', false)){
            return;
        }

        self::$endTime = microtime(true);
        self::$endMemory = memory_get_usage();
        self::$filesLoaded = get_included_files();

        if(Luminova::isCommand()){
            self::showCommandPerformanceMetrics($context);
            return;
        }

        self::$request ??= new Request();
        $classInfo = Luminova::getClassInfo();
 
        $info = [
            'Framework' => Luminova::copyright(),
            'PHP Version' => PHP_VERSION,
            'IP Address' => self::esc(IP::get()),
            'Environment' => ENVIRONMENT,
            'Script Path' => CONTROLLER_SCRIPT_PATH,
            'Controller' => (!empty($classInfo['namespace'])) 
                ? $classInfo['namespace'] . '::' . $classInfo['method'] . '()' 
                : 'N/A',
            'Cache File' =>  env('page.caching', false) ? Luminova::getCacheId() . '.lmv' : 'N/A',
            'Server Software' => self::esc($_SERVER['SERVER_SOFTWARE'] ?? 'Not Set'),
            'UserAgent' => self::esc(self::$request->getUserAgent()->toString()),
            'Method' => self::esc(self::$request->getMethod()?:'N/A'),
            'URL' => self::esc(self::$request->getUrl()),
            'Origin' => self::esc(self::$request->getOrigin()?:'N/A'),
            'Referrer' => self::esc(self::$request->getUserAgent()->getReferrer()?:'N/A'),
            'Cookies' => self::$request->getCookie()->isEmpty() ? null : self::$request->getCookie()->count(),
            'Is Secure' => (self::$request->isSecure() ? 'YES' : 'NO'),
            'Is AJAX' => (self::$request->isAJAX() ? 'YES' : 'NO'),
            'Is Cache' =>  (!empty($classInfo['cache'])) ? 'YES' : 'NO',
            'Is Static Cache' =>  (!empty($classInfo['staticCache'])) ? 'YES' : 'NO',
        ];

        if($isApi){
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
        $info['Cookies'] = self::getCookieFile($info['Cookies']);
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
            'FrameworkModules' => $categories['Module'],
            'ComposerModules' => $categories['Composer'],
            'ThirdPartyModules' => $categories['ThirdParty'],
            'Controllers' => $categories['Controller'],
            'OtherModules' => $categories['Others']
        ];

        $logData['included_files'] = $files;

        // Log the complete data
        Logger::metrics(json_encode($logData, JSON_PRETTY_PRINT)?: '', [
            'key' => self::$request->getUrl()
        ]);
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
        self::$terminal->writeln('Command Execution Profiling');
        self::$terminal->writeln(self::metrics(false), 'green');

        if (self::$terminal->prompt('Show More Details?', ['yes', 'no']) !== 'yes') {
            return;
        }

        $classInfo = Luminova::getClassInfo();

        // Display basic system information
        $info = [
            'Framework' => Luminova::copyright(),
            'NovaKit Version' => Luminova::NOVAKIT_VERSION,
            'PHP Version' => PHP_VERSION,
            'Environment' => ENVIRONMENT,
            'Script Path' => CONTROLLER_SCRIPT_PATH,
            'Controller' => (!empty($classInfo['namespace'])) ? $classInfo['namespace'] . '::' . $classInfo['method'] . '()' : 'N/A',
            'Server Software' => self::esc($_SERVER['SERVER_SOFTWARE'] ?? 'Not Set'),
            'Method' => 'CLI',
            'Group' => $context['commands']['group'] ?? 'N/A',
            'Command' => $context['commands']['command'] ?? 'N/A',
            'Executed' => $context['commands']['exe_string'] ?? 'No command executed'
        ];

        self::$terminal->print(self::$terminal->table(
            ['Variable', 'Values'], 
            array_map(function($key, $value) {
                return [
                    'Variable' => $key,
                    'Values' => $value
                ];
            }, array_keys($info), $info)
        ));

        if (!empty($context['commands']['options'])) {
            self::$terminal->writeln('Command Arguments:');
        
            // Map the arguments to table
            self::$terminal->print(self::$terminal->table(
                ['Name', 'Value'], 
                array_map(function($key, $value) {
                    return [
                        'Name' => $key,
                        'Value' => (is_array($value) ? json_encode($value) : $value ?? 'NULL')
                    ];
                }, array_keys($context['commands']['options']), $context['commands']['options'])
            ));
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

            self::$terminal->print(self::$terminal->table(
                ['Param', 'Value'], 
                $rows
            ));
        }

        // Display included files and categorize them
        self::$terminal->writeln('Included Files Summary:');
        [$categories, $files] = self::fileInfo('cli');
   
        // Display the summary of included files by category
        self::$terminal->print(self::$terminal->table(['Origination', 'Total'], [
            [
                'Origination' => 'Framework Modules', 
                'Total' => $categories['Module']
            ],
            [
                'Origination' => 'Composer Autoload Modules', 
                'Total' => $categories['Composer']
            ],
            [
                'Origination' => 'Third Party Modules', 
                'Total' => $categories['ThirdParty']
            ],
            [
                'Origination' => 'Controllers', 
                'Total' => $categories['Controller']
            ],
            [
                'Origination' => 'Other Modules', 
                'Total' => $categories['Others']
            ]
        ]));

        self::$terminal->writeln('Included Files:');
        // Display detailed list of included files
        self::$terminal->print(self::$terminal->table(['Category', 'File'], $files));
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
        $style ??= 'font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; position: fixed; bottom: 0; z-index: 9000; width: 100%; max-height: 400px; background-color: #000; color: #0d930d; padding: .5rem 1rem; margin: 0; left: 0; right: 0; box-sizing: border-box; overflow: auto;';
        $metrics = self::metrics();
        $detailsHtml = '';

        foreach ($info as $label => $value) {
            $value = ($label=== 'Cookies') ? self::getCookieFile($value, true) : ($value ?? 'N/A');
            $detailsHtml .= "<tr><td><strong>{$label}:</strong></td><td>{$value}</td></tr>";
        }

        $whatIncluded = self::whatIncluded();
        $contents = <<<JS
        <script data-luminova="debug-js-profiling">function lmvToggleProfilingDetails() {var d = document.getElementById('lmv-debug-details');var b = document.getElementById('lmv-toggle-button');var h = d.style.display === 'none';d.style.display = h ? 'block' : 'none';b.textContent = h ? 'Hide Details' : 'More Details';}</script>
        JS;

        $contents .= <<<HTML
        <div id="lmv-debug-container" style="{$style}">
            <div id="lmv-header-details" style="height: 35px; display: flex; line-height: 30px; font-weight: bold;">
                <p style="margin: 0;">{$metrics}</p>
                <button type="button" id="lmv-toggle-button" onclick="lmvToggleProfilingDetails()" style="line-height: 30px; font-size: 15px; border-radius: 8px; position: absolute; right: 1rem; background-color: #e9e9ed; color: #000; border: 1px solid #201f1f; cursor: pointer;">More Details</button>
            </div>
            <div id="lmv-debug-details" style="display: none; padding-top: 10px; color: #fff; height: 300px; overflow:auto;">
                <table style="width: 100%; margin-bottom: 1rem;color:#f2efef" cellpadding="4">
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
     * Create cookies length flags.
     * 
     * @return string Return cookie flag as HTML or text.
     */
    private static function getCookieFile(int|null $cookies, bool $is_html = false): string  
    {
        if ($cookies === null) {
            return '';
        }
        
        return $cookies . match (true) {
            $cookies <= 20 => $is_html 
                ? ' <span style="color: green;">[normal]</span>' 
                : ' [normal]',
            $cookies >= 21 && $cookies <= 39 => $is_html 
                ? ' <span style="color: orange;">[medium]</span>' 
                : ' [medium]',
            $cookies >= 40 && $cookies <= 50 => $is_html 
                ? ' <span style="color: red;">[large]</span>' 
                : ' [large]',
            $cookies > 50 => $is_html 
                ? ' <span style="color: purple;">[extra-large]</span>' 
                : ' [extra-large]',
            default => $is_html 
                ? ' <span style="color: gray;">[unknown]</span>' 
                : ' [unknown]',
        };
    }

    /**
     * Calculate and return the performance metrics.
     *
     * @param bool $html Whether reporting should include html output (default: true).
     * 
     * @return string Return formatted performance metrics including execution time, memory usage, and number of files loaded.
     */
    public static function metrics(bool $html = true): string
    {
        [$total, $unused] = self::fileCount();
        $separator = $html ? '<span style="color:#eecfcf;margin: 0 1rem;">|</span>' : ' | ';
        $unused = ($unused > 0)
            ? $separator . ($html 
                    ? '<span style="color:#af1b2e;" title="Total number of skipped controllers files while searching for matched controller for view.">Skipped Controllers: ' . $unused . '</span>' 
                    : 'Skipped Controllers: ' . $unused
                )
            : '';

        return sprintf(
            "Execution Time: %s%sDatabase Query Time: %s%sMemory Usage: %s%sLoaded Files: %d%s",
            Maths::toTimeUnit(abs(self::$endTime - self::$startTime) * 1_000, true),
            $separator,
            Maths::toTimeUnit(shared('__DB_QUERY_EXECUTION_TIME__', null, 0) * 1_000, true),
            $separator,
            Maths::toUnit(self::$endMemory - self::$startMemory, true),
            $separator,
            $total,
            $unused
        );
    }

    /**
     * Load all included files.
     * 
     * @param string $context The context to load the files for (e.g, `web`, `api or `cli`).
     * 
     * @return array<int,array|string> Return all included files.
     */
    public static function fileInfo(string $context = 'web'): array 
    {
        $categories = [
            'Module' => 0, 
            'Controller' => 0, 
            'ThirdParty' => 0, 
            'Composer' => 0,
            'Others' => 0
        ];
        $list = [];
        $html = '';

        if($context === 'web'){
            $ide = defined('IS_UP') ? env('debug.coding.ide', 'vscode') : 'vscode';
            $scheme = match ($ide) {
                'phpstorm' => 'phpstorm://open?url=file:',
                'sublime' => 'sublimetext://open?url=file:',
                'vscode' => 'vscode://file',
                default => "{$ide}://file",
            };
        }

        $filename = Luminova::getClassInfo()['filename'] ?? null;
        $index = 0;

        foreach (self::$filesLoaded ?? get_included_files() as $file) {
            if (str_ends_with($file, 'system/Debugger/Performance.php')) {
                continue;
            }

            $filtered = filter_paths($file);
            $category = 'Others';
            $color = '#d99a06';

            if (
                str_starts_with($filtered, 'system') ||
                str_starts_with($filtered, 'public/index.php') || 
                str_starts_with($filtered, 'bootstrap/')
            ) {
                if (
                    str_starts_with($filtered, 'system/plugins/composer') || 
                    str_ends_with($filtered, 'system/plugins/autoload.php')
                ) {
                    $category = 'Composer';
                    $color = '#e66c13';
                }elseif (str_starts_with($filtered, 'system/plugins')) {
                    $category = 'ThirdParty';
                    $color = '#af1b2e';
                } else {
                    $category = 'Module';
                    $color = '#04ac17';
                }
            }elseif(preg_match('/^app(\/Modules(\/[^\/]+)?)?\/Controllers/', $filtered)) {
                $category = 'Controller';
                $color = '#eee';

                if($filename && !str_ends_with($filtered, $filename . '.php')){
                    continue;
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

            $index++;
        }

        return [$categories, ($context === 'web' ? $html : $list)];
    }

    /**
     * Display a list of included files.
     *
     * @return string Return html contents.
     */
    public static function whatIncluded(): string
    {
        // Get file information and usage statistics
        [$categories, $html] = self::fileInfo('web');
        $unused = self::fileCount()[1]??0;
        
        // Generate optimization tip if unused files are present
        $unusedOptimization = ($unused > 0)
            ? <<<HTML
                <p style="background-color: #932727; color: #fff; padding: .5rem; border-radius: 6px; margin-bottom: 1rem;">
                    <strong>Unused Optimization Tip:</strong> To improve application performance and reduce the number of unused files, enable 
                    <code>feature.route.cache.attributes</code> in your environment configuration file (<code>.env</code>). Additionally, remove any unused modules and included files to further optimize memory usage and overall efficiency.
                </p>
                HTML
            : '';

        // Build the content table to display included file types and counts
        $content = <<<HTML
            <table style="width:100%; color:#f2efef; margin-bottom:1rem;" cellpadding="4">
                <thead style="background-color: #113535; color:#dadada; font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; font-weight: bold; font-size: 1.3rem;">
                    <tr>
                        <td style="padding: .5rem;">Included Files Description</td>
                        <td style="padding: .5rem; text-align:center;">Summary</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: .5rem 5px;"><strong>Framework Components and Modules:</strong></td>
                        <td style='text-align:center;'><span style='background-color:#057d14; padding: 0 1rem; border-radius: 8px; color: #ccc; font-weight: bold;'>{$categories['Module']}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: .5rem 5px;"><strong>Composer Autoload Modules:</strong></td>
                        <td style='text-align:center;'><span style='background-color:#af1b2e; padding: 0 1rem; border-radius: 8px; color: #ccc; font-weight: bold;'>{$categories['Composer']}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: .5rem 5px;"><strong>Third Party Modules:</strong></td>
                        <td style='text-align:center;'><span style='background-color:#af1b2e; padding: 0 1rem; border-radius: 8px; color: #ccc; font-weight: bold;'>{$categories['ThirdParty']}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: .5rem 5px;"><strong>Application Controller Classes:</strong></td>
                        <td style='text-align:center;'><span style='background-color:#eee; padding: 0 1rem; border-radius: 8px; color: #000; font-weight: bold;'>{$categories['Controller']}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: .5rem 5px;"><strong>Unused Application Controller Classes:</strong></td>
                        <td style='text-align:center;'><span style='background-color:#2a669b; padding: 0 1rem; border-radius: 8px; color: #fff; font-weight: bold;'>{$unused}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: .5rem 5px;"><strong>Other Application Modules:</strong></td>
                        <td style='text-align:center;'><span style='background-color:#d99a06; padding: 0 1rem; border-radius: 8px; color: #fff; font-weight: bold;'>{$categories['Others']}</span></td>
                    </tr>
                </tbody>
            </table>

            {$unusedOptimization}

            <h2 style="background-color: #113535; padding: .5rem; color:#dadada; font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; font-weight: bold; font-size: 1.3rem; margin-bottom:0;">
                Included File History
            </h2>
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
        return ($input === null) ? '' : htmlspecialchars($input);
    }

    /**
     * Calculate the total number of files loaded, excluding this class and unused controllers.
     *
     * @return array{0:unt,1:int} Return the total number of files loaded and unused files count.
     */
    private static function fileCount(): array 
    {
        $unused = ((Luminova::getClassInfo()['attrFiles'] ?? 1) - 1);
        return [count(self::$filesLoaded) - 1 - $unused, $unused];
    }
}