<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_80
    ]);

    $rectorConfig->parallel();
    /*$rectorConfig->bootstrapFiles([
        __DIR__ . '/system/Test/bootstrap.php',
    ]);*/

    //composer analyze-code
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/system',
        __DIR__ . '/bootstrap', 
        __DIR__ . '/resources',
        __DIR__ . '/routes',
       // __DIR__ . '/libraries',
        __DIR__ . '/public',
    ]);

    $rectorConfig->phpstanConfigs([
        __DIR__ . '/phpstan.neon'
    ]);

    $rectorConfig->skip([
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

    //$rectorConfig->importNames();
    //$rectorConfig->removeUnusedImports();

    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);
    $rectorConfig->rule(SimplifyUselessVariableRector::class);
    $rectorConfig->rule(RemoveAlwaysElseRector::class);
    $rectorConfig->rule(CountArrayToEmptyArrayComparisonRector::class);
    $rectorConfig->rule(ChangeNestedForeachIfsToEarlyContinueRector::class);
    $rectorConfig->rule(ChangeIfElseValueAssignToEarlyReturnRector::class);
    $rectorConfig->rule(SimplifyStrposLowerRector::class);
    $rectorConfig->rule(CombineIfRector::class);
    $rectorConfig->rule(SimplifyIfReturnBoolRector::class);
    $rectorConfig->rule(InlineIfToExplicitIfRector::class);
    $rectorConfig->rule(PreparedValueToEarlyReturnRector::class);
    $rectorConfig->rule(ShortenElseIfRector::class);
    $rectorConfig->rule(SimplifyIfElseToTernaryRector::class);
    $rectorConfig->rule(UnusedForeachValueToArrayKeysRector::class);
    $rectorConfig->rule(ChangeArrayPushToArrayAssignRector::class);
    $rectorConfig->rule(UnnecessaryTernaryExpressionRector::class);
    $rectorConfig->rule(SimplifyRegexPatternRector::class);
    $rectorConfig->rule(FuncGetArgsToVariadicParamRector::class);
    $rectorConfig->rule(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $rectorConfig->rule(SimplifyEmptyArrayCheckRector::class);
    $rectorConfig->rule(StringClassNameToClassConstantRector::class);
    $rectorConfig->rule(PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(CompleteDynamicPropertiesRector::class);
    $rectorConfig->rule(UnwrapFutureCompatibleIfPhpVersionRector::class);
    $rectorConfig->rule(UnwrapFutureCompatibleIfPhpVersionRector::class);
};
