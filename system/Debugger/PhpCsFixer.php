<?php
declare(strict_types=1);
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace Luminova\Debugger; 

use \PhpCsFixer\Finder;
use \PhpCsFixer\Config;
use \PhpCsFixer\ConfigInterface;

final class PhpCsFixer 
{
    /**
     * Instance of finder.
     * 
     * @var Finder|null $finder
     */
    private static ?Finder $finder = null;

    /**
     * Configuration options.
     * 
     * @var array<string,mixed> $configs
     */
    private array $configs = [
        '@PSR12' => true,
        'full_opening_tag' => true,
        'linebreak_after_opening_tag' => true,
        'blank_line_after_opening_tag' => false,
        'no_closing_tag' => true,
        'assign_null_coalescing_to_coalesce_equal' => false,
        'not_operator_with_successor_space' => false,
        'method_chaining_indentation' => false,
        'single_blank_line_at_eof' => true, 
        'phpdoc_indent' => true,
        'phpdoc_trim' => true,
        'single_quote' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_trailing_whitespace_in_comment' => true,
        'single_line_comment_spacing' => true,
        'semicolon_after_instruction' => false,
        'no_singleline_whitespace_before_semicolons' => true,
        'blank_line_after_namespace' => true,
        'no_leading_namespace_whitespace' => true,
        'clean_namespace' => true,
        'no_unused_imports' => true,
        'attribute_empty_parentheses' => false,
        'elseif' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'control_structure_braces' => true,
        'header_comment' => [
            'location' => 'after_declare_strict',
            'comment_type' => 'PHPDoc',
            'separate' => 'bottom'
        ],
        'braces' => [
            'allow_single_line_anonymous_class_with_empty_body' => true,
            'allow_single_line_closure' => true,
            'position_after_anonymous_constructs' => 'next',
            'position_after_control_structures' => 'next',
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'blank_lines_before_namespace' => [
            'min_line_breaks' => 1, 
            'max_line_breaks' => 1
        ],
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal'
        ],
        'whitespace_after_comma_in_array' => [
            'ensure_single_space' => true
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'no_superfluous_phpdoc_tags' => [
            'remove_inheritdoc' => false
        ],
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => false
        ],
        'yoda_style' => [
            'equal' => false, 
            'identical' => false, 
            'less_and_greater' => null
        ]
    ];

    /**
     * Perform fix for developer's project.
     * 
     * @var int FIX_PROJECT
     */
    public const FIX_PROJECT = 0;

    /**
     * Perform fix for framework's code.
     * 
     * @var int FIX_FRAMEWORK
     */
    public const FIX_FRAMEWORK = 1;

    /**
     * Initializes the php coding standard fixer. 
     * 
     * @param string $root Project root directory (e.g, `__DIR__`).
     * @param int $fix Weather running fix for project or framework (default: `self::FIX_PROJECT`).
     */
    public function __construct(string $root, int $fix = self::FIX_PROJECT)
    {
        self::$finder ??= Finder::create()
            ->in(($fix === self::FIX_PROJECT)
                ? [$root . '/src', $root . '/install']
                : [$root . '/app']
            )
            ->name('*.php')
            ->notPath([
                ($fix === self::FIX_PROJECT)  ? 'plugins' : 'Config',
            ])
            ->ignoreDotFiles(true)
            ->ignoreVCSIgnored(true)
            ->ignoreVCS(true);
    }

    public function setConfig(string $key, mixed $value): self 
    {
        // Can't change predefined configs
        if(isset($this->configs[$key])){
            return $this;
        }

        $this->configs[$key] = $value;
        return $this;
    }

    public function setHeaderComment(string $comment): self 
    {
        $this->configs['header_comment']['header'] = $comment;
        return $this;
    }

    /**
     * Get the PHP-CS-Fixer configuration rules.
     *
     * This method creates and returns a ConfigInterface object with the
     * predefined rules, finder, indentation, and line ending settings.
     *
     * @return ConfigInterface The configured PHP-CS-Fixer Config object.
     */
    public function getRules(): ConfigInterface 
    {
        return (new Config())->setRules($this->configs)
            ->setFinder(self::$finder)
            ->setIndent("    ")
            ->setLineEnding("\r\n");
    }
}