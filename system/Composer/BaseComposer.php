<?php
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 * @link https://luminova.ng
 */
namespace Luminova\Composer;

abstract class BaseComposer
{
    /**
     * @param int $totalSteps
     * @param callable $taskCallback
     * @param callable|null $onCompleteCallback
     * @param string $completionMessage
    */
    protected static function progress(int $totalSteps, callable $taskCallback, ?callable $onCompleteCallback = null, ?string $completionMessage = null): void
    {
        $results = [];
        
        for ($step = 1; $step <= $totalSteps; $step++) {
            $result = $taskCallback($step);
            $results[] = $result;
            $progressMessage = "Processing... Step $step/$totalSteps";
            echo "\r" . str_pad($progressMessage, 80, " ") . "\r";
            flush();
        }
        
        sleep(1);
        
        if($completionMessage !== null){
            echo "\033[32m$completionMessage\033[0m\n";
        }
        
        if ($onCompleteCallback !== null) {
            $onCompleteCallback($results[0], $results);
        }
    }


    protected static function parseLocation(string $path): string{
        if ($path != null ) {
            $path = ltrim($path, ".");
        }
        return $path;
    }

    protected static function isParentOrEqual(string $path1, string $path2): bool
    {
        $parts1 = explode('/', trim($path1, '/'));
        $parts2 = explode('/', trim($path2, '/'));
        $minLength = min(count($parts1), count($parts2));
        
        for ($i = 0; $i < $minLength; $i++) {
            if ($parts1[$i] !== $parts2[$i]) {
                return false;
            }
        }
        return true; 
    }
}