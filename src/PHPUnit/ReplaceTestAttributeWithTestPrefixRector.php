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
    private const TEST_ATTRIBUTE = 'PHPUnit\\Framework\\Attributes\\Test';
    private PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer;

    public function __construct(PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\ClassMethod::class,
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
     * @param Node\Stmt\ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        if (\strncmp($node->name->toString(), 'test', 4) === 0) {
            return null;
        }

        if (!$this->hasTestAttribute($node)) {
            return null;
        }

        $this->removeTestAttribute($node);

        $node->name->name = \sprintf(
            'test%s',
            \ucfirst($node->name->name),
        );

        return $node;
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
