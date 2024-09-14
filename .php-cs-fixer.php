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
    ]);

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
    'header_comment' => [
        'header' => $header,
        'location' => 'after_open',
        'comment_type' => 'PHPDoc'
    ],
    'array_syntax' => ['syntax' => 'short'],
    'blank_line_after_opening_tag' => false,
    'assign_null_coalescing_to_coalesce_equal' => false,
    'braces' => [
        'allow_single_line_anonymous_class_with_empty_body' => true,
        'allow_single_line_closure' => true,
        'position_after_anonymous_constructs' => 'next',
        'position_after_control_structures' => 'next',
        'position_after_functions_and_oop_constructs' => 'next',
    ],
    'binary_operator_spaces' => [
        'default' => 'single_space'
    ],
    'whitespace_after_comma_in_array' => [
        'ensure_single_space' => true
    ],
    'not_operator_with_successor_space' => true,
    'method_chaining_indentation' => false,
    'phpdoc_indent' => true,
    'phpdoc_trim' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_superfluous_phpdoc_tags' => [
        'remove_inheritdoc' => false
    ],
    'phpdoc_add_missing_param_annotation' => [
        'only_untyped' => false
    ],
    'yoda_style' => [
        'always_move_variable' => true
    ]
])
->setFinder($finder)
->setIndent("    ")
->setLineEnding("\r\n");