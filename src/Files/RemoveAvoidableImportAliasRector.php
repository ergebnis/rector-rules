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

namespace Ergebnis\Rector\Rules\Files;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PHPStan\PhpDocParser\Ast;
use Rector\BetterPhpDocParser;
use Rector\Comments;
use Rector\PhpDocParser;
use Rector\PhpParser;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class RemoveAvoidableImportAliasRector extends Rector\AbstractRector
{
    private const REFERENCE_TAG_NAMES = [
        '@see',
        '@used-by',
        '@uses',
    ];

    /**
     * @see https://www.php.net/manual/en/reserved.other-reserved-words.php
     */
    private const RESERVED_NAMES = [
        'array',
        'bool',
        'callable',
        'false',
        'float',
        'int',
        'iterable',
        'mixed',
        'never',
        'null',
        'object',
        'parent',
        'self',
        'static',
        'string',
        'true',
        'void',
    ];
    private BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory;
    private Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater;

    public function __construct(
        BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory,
        Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Removes aliases from imports of classes and namespaces when the imported name is not otherwise in use.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
                        namespace App\Http;

                        use App\Controller as Foo;

                        final class Router
                        {
                            public function dashboard(): Foo\DashboardController
                            {
                            }
                        }
                        CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                        namespace App\Http;

                        use App\Controller;

                        final class Router
                        {
                            public function dashboard(): Controller\DashboardController
                            {
                            }
                        }
                        CODE_SAMPLE
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [
            PhpParser\Node\FileNode::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        /** @var PhpParser\Node\FileNode $node */
        $containerNodes = [];

        if ($node->isNamespaced()) {
            foreach ($node->stmts as $statement) {
                if ($statement instanceof Node\Stmt\Namespace_) {
                    $containerNodes[] = $statement;
                }
            }
        } else {
            $containerNodes[] = $node;
        }

        $hasChanged = false;

        foreach ($containerNodes as $containerNode) {
            if ($this->processContainerNode($containerNode)) {
                $hasChanged = true;
            }
        }

        if (!$hasChanged) {
            return null;
        }

        return $node;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private function processContainerNode(Node $containerNode): bool
    {
        $useItemsWithImportedNames = self::useItemsWithImportedNames($containerNode);

        $unavailableNames = [];

        foreach ($useItemsWithImportedNames as [$useItem]) {
            $unavailableNames[\strtolower($useItem->getAlias()->toString())] = true;
        }

        foreach (self::declaredNames($containerNode) as $declaredName) {
            $unavailableNames[\strtolower($declaredName)] = true;
        }

        $firstSegmentsOfRelativeReferences = \array_merge(
            self::firstSegmentsOfRelativeNames($containerNode),
            $this->firstSegmentsOfRelativeNamesInDocBlocks($containerNode),
        );

        $hasChanged = false;

        foreach ($useItemsWithImportedNames as [$useItem, $importedName]) {
            if (null === $useItem->alias) {
                continue;
            }

            $alias = $useItem->alias->toString();

            $importedNameSegments = \explode(
                '\\',
                $importedName,
            );

            $name = \end($importedNameSegments);

            if (\in_array(\strtolower($name), self::RESERVED_NAMES, true)) {
                continue;
            }

            if (\strtolower($name) !== \strtolower($alias)) {
                if (\array_key_exists(\strtolower($name), $unavailableNames)) {
                    continue;
                }

                if (\in_array(\strtolower($name), $firstSegmentsOfRelativeReferences, true)) {
                    continue;
                }
            }

            $this->renameFirstSegmentInNames(
                $containerNode,
                $alias,
                $name,
            );

            $this->renameFirstSegmentInDocBlocks(
                $containerNode,
                $alias,
                $name,
            );

            $useItem->alias = null;

            unset($unavailableNames[\strtolower($alias)]);

            $unavailableNames[\strtolower($name)] = true;

            $hasChanged = true;
        }

        return $hasChanged;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     *
     * @return list<array{0: Node\UseItem, 1: string}>
     */
    private static function useItemsWithImportedNames(Node $containerNode): array
    {
        $useItemsWithImportedNames = [];

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $useItem) {
                    $useItemsWithImportedNames[] = [
                        $useItem,
                        $useItem->name->toString(),
                    ];
                }

                continue;
            }

            if (!$statement instanceof Node\Stmt\GroupUse) {
                continue;
            }

            foreach ($statement->uses as $useItem) {
                if (
                    Node\Stmt\Use_::TYPE_NORMAL !== $statement->type
                    && Node\Stmt\Use_::TYPE_NORMAL !== $useItem->type
                ) {
                    continue;
                }

                $useItemsWithImportedNames[] = [
                    $useItem,
                    \sprintf(
                        '%s\\%s',
                        $statement->prefix->toString(),
                        $useItem->name->toString(),
                    ),
                ];
            }
        }

        return $useItemsWithImportedNames;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     *
     * @return list<string>
     */
    private static function declaredNames(Node $containerNode): array
    {
        $nodeFinder = new NodeFinder();

        $declaredNames = [];

        foreach ($nodeFinder->findInstanceOf(self::statementsWithoutImports($containerNode), Node\Stmt\ClassLike::class) as $classLike) {
            if (!$classLike->name instanceof Node\Identifier) {
                continue;
            }

            $declaredNames[] = $classLike->name->toString();
        }

        return $declaredNames;
    }

    /**
     * Returns the lower-cased first segments of names that PHP resolves through the import table for classes and
     * namespaces, as they are written in the source.
     *
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     *
     * @return list<string>
     */
    private static function firstSegmentsOfRelativeNames(Node $containerNode): array
    {
        $firstSegments = [];

        foreach (self::relativeNames($containerNode) as $originalName) {
            $firstSegments[] = \strtolower($originalName->getFirst());
        }

        return \array_values(\array_unique($firstSegments));
    }

    /**
     * Returns the names, as they are written in the source, that PHP resolves through the import table for classes and
     * namespaces, keyed by the object identifier of the resolved name.
     *
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     *
     * @return array<int, Node\Name>
     */
    private static function relativeNames(Node $containerNode): array
    {
        $statements = self::statementsWithoutImports($containerNode);

        $nodeFinder = new NodeFinder();

        $namesOfFunctionsAndConstants = [];

        foreach ($nodeFinder->find($statements, static function (Node $node): bool {
            return $node instanceof Node\Expr\ConstFetch
                || $node instanceof Node\Expr\FuncCall;
        }) as $node) {
            /** @var Node\Expr\ConstFetch|Node\Expr\FuncCall $node */
            if ($node->name instanceof Node\Name) {
                $namesOfFunctionsAndConstants[\spl_object_id($node->name)] = true;
            }
        }

        $relativeNames = [];

        foreach ($nodeFinder->findInstanceOf($statements, Node\Name\FullyQualified::class) as $name) {
            $originalName = $name->getAttribute('originalName');

            if (!$originalName instanceof Node\Name) {
                continue;
            }

            if ($originalName instanceof Node\Name\FullyQualified) {
                continue;
            }

            if ($originalName instanceof Node\Name\Relative) {
                continue;
            }

            if (
                \array_key_exists(\spl_object_id($name), $namesOfFunctionsAndConstants)
                && $originalName->isUnqualified()
            ) {
                continue;
            }

            $relativeNames[\spl_object_id($name)] = $originalName;
        }

        return $relativeNames;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     *
     * @return list<string>
     */
    private function firstSegmentsOfRelativeNamesInDocBlocks(Node $containerNode): array
    {
        $firstSegments = [];

        $this->traverseNodesWithCallable(self::statementsWithoutImports($containerNode), function (Node $node) use (&$firstSegments): ?Node {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return null;
            }

            $phpDocNodeTraverser = new Ast\NodeTraverser([
                new PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor(static function (Ast\Node $phpDocNode) use (&$firstSegments): ?Ast\Node {
                    $reference = self::referenceFromTag($phpDocNode);

                    if (null !== $reference) {
                        $nameSegments = \explode(
                            '\\',
                            $reference,
                        );

                        $firstSegments[] = \strtolower($nameSegments[0]);

                        return null;
                    }

                    if (!$phpDocNode instanceof Ast\Type\IdentifierTypeNode) {
                        return null;
                    }

                    if ($phpDocNode instanceof BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode) {
                        return null;
                    }

                    if (\strpos($phpDocNode->name, '\\') === 0) {
                        return null;
                    }

                    $nameSegments = \explode(
                        '\\',
                        $phpDocNode->name,
                    );

                    $firstSegments[] = \strtolower($nameSegments[0]);

                    return null;
                }),
            ]);

            $phpDocNodeTraverser->traverse([
                $phpDocInfo->getPhpDocNode(),
            ]);

            return null;
        });

        return \array_values(\array_unique($firstSegments));
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private function renameFirstSegmentInNames(
        Node $containerNode,
        string $alias,
        string $name
    ): void {
        $relativeNames = self::relativeNames($containerNode);

        $this->traverseNodesWithCallable(self::statementsWithoutImports($containerNode), static function (Node $node) use ($alias, $name, $relativeNames): ?Node {
            if (!$node instanceof Node\Name\FullyQualified) {
                return null;
            }

            if (!\array_key_exists(\spl_object_id($node), $relativeNames)) {
                return null;
            }

            $originalName = $relativeNames[\spl_object_id($node)];

            if (\strtolower($originalName->getFirst()) !== \strtolower($alias)) {
                return null;
            }

            $nameSegments = $originalName->getParts();

            $nameSegments[0] = $name;

            return new Node\Name(\implode(
                '\\',
                $nameSegments,
            ));
        });
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private function renameFirstSegmentInDocBlocks(
        Node $containerNode,
        string $alias,
        string $name
    ): void {
        $this->traverseNodesWithCallable(self::statementsWithoutImports($containerNode), function (Node $node) use ($alias, $name): ?Node {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return null;
            }

            $hasChanged = false;

            $phpDocNodeTraverser = new Ast\NodeTraverser([
                new PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor(static function (Ast\Node $phpDocNode) use ($alias, $name, &$hasChanged): ?Ast\Node {
                    $reference = self::referenceFromTag($phpDocNode);

                    if (null !== $reference) {
                        /** @var Ast\PhpDoc\PhpDocTagNode $phpDocNode */
                        $nameSegments = \explode(
                            '\\',
                            $reference,
                        );

                        if (\strtolower($nameSegments[0]) !== \strtolower($alias)) {
                            return null;
                        }

                        $nameSegments[0] = $name;

                        /** @var Ast\PhpDoc\GenericTagValueNode $value */
                        $value = $phpDocNode->value;

                        $hasChanged = true;

                        return new Ast\PhpDoc\PhpDocTagNode(
                            $phpDocNode->name,
                            new Ast\PhpDoc\GenericTagValueNode(\implode(
                                '\\',
                                $nameSegments,
                            ) . \substr($value->value, \strlen($reference))),
                        );
                    }

                    if (!$phpDocNode instanceof Ast\Type\IdentifierTypeNode) {
                        return null;
                    }

                    if ($phpDocNode instanceof BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode) {
                        return null;
                    }

                    if (\strpos($phpDocNode->name, '\\') === 0) {
                        return null;
                    }

                    $nameSegments = \explode(
                        '\\',
                        $phpDocNode->name,
                    );

                    if (\strtolower($nameSegments[0]) !== \strtolower($alias)) {
                        return null;
                    }

                    $nameSegments[0] = $name;

                    $hasChanged = true;

                    return new Ast\Type\IdentifierTypeNode(\implode(
                        '\\',
                        $nameSegments,
                    ));
                }),
            ]);

            $phpDocNodeTraverser->traverse([
                $phpDocInfo->getPhpDocNode(),
            ]);

            if ($hasChanged) {
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            }

            return null;
        });
    }

    /**
     * Returns the class reference at the start of the value of a @see, @used-by, or @uses tag, without a method, property, or constant
     * reference, or null when the node is no such tag or its value does not start with a name that PHP would resolve through
     * the import table for classes and namespaces.
     */
    private static function referenceFromTag(Ast\Node $phpDocNode): ?string
    {
        if (!$phpDocNode instanceof Ast\PhpDoc\PhpDocTagNode) {
            return null;
        }

        if (!\in_array($phpDocNode->name, self::REFERENCE_TAG_NAMES, true)) {
            return null;
        }

        if (!$phpDocNode->value instanceof Ast\PhpDoc\GenericTagValueNode) {
            return null;
        }

        if (1 !== \preg_match('/^[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*(\\\\[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)*/', $phpDocNode->value->value, $matches)) {
            return null;
        }

        return $matches[0];
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     *
     * @return list<Node\Stmt>
     */
    private static function statementsWithoutImports(Node $containerNode): array
    {
        $statements = [];

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                continue;
            }

            if ($statement instanceof Node\Stmt\GroupUse) {
                continue;
            }

            $statements[] = $statement;
        }

        return $statements;
    }
}
