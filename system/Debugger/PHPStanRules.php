<?php
declare(strict_types=1);
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

use \PhpParser\Node;
use \PHPStan\Rules\Rule;
use \PhpParser\Node\Stmt;
use \PhpParser\Comment\Doc;
use \PHPStan\Analyser\Scope;
use \PhpParser\Node\Stmt\Use_;
use \PhpParser\Node\Stmt\Echo_;
use \PhpParser\Node\Expr\Eval_;
use \PhpParser\Node\Expr\Variable;
use \PhpParser\Node\Expr\Include_;

final class PHPStanRules implements Rule
{
    /**
     * @return string
     */
    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @param Node $node
     * @param Scope $scope
     * 
     * @return array
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        // Rule Use import instead of include/required
        if ($node instanceof Include_) {
            [$type, $func] = $this->getIncludeChecks($node);
            $errors[] = sprintf(
                'Avoid using `%s`; use the `\Luminova\Funcs\%s` helper function instead.',
                $type,
                $func
            );
        }

        // Rule Use statement should not appear before docblock license
         if ($node instanceof Stmt && ($comments = $node->getComments()) !== []) {
            foreach ($comments as $comment) {
                if (!$comment instanceof Doc) {
                    continue;
                }

                $previous = $node->getAttribute('previous');
                while ($previous) {
                    if ($previous instanceof Use_) {
                        $errors[] = 'Use statement must be located after license docblock.';
                        break;
                    }

                    $previous = $previous->getAttribute('previous');
                }
            }
        }

        // Rule Variable naming should be camelCase
        if ($node instanceof Variable && is_string($node->name)) {
            if (str_contains($node->name, '_')) {
                $errors[] = sprintf(
                    'Variable name "%s" contains underscores. Use camelCase instead.',
                    $node->name
                );
            }
        }

         // Rule Disallow echo usage inside class methods
        if ($node instanceof Echo_ && $scope->isInClass()) {
            $errors[] = 'Avoid using echo inside class methods. Return value or use Logger instead.';
        }

        // Rule Disallow eval()
        if ($node instanceof Eval_) {
            $errors[] = 'Avoid using eval(). It is insecure.';
        }

        return $errors;
    }

    private function getIncludeChecks(Node $node): array
    {
        return match ($node->type) {
            Include_::TYPE_INCLUDE => ['include', 'import(path: \'...\', require: false, once: false)'],
            Include_::TYPE_INCLUDE_ONCE => ['include_once', 'import(path: \'...\', require: false, once: true)'],
            Include_::TYPE_REQUIRE => ['require', 'import(path: \'...\', once: false)'],
            Include_::TYPE_REQUIRE_ONCE => ['require_once', 'import(path: \'...\', once: true)'],
            default => ['include/require', 'import(...)']
        };
    }
}