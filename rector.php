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
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;

return static function (RectorConfig $config): void {

    $config->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_80
    ]);

    $config->parallel();
    $config->bootstrapFiles([
        __DIR__ . '/bootstrap/constants.php',
        __DIR__ . '/bootstrap/functions.php'
    ]);

    //composer analyze-code
    $config->paths([
        __DIR__ . '/app',
        __DIR__ . '/system',
        __DIR__ . '/bootstrap', 
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/public',
    ]);

    $config->phpstanConfigs([
        __DIR__ . '/phpstan.neon'
    ]);

    $config->skip([
        __DIR__ . '/system/Functions/Func.php',
        __DIR__ . '/system/plugins',
        __DIR__ . '/builds',
        __DIR__ . '/dump',
        __DIR__ . '/samples',
        __DIR__ . '/libraries',
        JsonThrowOnErrorRector::class,
        YieldDataProviderRector::class,
        CountOnNullRector::class,
        RandomFunctionRector::class,
        SimplifyRegexPatternRector::class
    ]);

    //$config->importNames();
    //$config->removeUnusedImports();

    $config->rule(TypedPropertyFromStrictConstructorRector::class);
    $config->rule(SimplifyUselessVariableRector::class);
    $config->rule(RemoveAlwaysElseRector::class);
    $config->rule(CountArrayToEmptyArrayComparisonRector::class);
    $config->rule(ChangeNestedForeachIfsToEarlyContinueRector::class);
    $config->rule(ChangeIfElseValueAssignToEarlyReturnRector::class);
    $config->rule(SimplifyStrposLowerRector::class);
    $config->rule(CombineIfRector::class);
    $config->rule(SimplifyIfReturnBoolRector::class);
    $config->rule(InlineIfToExplicitIfRector::class);
    $config->rule(PreparedValueToEarlyReturnRector::class);
    $config->rule(ShortenElseIfRector::class);
    $config->rule(SimplifyIfElseToTernaryRector::class);
    $config->rule(UnusedForeachValueToArrayKeysRector::class);
    $config->rule(ChangeArrayPushToArrayAssignRector::class);
    $config->rule(UnnecessaryTernaryExpressionRector::class);
    $config->rule(SimplifyRegexPatternRector::class);
    $config->rule(FuncGetArgsToVariadicParamRector::class);
    $config->rule(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $config->rule(SimplifyEmptyArrayCheckRector::class);
    $config->rule(StringClassNameToClassConstantRector::class);
    $config->rule(PrivatizeFinalClassPropertyRector::class);
    $config->rule(CompleteDynamicPropertiesRector::class);
    $config->rule(UnwrapFutureCompatibleIfPhpVersionRector::class);
    $config->rule(UnwrapFutureCompatibleIfPhpVersionRector::class);
};