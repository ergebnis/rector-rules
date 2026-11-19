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

namespace Ergebnis\Rector\Rules\PHPUnit;

use PhpParser\Node;
use Rector\PHPUnit;
use Rector\Rector;
use Symplify\RuleDocGenerator;

/**
 * @see https://github.com/rectorphp/rector-phpunit/blob/1.1.0/rules/CodeQuality/Rector/ClassMethod/ReplaceTestAnnotationWithPrefixedFunctionRector.php
 */
final class ReplaceTestAttributeWithTestPrefixRector extends Rector\AbstractRector
{
    private const DEPENDS_ATTRIBUTES = [
        'PHPUnit\\Framework\\Attributes\\Depends',
        'PHPUnit\\Framework\\Attributes\\DependsUsingDeepClone',
        'PHPUnit\\Framework\\Attributes\\DependsUsingShallowClone',
    ];
    private const TEST_ATTRIBUTE = 'PHPUnit\\Framework\\Attributes\\Test';
    private PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer;

    public function __construct(PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\Class_::class,
        ];
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Replaces #[Test] attributes with test method prefixes.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework;

final class SomeTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function onePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework;

final class SomeTest extends Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }
}
CODE_SAMPLE
                ),
            ],
        );
    }

    /**
     * @param Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        $declaredMethodNames = [];

        foreach ($node->getMethods() as $classMethod) {
            $declaredMethodNames[] = \strtolower($classMethod->name->toString());
        }

        $renamedMethodNames = [];

        foreach ($node->getMethods() as $classMethod) {
            $methodName = $classMethod->name->toString();

            if (\strncmp($methodName, 'test', 4) === 0) {
                continue;
            }

            if (!$this->hasTestAttribute($classMethod)) {
                continue;
            }

            $prefixedMethodName = \sprintf(
                'test%s',
                \ucfirst($methodName),
            );

            if (\in_array(\strtolower($prefixedMethodName), $declaredMethodNames, true)) {
                continue;
            }

            $this->removeTestAttribute($classMethod);

            $classMethod->name->name = $prefixedMethodName;

            $declaredMethodNames[] = \strtolower($prefixedMethodName);
            $renamedMethodNames[\strtolower($methodName)] = $prefixedMethodName;
        }

        if ([] === $renamedMethodNames) {
            return null;
        }

        foreach ($node->getMethods() as $classMethod) {
            $this->updateDependsAttributes(
                $classMethod,
                $renamedMethodNames,
            );
        }

        return $node;
    }

    /**
     * @param array<string, string> $renamedMethodNames
     */
    private function updateDependsAttributes(
        Node\Stmt\ClassMethod $classMethod,
        array $renamedMethodNames
    ): void {
        foreach ($classMethod->attrGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if (!$this->isDependsAttributeName($attribute->name)) {
                    continue;
                }

                if (\count($attribute->args) !== 1) {
                    continue;
                }

                $value = $attribute->args[0]->value;

                if (!$value instanceof Node\Scalar\String_) {
                    continue;
                }

                $referencedMethodName = \strtolower($value->value);

                if (!\array_key_exists($referencedMethodName, $renamedMethodNames)) {
                    continue;
                }

                $attribute->args[0]->value = new Node\Scalar\String_($renamedMethodNames[$referencedMethodName]);
            }
        }
    }

    private function isDependsAttributeName(Node\Name $name): bool
    {
        foreach (self::DEPENDS_ATTRIBUTES as $dependsAttribute) {
            if ($this->isName($name, $dependsAttribute)) {
                return true;
            }

            if (
                $name instanceof Node\Name\FullyQualified
                && $name->toString() === $dependsAttribute
            ) {
                return true;
            }
        }

        return false;
    }

    private function hasTestAttribute(Node\Stmt\ClassMethod $classMethod): bool
    {
        foreach ($classMethod->attrGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($this->isTestAttributeName($attribute->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isTestAttributeName(Node\Name $name): bool
    {
        if ($this->isName($name, self::TEST_ATTRIBUTE)) {
            return true;
        }

        if ($name instanceof Node\Name\FullyQualified) {
            return $name->toString() === self::TEST_ATTRIBUTE;
        }

        return false;
    }

    private function removeTestAttribute(Node\Stmt\ClassMethod $classMethod): void
    {
        $filtered = [];

        foreach ($classMethod->attrGroups as $attributeGroup) {
            $remainingAttributes = [];

            foreach ($attributeGroup->attrs as $attribute) {
                if (!$this->isTestAttributeName($attribute->name)) {
                    $remainingAttributes[] = $attribute;
                }
            }

            if ([] !== $remainingAttributes) {
                $attributeGroup->attrs = $remainingAttributes;

                $filtered[] = $attributeGroup;
            }
        }

        $classMethod->attrGroups = $filtered;
    }
}
