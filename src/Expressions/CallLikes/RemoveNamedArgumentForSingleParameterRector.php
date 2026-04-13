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
            Node\Expr\CallLike::class,
        ];
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Removes named arguments for single-parameter function and method calls.',
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
     * @param Node\Expr\CallLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }

        $namedArguments = \array_filter($node->getArgs(), static function ($argument): bool {
            return $argument->name instanceof Node\Identifier;
        });

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

        $parameters = $parametersAcceptor->getParameters();

        if (\count($parameters) !== 1) {
            return null;
        }

        $parameter = $parameters[0];
        $parameterName = $parameter->getName();

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

        if (!$hasChanged) {
            return null;
        }

        return $node;
    }
}
