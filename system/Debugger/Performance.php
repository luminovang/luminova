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

use \Luminova\Boot;
use \Luminova\Luminova;
use \Luminova\Utility\Math;
use \Luminova\Http\Request;
use \Luminova\Logger\Logger;
use \Luminova\Http\Network\IP;
use \Luminova\Debugger\Tracer;
use \Luminova\Command\Terminal;
use \Luminova\Exceptions\InvalidArgumentException;

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
     * Is profiling disabled. 
     *
     * @var bool $isDisabled
     */
    private static bool $isDisabled = false;

    /**
     * Temporary disable performance profiling.
     * 
     * This can be called before rendering template or API response.
     *
     * @return void
     */
    public static function disable(): void 
    {
        self::$isDisabled = true;
        self::$startTime = 0.0;
        self::$startMemory = 0;
    }

    /**
     * Enable performance profiling, if it was previously disabled.
     * 
     * This can be called before rendering template or API response.
     *
     * @return void
     */
    public static function enable(): void 
    {
        self::$isDisabled = false;
    }

    /**
     * Start measuring time and memory usage.
     *
     * @return void
     */
    public static function start(): void
    {
        if(self::$isDisabled){
            return;
        }

        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
    }

    /**
     * Stop measuring time and memory usage, and display performance metrics.
     * 
     * @param string|null $style Optional CSS style rules for the profiling container.
     * @param array|null $command Command information for CLI profiling (default: null).
     * 
     * **Command structure:**
     *
     * ```
     * [
     *     'input'      => string,        // Original CLI input (without PHP binary)
     *     'name'       => string,        // Resolved command name.
     *     'group'      => string,        // Command group namespace.
     *     'arguments'  => string[],      // Positional arguments (e.g. ['limit=2'])
     *     'options'    => array<string,mixed>, // Named options (e.g. ['no-header' => null])
     *     'command'    => string,        // Full executable command string
     *     'params'     => string[],      // Parsed parameter values
     * ]
     * 
     * @return void
     */
    public static function stop(?string $style = null, ?array $command = null): void
    {
        if(self::$isDisabled){
            return;
        }

        $isApi = Luminova::isApiRequest(true);
        if($isApi && !env('debug.api.performance.profiling', false)){
            return;
        }

        self::$endTime = microtime(true);
        self::$endMemory = memory_get_usage();
        self::$filesLoaded = get_included_files();

        if(Luminova::isCommand()){
            self::showCommandPerformanceMetrics($command);
            return;
        }

        $request = Request::getInstance();
        $classMetadata = Boot::get(Boot::CLASS_METADATA);
 
        $info = [
            'Framework' => Luminova::copyright(),
            'PHP Version' => PHP_VERSION,
            'IP Address' => IP::get(),
            'Environment' => ENVIRONMENT,
            'Script Path' => CONTROLLER_SCRIPT_PATH,
            'Controller' => (!empty($classMetadata['namespace'])) 
                ? $classMetadata['namespace'] . '::' . $classMetadata['method'] . '()' 
                : 'N/A',
            'Cache ID' =>  env('page.caching', false) ? Luminova::getCacheId() : 'N/A',
            'Server Software' => self::esc($_SERVER['SERVER_SOFTWARE'] ?? 'Not Set'),
            'UserAgent' => self::esc($request->getUserAgent()->toString()),
            'Method' => self::esc($request->getMethod()?:'N/A'),
            'URL' => self::esc($request->getUrl(true)),
            'Origin' => self::esc($request->getOrigin()?:'N/A'),
            'Referrer' => self::esc($request->getUserAgent()->getReferrer()?:'N/A'),
            'Cookies' => $request->getCookie()->isEmpty() ? null : $request->getCookie()->count(),
            'Is Secure' => ($request->isSecure() ? 'YES' : 'NO'),
            'Is AJAX' => ($request->isAJAX() ? 'YES' : 'NO'),
            'Is Cached' =>  (!empty($classMetadata['cache'])) ? 'YES' : 'NO',
            'Is Static Cache' =>  (!empty($classMetadata['staticCache'])) ? 'YES' : 'NO',
        ];

        if(!$isApi){
            $content = $_SERVER['HTTP_LMV_SENT_CONTENT_TYPE']
                ?? $_SERVER['CONTENT_TYPE'] 
                ?? $_SERVER['HTTP_CONTENT_TYPE']
                ?? '';

            if(!$content || str_contains($content, 'text/html')){
                self::showWebPerformanceMetrics($style, $info);
                return;
            }
        }

        self::logApiPerformanceMetrics($info);
    }

    /**
     * Execute a performance benchmark and return timing and memory metrics.
     *
     * Measures total execution time, average time per iteration, and memory
     * usage for the given callback. The callback is executed once and is
     * responsible for running the provided number of iterations.
     *
     * @param string $name The benchmark identifier.
     * @param int $iterations Number of iterations to execute inside the callback.
     * @param (callable(int $n):void) $callback Code under test. Receives $iterations as its only argument.
     *
     * @return array<string,mixed> Returns the benchmark result data (timing and memory metrics).
     * @see self::benchmark() To compare multiple benchmarks result.
     *
     * @example - Example:
     * ```php
     * use Monolog\Logger;
     * use Luminova\Logger\NovaLogger;
     * use Monolog\Handler\StreamHandler;
     * use function Luminova\Funcs\root;
     * 
     * $iterations = 10;
     * $logFile = root('/writeable/logs/', 'debug.log');
     *
     * $nova = Performance::benchmark('NovaLogger', $iterations, function (int $n) {
     *     $logger = new NovaLogger('app_channel', useLocking: false);
     *
     *     for ($i = 0; $i < $n; $i++) {
     *         $logger->log('debug', 'Test Nova Speed.');
     *     }
     * });
     * 
     * $mono = Performance::benchmark('Monolog', $iterations, function ($n) use ($logFile) {
     *      $logger = new Logger('my_app_channel');
     *      $logger->pushHandler(
     *          new StreamHandler($logFile, Logger::DEBUG, true, 0644, false)
     *      );
     * 
     *      for ($i = 0; $i < $n; $i++) {
     *          $logger->debug('Test Mono Speed.');
     *      }
     * });
     * 
     * print_r(Performance::compare($nova, $mono));
     * ```
     */
    public static function benchmark(
        string $name,
        int $iterations,
        callable $callback
    ): array 
    {
        gc_collect_cycles();

        $startMem = memory_get_usage(true);
        $start    = hrtime(true);

        $callback($iterations);

        $end      = hrtime(true);
        $endMem   = memory_get_usage(true);

        $totalNs = $end - $start;
        $totalMs = $totalNs / 1_000_000;

        return [
            'name'        => $name,
            'iterations'  => $iterations,
            'total_ms'    => $totalMs,
            'avg_ms'      => $totalMs / max(1, $iterations),
            'memory_kb'   => ($endMem - $startMem) / 1024,
            'started_at'  => $start,
            'ended_at'    => $end,
        ];
    }

    /**
     * Compare multiple benchmark results and return a performance verdict.
     *
     * Determines which benchmark is fastest, differences in milliseconds,
     * execution ratios, and a human-readable verdict including percentage difference.
     *
     * @param array<string,mixed> ...$benchmarks Benchmark result arrays from self::benchmark().
     *
     * @return array{
     *     fastest:string,
     *     verdict:string,
     *     ratios:array<string,float>,
     *     differences:array<string,float>,
     *     percentages:array<string,float>
     * } Returns comparison results among all benchmarks.
     * 
     * @see self::benchmark() To performance calculate benchmarks.
     * 
     * @example - Example:
     * ```php
     * 
     * $iterations = 10;
     *
     * $t1 = Performance::benchmark('Test 1', $iterations, fn() => });
     * $t2 = Performance::benchmark('Test 2', $iterations, fn() => });
     * $t3 = Performance::benchmark('Test 3', $iterations, fn() => });
     * 
     * print_r(Performance::compare($t1, $t2, $t3));
     * ```
     */
    public static function compare(array ...$benchmarks): array
    {
        $count = count($benchmarks);
        if ($count < 2) {
            throw new InvalidArgumentException('At least two benchmarks are required for comparison.');
        }

        $fastest = null;

        foreach ($benchmarks as $idx => &$benchmark) {
            if (empty($benchmark['name'])) {
                $benchmark['name'] = "Test " . ($idx + 1);
            }

            if ($fastest === null || ($benchmark['total_ms'] ?? INF) < ($fastest['total_ms'] ?? INF)) {
                $fastest = $benchmark;
            }
        }
        unset($benchmark);

        $fastestName = $fastest['name'] ?? '';
        $fastestTotal = max((float) ($fastest['total_ms'] ?? 0), 0.000001);

        $diffs = [];
        $ratios = [];
        $percentages = [];
        $verdicts = [];

        foreach ($benchmarks as $benchmark) {
            $name = $benchmark['name'];
            $total = (float) ($benchmark['total_ms'] ?? 0);

            $diffs[$name] = round($total - $fastestTotal, 3);
            $ratios[$name] = round($total / $fastestTotal, 2);
            $percentages[$name] = max(0.0, round(($total - $fastestTotal) / $fastestTotal * 100, 2));
        }
        
        foreach ($benchmarks as $benchmark) {
            $name = $benchmark['name'];
            if ($name === $fastestName) continue;

            $diff = abs($diffs[$name]);
            $percentage = $percentages[$name];

            $verdicts[] = match (true) {
                $diff === 0.0 => sprintf('Both %s and %s perform equally.', $fastestName, $name),
                $percentage >= 50 => 
                    sprintf('%s is significantly faster than %s (%.2f%% faster).', $fastestName, $name, $percentage),
                default => 
                    sprintf('%s is slightly faster than %s (%.2f%% faster).', $fastestName, $name, $percentage),
            };
        }

        return [
            'fastest'     => $fastestName,
            'verdict'     => implode(' ', $verdicts),
            'ratios'      => $ratios,
            'differences' => $diffs,
            'percentages' => $percentages
        ];
    }

    /**
     * Calculate and return the performance metrics.
     *
     * @param bool $html Whether reporting should include html output (default: true).
     * 
     * @return string Return formatted performance metrics including execution time, 
     *          memory usage, and number of files loaded.
     */
    public static function metrics(bool $html = true): string
    {
        [$total, $controllers] = self::fileCount();
        $separator = $html ? '<span style="color:#eecfcf;margin: 0 1rem;">|</span>' : ' | ';
        $unused = ($controllers >= 10)
            ? $separator . ($html 
                    ? '<span style="color:#af1b2e;" title="Total number of scanned controllers files while searching for matched controller for view.">Scanned Controllers: ' . $controllers . '</span>' 
                    : 'Scanned Controllers: ' . $controllers
                )
            : '';

        $db = Boot::get(Boot::QUERY_PROFILING) ?? [];

        return sprintf(
            "Execution: %s%sDatabase: %s%sMemory: %s%sFiles: %d%s",
            Math::toTimeUnit(abs(self::$endTime - self::$startTime) * 1_000, withName: true),
            $separator,
            Math::toTimeUnit(
                ($db['global']['time'] ?? 0) * 1_000, 
                withName: true
            ),
            $separator,
            Math::toUnit(self::$endMemory - self::$startMemory, withName: true),
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
            $scheme = Tracer::getIdeEditorUri();
        }

        $filename = Boot::get(Boot::CLASS_METADATA)['filename'] ?? null;
        $index = 0;

        foreach (self::$filesLoaded ?? get_included_files() as $file) {
            if (str_ends_with($file, 'system/Debugger/Performance.php')) {
                continue;
            }

            $filtered = Luminova::toDisplayPath($file);
            $category = 'Others';
            $color = '#6f4f06';

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
                    $color = '#15728e';
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
                $file = urlencode($file);
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
        $controllers = self::fileCount()[1];
        
        // Generate optimization tip if unused files are present
        $unusedOptimization = ($controllers >= 10)
            ? <<<HTML
                <p style="background-color: #932727; color: #fff; padding: .5rem; border-radius: 6px; margin-bottom: 1rem;">
                    <strong>Unused Optimization Tip:</strong> To improve application performance and reduce the number of unused files, enable 
                    <code>feature.route.cache.attributes</code> in your environment configuration file (<code>.env</code>). Additionally, remove any unused modules and included files to further optimize memory usage and overall efficiency.
                </p>
                HTML
            : '';

        // Build the content table to display included file types and counts
        $content = <<<HTML
            <div class="lmv-tab" id="lmv-tab-sources" style="display:none">
                <table style="width:100%; color:#f2efef; margin-bottom:1rem;border: 1px solid #113535" cellpadding="4" cellspacing="3">
                    <thead style="background-color: #113535; color:#dadada; font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; font-weight: bold; font-size: 1.3rem;">
                        <tr style="border-bottom: 1px solid #113535">
                            <td style="padding: .5rem;">Loaded File Source</td>
                            <td style="padding: .5rem; text-align:center;">Summary</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #113535">
                            <td style="padding: .5rem 5px;"><strong>Composer Autoload Modules:</strong></td>
                            <td style='text-align:center;'><span style='background-color:#15728e; padding: 0 1rem; border-radius: 8px; color: #ccc; font-weight: bold;'>{$categories['Composer']}</span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #113535">
                            <td style="padding: .5rem 5px;"><strong>Framework Core Modules:</strong></td>
                            <td style='text-align:center;'><span style='background-color:#057d14; padding: 0 1rem; border-radius: 8px; color: #ccc; font-weight: bold;'>{$categories['Module']}</span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #113535">
                            <td style="padding: .5rem 5px;"><strong>Third Party Modules:</strong></td>
                            <td style='text-align:center;'><span style='background-color:#ac3507; padding: 0 1rem; border-radius: 8px; color: #ccc; font-weight: bold;'>{$categories['ThirdParty']}</span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #113535">
                            <td style="padding: .5rem 5px;"><strong>Application Modules:</strong></td>
                            <td style='text-align:center;'><span style='background-color:#6f4f06; padding: 0 1rem; border-radius: 8px; color: #fff; font-weight: bold;'>{$categories['Others']}</span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #113535">
                            <td style="padding: .5rem 5px;"><strong>Controllers Scanned:</strong></td>
                            <td style='text-align:center;'><span style='background-color:#2a669b; padding: 0 1rem; border-radius: 8px; color: #fff; font-weight: bold;'>{$controllers}</span></td>
                        </tr>
                        <tr>
                            <td style="padding: .5rem 5px;"><strong>Controllers Loaded:</strong></td>
                            <td style='text-align:center;'><span style='background-color:#eee; padding: 0 1rem; border-radius: 8px; color: #000; font-weight: bold;'>{$categories['Controller']}</span></td>
                        </tr>
                    </tbody>
                </table>

                {$unusedOptimization}
            </div>

            <div class="lmv-tab" id="lmv-tab-files" style="display:none">
                <h2 style="background-color: #113535; padding: .5rem; color:#dadada; font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; font-weight: bold; font-size: 1.3rem; margin-bottom:0;">
                    Loaded Files
                </h2>
                <ol id='lmv-included-files' style='list-style-type: none; padding: 0; margin: 0;'>{$html}</ol>
            </div>
        HTML;

        return $content;
    }

    /**
     * Log performance metrics when making API calls.
     * 
     * @param array<string,mixed> $info The performance basic information.
     * 
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
            'AppModules' => $categories['Others'],
            'Controllers' => $categories['Controller']
        ];

        $logData['database_executions'] = Boot::get(Boot::QUERY_PROFILING) ?? [];
        $logData['included_files'] = $files;

        // Log the complete data
        Logger::metrics(json_encode($logData, JSON_PRETTY_PRINT)?: '', [
            'key' => Request::getInstance()->getUrl(true)
        ]);
    }

    /**
     * Display performance metrics when making CLI Command request.
     * 
     * @param array|null $command The additional command information passed by router.
     * 
     * {@var array{group, name, input, options, arguments} $commands
     * 
     * @return void
     */
    private static function showCommandPerformanceMetrics(?array $command = null): void 
    {
        Terminal::init();
        Terminal::newLine();
        Terminal::writeln('Command Execution Profiling');
        Terminal::writeln(self::metrics(false), 'green');

        if (Terminal::prompt('Show More Details?', ['yes', 'no']) !== 'yes') {
            return;
        }

        $command ??= [];
        $classMetadata = Boot::get(Boot::CLASS_METADATA);

        // Display basic system information
        $info = [
            'Framework' => Luminova::copyright(),
            'NovaKit Version' => Luminova::NOVAKIT_VERSION,
            'PHP Version' => PHP_VERSION,
            'Environment' => ENVIRONMENT,
            'Script Path' => CONTROLLER_SCRIPT_PATH,
            'Controller' => (!empty($classMetadata['namespace'])) ? $classMetadata['namespace'] . '::' . $classMetadata['method'] . '()' : 'N/A',
            'Terminal' => self::esc(Terminal::getTerminalName() ?: 'Unknown'),
            'Server Software' => self::esc($_SERVER['SERVER_SOFTWARE'] ?? 'Not Set'),
            'Method' => 'CLI',
            'Group' => $command['group'] ?? 'N/A',
            'Command' => $command['name'] ?? 'N/A',
            'Executed' => $command['input'] ?? 'No command executed'
        ];

        Terminal::print(Terminal::table(
            ['Variable', 'Values'], 
            array_map(function($key, $value) {
                return [
                    'Variable' => $key,
                    'Values' => $value
                ];
            }, array_keys($info), $info)
        ));

        if (!empty($command['options'])) {
            Terminal::writeln('Command Arguments:');
        
            // Map the arguments to table
            Terminal::print(Terminal::table(
                ['Name', 'Value'], 
                array_map(function($key, $value) {
                    return [
                        'Name' => $key,
                        'Value' => (is_array($value) ? json_encode($value) : $value ?? 'TRUE')
                    ];
                }, array_keys($command['options']), $command['options'])
            ));
        }

        if (!empty($command['arguments'])) {
            Terminal::writeln('Command Parameters:');
      
            $rows = [];
            $arguments = $command['arguments'];
            $totalArgs = count($arguments);

            for ($index = 0; $index < $totalArgs; $index++) {
                $arg = $arguments[$index];

                if (str_contains($arg, '=')) {
                    [$key, $value] = explode('=', $arg, 2);
                } else {
                    // Process for key-next value when command param is executed `foo bar` instead of `foo=bar`
                    $key = $arg;
                    $value = $arguments[$index + 1] ?? null;
                    $index++; // Skip the next argument as it has been processed as value
                }

                $rows[] = [
                    'Param' => $key,
                    'Value' => $value ?? 'TRUE'
                ];
            }

            Terminal::print(Terminal::table(
                ['Param', 'Value'], 
                $rows
            ));
        }

        // Display included files and categorize them
        Terminal::writeln('Loaded File Summary:');
        [$categories, $files] = self::fileInfo('cli');
   
        // Display the summary of included files by category
        Terminal::print(Terminal::table(['Source', 'Summary'], [
            [
                'Source' => 'Composer Autoload Modules', 
                'Summary' => $categories['Composer']
            ],
            [
                'Source' => 'Framework Core Modules', 
                'Summary' => $categories['Module']
            ],
            [
                'Source' => 'Third Party Modules', 
                'Summary' => $categories['ThirdParty']
            ],
            [
                'Source' => 'Application Modules', 
                'Summary' => $categories['Others']
            ],
            [
                'Source' => 'Controllers Scanned', 
                'Summary' => $classMetadata['controllers']
            ],
            [
                'Source' => 'Controllers Loaded', 
                'Summary' => $categories['Controller']
            ]
        ]));

        Terminal::writeln('Loaded Files:');
        // Display detailed list of included files
        Terminal::print(Terminal::table(['Category', 'File'], $files));
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
        $style ??= 'font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; position: fixed; bottom: 0; z-index: 9000; width: 100%; max-height: 100%; background-color: #000; color: #0d930d; padding: .5rem 1rem; margin: 0; left: 0; right: 0; box-sizing: border-box; overflow: auto;';
        $metrics = self::metrics();
        $detailsHtml = '';

        foreach ($info as $label => $value) {
            $value = ($label=== 'Cookies') 
                ? self::getCookieFile($value, true) 
                : ($value ?? 'N/A');

            if($value === 'N/A'){
                continue;
            }

            $detailsHtml .= "<tr style='padding: .5rem;'><td><strong>{$label}:</strong></td><td>{$value}</td></tr>";
        }

        $whatIncluded = self::whatIncluded();

        $queries = 0;
        $db = Boot::get(Boot::QUERY_PROFILING) ?? [];
        $dbHtml = '';

        if($db){
            $queries = count($db['queries']);
            $dbHtml .= '<h2 style="background-color: #113535; padding: .5rem; color:#dadada; font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; font-weight: bold; font-size: 1.3rem; margin-bottom:0;">' . ucfirst(htmlspecialchars($db['global']['driver'])) . ' Database Query Executions</h2>';
            $preCodeStyle = 'style="background:#1e1e1e;
                color:#BFC7D5;
                padding:8px;
                border-radius:6px;
                font-size:13px;
                display: block;
                margin-bottom: 1rem;
                font-family:monospace;
                white-space: pre-wrap; 
                word-wrap: break-word;
                word-break: break-word;
                overflow-x:auto;"';
            $codeStyle = 'style="display: block;
                overflow-x: auto;
                padding: 1em;
                background: #161618;
                color: #BFC7D5;
                border: 1px solid #292a2c;
                word-break: normal;
                border-radius: .25rem;
                word-wrap: break-word;
            "';

            foreach ($db['queries'] as $idx => $row) {
                $dbHtml .= '<h2 style="background:#374a4a;color:#e9e9ed; padding: .3rem; color:#dadada; font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; font-weight: bold; font-size: 1rem; margin-bottom:0;">
                    #' . ($idx + 1) . ' ' . htmlspecialchars($row['method']) . '
                </h2>';

                $dbHtml .= '<pre ' . $preCodeStyle . '><span style="font-weight:bold">Query</span><code ' . $codeStyle . ' class="language-sql">';
                $dbHtml .= htmlspecialchars(trim($row['query']));
                $dbHtml .= '</code></pre>';

                $dbHtml .= '<pre ' . $preCodeStyle . '><span style="font-weight:bold">Params</span><code ' . $codeStyle . ' class="language-json">';
                $dbHtml .= htmlspecialchars(json_encode($row['params'], JSON_PRETTY_PRINT));
                $dbHtml .= '</code></pre>';

                $dbHtml .= '<div style="width:100%;display:table;">';
                $dbHtml .= '<table style="width:30%;border-collapse:collapse;margin-bottom:1rem;float:right">';
                $dbHtml .= '<tr style="background:#374a4a;color:#e9e9ed;text-align:left;">';
                $dbHtml .= '<th style="padding:4px 8px;border:1px solid #113535;">Query Execution</th>';
                $dbHtml .= '</tr>';

                $dbHtml .= '<tr style="border:1px solid #113535;">';
                
                $dbHtml .= '<td style="padding:4px 8px;border:1px solid #113535;">'. Math::toTimeUnit($row['time'] * 1_000, withName: true) . ' ' . self::getVerdict($row['time']) . '</td>';

                $dbHtml .= '</tr>';
                $dbHtml .= '</table></div>';
            }

        }

        $contents = <<<HTML
        <script data-luminova="debug-js-profiling">
            function lmvToggleProfilingDetails(){ var d=document.getElementById('lmv-debug-details'); var b=document.getElementById('lmv-toggle-button'); var e=document.getElementById('lmv-expand-button'); var h=d.style.display==='none'; d.style.display=h ? 'block' : 'none'; d.style.height='300px'; lmvIcon(false); e.style.display=h ? 'block' : 'none'; b.textContent=h ? 'Hide Details' : 'More Details';}function lmvExpandProfilingDetails(escape=false){ var d=document.getElementById('lmv-debug-details'); var isExpand=(d.style.height==='300px'); if(escape && isExpand){ return;} d.style.height=isExpand ? 'calc(100vh - 40px)' : '300px'; lmvIcon(isExpand);}function lmvIcon(isExpand){ var icon=document.getElementById('lmv-expand-icon'); icon.innerHTML=isExpand ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 3V9H3" stroke="currentColor" stroke-width="2"/><path d="M15 3V9H21" stroke="currentColor" stroke-width="2"/><path d="M9 21V15H3" stroke="currentColor" stroke-width="2"/><path d="M15 21V15H21" stroke="currentColor" stroke-width="2"/></svg>` : `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 9V3H9" stroke="currentColor" stroke-width="2"/><path d="M21 9V3H15" stroke="currentColor" stroke-width="2"/><path d="M3 15V21H9" stroke="currentColor" stroke-width="2"/><path d="M21 15V21H15" stroke="currentColor" stroke-width="2"/></svg>`;}function lmvToggleProfilingDetail(target){ var tabs=document.getElementsByClassName('lmv-tab'); for (var i=0; i < tabs.length; i++){ tabs[i].style.display='none';} var t=document.getElementById(target); if (t) t.style.display='block'; var buttons=document.getElementsByClassName('lmv-tab-buttons'); for (var i=0; i < buttons.length; i++){ buttons[i].style.backgroundColor='#374a4a';} var btn=event.currentTarget || document.querySelector('[onclick*="' + target + '"]'); if (btn) btn.style.backgroundColor='#113535';}document.addEventListener('keydown', function (e){ if (e.key==='Escape'){ lmvExpandProfilingDetails(true);}});
        </script>
        HTML;

        $counters = 'style="background-color: #fff;color: #000;border-radius: 10%;padding: 2px;font-size: 13px;font-weight: bold;display: inline-block;padding: 3px 7px;line-height: 14px;"';

        $tabs = [
            'lmv-tab-application' => 'Application',
            'lmv-tab-sources' => 'File Source',
            'lmv-tab-files' => 'Included Files <span ' . $counters . '>' . self::fileCount()[0] . '</span>',
            'lmv-tab-database' => 'Database Executions <span ' . $counters . '>' . $queries . '</span>'
        ];

        $buttons = '';
        foreach($tabs as $tid => $tab){
            $active = ($tid === 'lmv-tab-application') ? '#113535' : '#374a4a';
            $buttons .= '<button class="lmv-tab-buttons" type="button" onclick="lmvToggleProfilingDetail(\'' . $tid .'\')" style="line-height: 30px; font-size: 15px; border-radius: 8px 8px 0 0; background-color: ' . $active . '; color: #e9e9ed; border: 1px solid #113535; cursor: pointer;padding:3px 8px;margin-right:.6rem">' . $tab .'</button>';
        }

        $contents .= <<<HTML
        <div id="lmv-debug-container" style="{$style}">
            <div id="lmv-header-details" style="height: 35px; display: flex; line-height: 30px; font-weight: bold;">
                <p style="margin: 0;">{$metrics}</p>
                <div style="position: absolute; right: 1rem;display:flex">
                    <button type="button" id="lmv-expand-button" onclick="lmvExpandProfilingDetails()" style="display:none;line-height: 16px; border-radius: 8px; background-color: transparent; color: #e9e9ed; border: 1px solid #e9e9ed; cursor: pointer;padding:3px 8px;margin-right: 10px"><span id="lmv-expand-icon"></span></button>
                    <button type="button" id="lmv-toggle-button" onclick="lmvToggleProfilingDetails()" style="line-height: 30px; font-size: 15px; border-radius: 8px; background-color: #073084; color: #e9e9ed; border: 1px solid #073084; cursor: pointer;padding:3px 8px;">More Details</button>
                </div>
            </div>
            <div id="lmv-debug-details" style="display: none; padding-top: 10px; color: #fff; height: 300px; overflow:auto;">
                {$buttons}
                <div class="lmv-tab" id="lmv-tab-application">
                    <table style="width: 100%; margin-bottom: 1rem;color:#f2efef" cellpadding="4">
                        <thead style="background-color: #113535; color:#dadada; font-family: -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; font-weight: bold; font-size: 1.3rem;">
                            <tr>
                                <td style="padding: .5rem;" colspan="2">On This Page</td>
                            </tr>
                        </thead>
                        <tbody>
                            {$detailsHtml}
                        </tbody>
                    </table>
                </div>
                {$whatIncluded}
                <div class="lmv-tab" id="lmv-tab-database" style="display:none">{$dbHtml}</div>
            </div>
        </div>
        HTML;

        echo $contents;
    }

    /**
     * Calculate database executions per query.
     * 
     * @param float|int $time The execution time.
     * 
     * @return string Return HTML string for verdict label.
     */
    private static function getVerdict(float|int $time): string 
    {
        $timeMs = $time * 1000;

        [$label, $color] = match(true) {
            $timeMs < 1   => ['Efficient', '#28a745'],
            $timeMs < 10  => ['Normal', '#17a2b8'],
            $timeMs < 50  => ['Moderate', '#ffc107'],
            $timeMs < 200 => ['Slow', '#fd7e14'],
            default       => ['Critical', '#dc3545'],
        };

        return '<span style="
            display:inline-block;
            min-width:60px;
            text-align:center;
            padding:2px 8px;
            border-radius:12px;
            background-color:' . $color . ';
            color:#fff;
            font-size:12px;
            font-weight:600;
        ">' . $label . '</span>';

    }

    /**
     * Create cookies length flags.
     * 
     * @return string Return cookie flag as HTML or text.
     */
    private static function getCookieFile(?int $cookies, bool $is_html = false): string  
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
     * @return array{0:int,1:int} Return the total number of files loaded and unused files count.
     */
    private static function fileCount(): array 
    {
        $controllers = max(Boot::get(Boot::CLASS_METADATA)['controllers'] ?? 0, 0);

        return [
            count(self::$filesLoaded) - $controllers, 
            $controllers
        ];
    }
}