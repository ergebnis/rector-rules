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

namespace Ergebnis\Rector\Rules\Traits;

use PhpParser\Node;
use PHPStan\Reflection;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class RemoveUnusedTraitUseRector extends Rector\AbstractRector
{
    private Reflection\ReflectionProvider $reflectionProvider;

    public function __construct(Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
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
            'Removes unused trait use statements from final classes.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
final class Example
{
    use SomeTrait;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Example
{
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
        if (!$node->isFinal()) {
            return null;
        }

        if ($node->isAnonymous()) {
            return null;
        }

        $calledMethodNames = $this->collectCalledMethodNames($node);
        $accessedPropertyNames = $this->collectAccessedPropertyNames($node);
        $referencedConstantNames = $this->collectReferencedConstantNames($node);
        $parentAbstractMethodNames = $this->collectParentAbstractMethodNames($node);
        $interfaceMethodNames = $this->collectInterfaceMethodNames($node);

        $changed = false;

        foreach ($node->stmts as $key => $statement) {
            if (!$statement instanceof Node\Stmt\TraitUse) {
                continue;
            }

            if (\count($statement->adaptations) > 0) {
                continue;
            }

            $unusedTraitKeys = [];

            foreach ($statement->traits as $traitKey => $traitName) {
                $traitClassName = $traitName->toString();

                if (!$this->reflectionProvider->hasClass($traitClassName)) {
                    continue;
                }

                $traitReflection = $this->reflectionProvider->getClass($traitClassName);
                $nativeReflection = $traitReflection->getNativeReflection();

                if (self::isTraitUsed(
                    $nativeReflection,
                    $calledMethodNames,
                    $accessedPropertyNames,
                    $referencedConstantNames,
                    $parentAbstractMethodNames,
                    $interfaceMethodNames,
                )) {
                    continue;
                }

                $unusedTraitKeys[] = $traitKey;
            }

            if (\count($unusedTraitKeys) === 0) {
                continue;
            }

            if (\count($unusedTraitKeys) === \count($statement->traits)) {
                unset($node->stmts[$key]);

                $changed = true;

                continue;
            }

            foreach ($unusedTraitKeys as $traitKey) {
                unset($statement->traits[$traitKey]);
            }

            $statement->traits = \array_values($statement->traits);

            $changed = true;
        }

        if (!$changed) {
            return null;
        }

        $node->stmts = \array_values($node->stmts);

        return $node;
    }

    /**
     * @return list<string>
     */
    private function collectCalledMethodNames(Node\Stmt\Class_ $node): array
    {
        $names = [];

        $this->traverseNodesWithCallable($node->stmts, static function (Node $node) use (&$names): ?Node {
            if (!$node instanceof Node\Expr\MethodCall) {
                return null;
            }

            if (!$node->var instanceof Node\Expr\Variable) {
                return null;
            }

            if ('this' !== $node->var->name) {
                return null;
            }

            if (!$node->name instanceof Node\Identifier) {
                return null;
            }

            $names[] = $node->name->name;

            return null;
        });

        return $names;
    }

    /**
     * @return list<string>
     */
    private function collectAccessedPropertyNames(Node\Stmt\Class_ $node): array
    {
        $names = [];

        $this->traverseNodesWithCallable($node->stmts, static function (Node $node) use (&$names): ?Node {
            if (!$node instanceof Node\Expr\PropertyFetch) {
                return null;
            }

            if (!$node->var instanceof Node\Expr\Variable) {
                return null;
            }

            if ('this' !== $node->var->name) {
                return null;
            }

            if (!$node->name instanceof Node\Identifier) {
                return null;
            }

            $names[] = $node->name->name;

            return null;
        });

        return $names;
    }

    /**
     * @return list<string>
     */
    private function collectReferencedConstantNames(Node\Stmt\Class_ $node): array
    {
        $names = [];

        $this->traverseNodesWithCallable($node->stmts, static function (Node $node) use (&$names): ?Node {
            if (!$node instanceof Node\Expr\ClassConstFetch) {
                return null;
            }

            if (!$node->class instanceof Node\Name) {
                return null;
            }

            $className = $node->class->toString();

            if ('self' !== $className && 'static' !== $className) {
                return null;
            }

            if (!$node->name instanceof Node\Identifier) {
                return null;
            }

            $names[] = $node->name->name;

            return null;
        });

        return $names;
    }

    /**
     * @return list<string>
     */
    private function collectParentAbstractMethodNames(Node\Stmt\Class_ $node): array
    {
        $names = [];

        if (!$node->extends instanceof Node\Name) {
            return $names;
        }

        $parentClassName = $node->extends->toString();

        if (!$this->reflectionProvider->hasClass($parentClassName)) {
            return $names;
        }

        $classReflection = $this->reflectionProvider->getClass($parentClassName);

        while (true) {
            $nativeReflection = $classReflection->getNativeReflection();

            foreach ($nativeReflection->getMethods() as $method) {
                if ($method->isAbstract()) {
                    $names[] = $method->getName();
                }
            }

            $parentClass = $classReflection->getParentClass();

            if (!$parentClass instanceof Reflection\ClassReflection) {
                break;
            }

            $classReflection = $parentClass;
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private function collectInterfaceMethodNames(Node\Stmt\Class_ $node): array
    {
        $names = [];

        foreach ($node->implements as $interfaceName) {
            $interfaceClassName = $interfaceName->toString();

            if (!$this->reflectionProvider->hasClass($interfaceClassName)) {
                continue;
            }

            $interfaceReflection = $this->reflectionProvider->getClass($interfaceClassName);
            $nativeReflection = $interfaceReflection->getNativeReflection();

            foreach ($nativeReflection->getMethods() as $method) {
                $names[] = $method->getName();
            }
        }

        return $names;
    }

    /**
     * @param \ReflectionClass<object> $nativeReflection
     * @param list<string>             $calledMethodNames
     * @param list<string>             $accessedPropertyNames
     * @param list<string>             $referencedConstantNames
     * @param list<string>             $parentAbstractMethodNames
     * @param list<string>             $interfaceMethodNames
     */
    private static function isTraitUsed(
        \ReflectionClass $nativeReflection,
        array $calledMethodNames,
        array $accessedPropertyNames,
        array $referencedConstantNames,
        array $parentAbstractMethodNames,
        array $interfaceMethodNames
    ): bool {
        foreach ($nativeReflection->getMethods() as $method) {
            $methodName = $method->getName();

            if (\strncmp($methodName, '__', 2) === 0) {
                continue;
            }

            if ($method->isPublic()) {
                return true;
            }

            if (\in_array($methodName, $calledMethodNames, true)) {
                return true;
            }

            if (\in_array($methodName, $parentAbstractMethodNames, true)) {
                return true;
            }

            if (\in_array($methodName, $interfaceMethodNames, true)) {
                return true;
            }
        }

        foreach ($nativeReflection->getProperties() as $property) {
            if (\in_array($property->getName(), $accessedPropertyNames, true)) {
                return true;
            }
        }

        foreach ($nativeReflection->getReflectionConstants() as $constant) {
            if (\in_array($constant->getName(), $referencedConstantNames, true)) {
                return true;
            }
        }

        return false;
    }
}
