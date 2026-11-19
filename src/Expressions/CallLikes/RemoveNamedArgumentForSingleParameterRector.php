<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/rector-rules
 */

namespace Ergebnis\Rector\Rules\Expressions\CallLikes;

use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use Rector\NodeTypeResolver;
use Rector\Rector;
use Rector\Reflection as RectorReflection;
use Symplify\RuleDocGenerator;

final class RemoveNamedArgumentForSingleParameterRector extends Rector\AbstractRector
{
    private RectorReflection\ReflectionResolver $reflectionResolver;

    public function __construct(RectorReflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Attribute::class,
            Node\Expr\CallLike::class,
        ];
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Removes named arguments for single-parameter function calls, method calls, and attributes.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
                        strlen(string: 'hello');
                        CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                        strlen('hello');
                        CODE_SAMPLE
                ),
            ],
        );
    }

    /**
     * @param Node\Attribute|Node\Expr\CallLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Node\Attribute) {
            return $this->refactorAttribute($node);
        }

        if ($node->isFirstClassCallable()) {
            return null;
        }

        $namedArguments = self::namedArguments($node->getArgs());

        if (\count($namedArguments) !== 1) {
            return null;
        }

        $scope = $node->getAttribute(NodeTypeResolver\Node\AttributeKey::SCOPE);

        if (!$scope instanceof Analyser\Scope) {
            return null;
        }

        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);

        if (
            !$reflection instanceof Reflection\FunctionReflection
            && !$reflection instanceof Reflection\MethodReflection
        ) {
            return null;
        }

        $parametersAcceptor = NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper::select(
            $reflection,
            $node,
            $scope,
        );

        if (!self::removeNamedArgument($namedArguments, $parametersAcceptor->getParameters())) {
            return null;
        }

        return $node;
    }

    private function refactorAttribute(Node\Attribute $attribute): ?Node\Attribute
    {
        $namedArguments = self::namedArguments($attribute->args);

        if (\count($namedArguments) !== 1) {
            return null;
        }

        $reflection = $this->reflectionResolver->resolveConstructorReflectionFromAttribute($attribute);

        if (!$reflection instanceof Reflection\MethodReflection) {
            return null;
        }

        $parametersAcceptor = Reflection\ParametersAcceptorSelector::combineAcceptors($reflection->getVariants());

        if (!self::removeNamedArgument($namedArguments, $parametersAcceptor->getParameters())) {
            return null;
        }

        return $attribute;
    }

    /**
     * @param array<array-key, Node\Arg|Node\VariadicPlaceholder> $arguments
     *
     * @return list<Node\Arg>
     */
    private static function namedArguments(array $arguments): array
    {
        $namedArguments = [];

        foreach ($arguments as $argument) {
            if (!$argument instanceof Node\Arg) {
                continue;
            }

            if (!$argument->name instanceof Node\Identifier) {
                continue;
            }

            $namedArguments[] = $argument;
        }

        return $namedArguments;
    }

    /**
     * @param list<Node\Arg>                       $namedArguments
     * @param list<Reflection\ParameterReflection> $parameters
     */
    private static function removeNamedArgument(
        array $namedArguments,
        array $parameters
    ): bool {
        if (\count($parameters) !== 1) {
            return false;
        }

        $parameterName = $parameters[0]->getName();

        $hasChanged = false;

        foreach ($namedArguments as $namedArgument) {
            if (!$namedArgument->name instanceof Node\Identifier) {
                continue;
            }

            if ($namedArgument->name->toString() !== $parameterName) {
                continue;
            }

            $namedArgument->name = null;

            $hasChanged = true;
        }

        return $hasChanged;
    }
}
