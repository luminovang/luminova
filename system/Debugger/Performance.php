<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace Luminova\Debugger; 

final class Performance
{
    /**
     * Start time of the performance measurement.
     * @var float $startTime 
     */
    private static float $startTime;

    /**
     * Start memory usage of the performance measurement.
     * @var int $startMemory 
     */
    private static int $startMemory;

    /**
     * End time of the performance measurement.
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
     * Start measuring time and memory usage.
     *
     * @return void
     */
    public static function start(): void
    {
        static::$startTime = microtime(true);
        static::$startMemory = memory_get_usage();
    }

    /**
     * Stop measuring time and memory usage.
     * 
     * @var string|null $style Optional CSS style rules.
     * 
     * @return void
     */
    public static function stop(?string $style = null): void
    {
        static::$endTime = microtime(true);
        static::$endMemory = memory_get_usage();
        $style ??= 'position: fixed;bottom: 0px;z-index: 9000;width: 100%;background-color: #000;color: #0d930d;padding: .5rem 1rem;font-weight: bold;margin: 0px;left: 0;right: 0px;';
        echo '<p style="' . $style . '">' . static::metrics() . '</p>';
    }

    /**
     * Calculate and return the performance metrics.
     *
     * @return string Formatted performance metrics including execution time, memory usage, and number of files loaded.
     */
    public static function metrics(): string
    {
        $files = get_included_files();
        return sprintf(
            'Execution Time: %.4f seconds | Memory Usage: %.2f KB | Files Loaded: %d',
            (static::$endTime - static::$startTime),
            (static::$endMemory - static::$startMemory) / 1024,
            count($files)
        );
    }

    /**
     * Display a list of included files.
     *
     * This method fetches all included files, formats them into an HTML list,
     * and outputs the list directly to the browser.
     *
     * @return void
     */
    public static function whatIncluded(): void
    {
        $files = get_included_files();
        
        echo "<h2>List of Included Files</h2>";
        echo "<ul>";
        
        foreach ($files as $id => $file) {
            echo "<li>[" . ($id + 1) . "] " . htmlspecialchars($file) . "</li>";
        }
        
        echo "</ul>";
        
        exit;
    }
}