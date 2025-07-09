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
namespace Luminova\Debugger; 

use function \Luminova\Funcs\get_content;

final class Tracer
{
    /**
     * Creates a syntax-highlighted version of a PHP file.
     * 
     * @param string $file File to highlight.
     * @param int $line Line number.
     * @param int $lines Maximum number of lines.
     * 
     * @return bool|string Return html highlight of the passed file.
     */
    public static function highlight(string $file, int $line, int $lines = 15): bool|string
    {
        if ($file === '' || ! is_readable($file)) {
            return false;
        }

        // Set our highlight colors:
        self::highlightColor();
        $source = get_content($file);

        if($source === false){
            return false;
        }
          
        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $source = explode("\n", highlight_string($source, true));
        $source = str_replace('<br />', "\n", $source[1]);
        $source = explode("\n", str_replace("\r\n", "\n", $source));

        // Get just the part to show
        $start = max($line - (int) round($lines / 2), 0);
        $source = array_splice($source, $start, $lines, true);
        $format = '% ' . strlen((string) ($start + $lines)) . 'd';

        $code = '';
        $spans = 0;

        foreach ($source as $index => $row) {
            $spans += substr_count($row, '<span') - substr_count($row, '</span');
            $row = str_replace(["\r", "\n"], ['', ''], $row);
            $entry = ($index + $start + 1);

            if ($entry === $line) {
                preg_match_all('#<[^>]+>#', $row, $tags);

                $code .= sprintf(
                    "<span class=\"line highlight\"><span class=\"number\">{$format}</span> %s\n</span>%s",
                    $entry,
                    strip_tags($row),
                    implode('', $tags[0])
                );
            } else {
                $code .= sprintf(
                    "<span class=\"line\"><span class=\"number\">{$format}</span> %s\n",
                    $entry, 
                    $row
                );
                $spans++;
            }
        }

        if ($spans > 0) {
            $code .= str_repeat('</span>', $spans);
        }

        return '<pre><code>' . $code . '</code></pre>';
    }

    /**
     * Initialize ini_set highlight.
     * 
     * @return void
     */
    private static function highlightColor(): void
    {
        if (function_exists('ini_set')) {
            ini_set('highlight.comment', '#767a7e; font-style: italic');
            ini_set('highlight.default', '#c7c7c7');
            ini_set('highlight.html', '#06B');
            ini_set('highlight.keyword', '#f1ce61;');
            ini_set('highlight.string', '#869d6a');
        }
    }
}