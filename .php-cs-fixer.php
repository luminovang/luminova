<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
declare(strict_types=1);
use \PhpCsFixer\Finder;
use \PhpCsFixer\Config;

$finder = Finder::create()
    ->in([
        __DIR__ . '/app'
    ])
    ->notPath([
        'Config',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$header = <<<HEADER
    My Application.

    This file is part of the "My Application" project.
    @package MyApplication
    @author Dav [or Dav's full name]
    @copyright (c) yyyy Dav
    @license MIT License (or any other license type)
    @link https://myapplication.example.com
HEADER;

return (new Config())->setRules([
    '@PSR12' => true,
    'blank_line_after_opening_tag' => false,
    'assign_null_coalescing_to_coalesce_equal' => false,
    'not_operator_with_successor_space' => false,
    'method_chaining_indentation' => false,
    'phpdoc_indent' => true,
    'phpdoc_trim' => true,
    'single_quote' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_unused_imports' => true,
    'header_comment' => [
        'header' => $header,
        'location' => 'after_open',
        'comment_type' => 'PHPDoc'
    ],
    'braces' => [
        'allow_single_line_anonymous_class_with_empty_body' => true,
        'allow_single_line_closure' => true,
        'position_after_anonymous_constructs' => 'next',
        'position_after_control_structures' => 'next',
        'position_after_functions_and_oop_constructs' => 'next',
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
        'always_move_variable' => false
    ]
])
->setFinder($finder)
->setIndent("    ")
->setLineEnding("\r\n");