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
    private const SPECIAL_CONSTANT_NAMES = [
        'false',
        'null',
        'true',
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
            'Removes aliases from imports of classes, namespaces, functions, and constants when the imported name is not otherwise in use.',
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
        $useItemsWithImportedNames = self::useItemsWithImportedNames(
            $containerNode,
            Node\Stmt\Use_::TYPE_NORMAL,
        );

        $unavailableNames = [];

        foreach ($useItemsWithImportedNames as [$useItem]) {
            $unavailableNames[\strtolower($useItem->getAlias()->toString())] = true;
        }

        foreach (self::declaredNames($containerNode) as $declaredName) {
            $unavailableNames[\strtolower($declaredName)] = true;
        }

        $changedUseItemIds = [];

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

            $changedUseItemIds[] = \spl_object_id($useItem);

            unset($unavailableNames[\strtolower($alias)]);

            $unavailableNames[\strtolower($name)] = true;

            $hasChanged = true;
        }

        if ($this->processFunctionOrConstantImports($containerNode, Node\Stmt\Use_::TYPE_FUNCTION, $changedUseItemIds)) {
            $hasChanged = true;
        }

        if ($this->processFunctionOrConstantImports($containerNode, Node\Stmt\Use_::TYPE_CONSTANT, $changedUseItemIds)) {
            $hasChanged = true;
        }

        self::rebuildMixedGroupUsesWithChangedUseItems(
            $containerNode,
            $changedUseItemIds,
        );

        return $hasChanged;
    }

    /**
     * Replaces group use statements that import more than one kind of symbol and contain a changed use item with new
     * nodes, because the format-preserving printer would otherwise print the kind of the changed use item twice.
     *
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<int>                                    $changedUseItemIds
     */
    private static function rebuildMixedGroupUsesWithChangedUseItems(
        Node $containerNode,
        array $changedUseItemIds
    ): void {
        foreach ($containerNode->stmts as $index => $statement) {
            if (!$statement instanceof Node\Stmt\GroupUse) {
                continue;
            }

            if (Node\Stmt\Use_::TYPE_UNKNOWN !== $statement->type) {
                continue;
            }

            $hasChangedUseItem = false;

            foreach ($statement->uses as $useItem) {
                if (\in_array(\spl_object_id($useItem), $changedUseItemIds, true)) {
                    $hasChangedUseItem = true;
                }
            }

            if (!$hasChangedUseItem) {
                continue;
            }

            $containerNode->stmts[$index] = new Node\Stmt\GroupUse(
                new Node\Name($statement->prefix->toString()),
                \array_map(static function (Node\UseItem $useItem): Node\UseItem {
                    return new Node\UseItem(
                        new Node\Name($useItem->name->toString()),
                        $useItem->alias,
                        $useItem->type,
                    );
                }, $statement->uses),
                $statement->type,
            );
        }
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode                $containerNode
     * @param Node\Stmt\Use_::TYPE_CONSTANT|Node\Stmt\Use_::TYPE_FUNCTION $type
     * @param list<int>                                                   $changedUseItemIds
     */
    private function processFunctionOrConstantImports(
        Node $containerNode,
        int $type,
        array &$changedUseItemIds
    ): bool {
        $useItemsWithImportedNames = self::useItemsWithImportedNames(
            $containerNode,
            $type,
        );

        $unavailableNames = [];

        foreach ($useItemsWithImportedNames as [$useItem]) {
            $unavailableNames[self::normalizedName($useItem->getAlias()->toString(), $type)] = true;
        }

        foreach (self::declaredFunctionOrConstantNames($containerNode, $type) as $declaredName) {
            $unavailableNames[self::normalizedName($declaredName, $type)] = true;
        }

        foreach (self::unqualifiedFunctionOrConstantNamesNotResolvedThroughImports($containerNode, $type) as $referencedName) {
            $unavailableNames[self::normalizedName($referencedName, $type)] = true;
        }

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

            if (
                Node\Stmt\Use_::TYPE_CONSTANT === $type
                && \in_array(\strtolower($name), self::SPECIAL_CONSTANT_NAMES, true)
            ) {
                continue;
            }

            if (
                self::normalizedName($name, $type) !== self::normalizedName($alias, $type)
                && \array_key_exists(self::normalizedName($name, $type), $unavailableNames)
            ) {
                continue;
            }

            $this->traverseNodesWithCallable(self::statementsWithoutImports($containerNode), static function (Node $node) use ($alias, $name, $type): ?Node {
                if (
                    Node\Stmt\Use_::TYPE_FUNCTION === $type
                    && !$node instanceof Node\Expr\FuncCall
                ) {
                    return null;
                }

                if (
                    Node\Stmt\Use_::TYPE_CONSTANT === $type
                    && !$node instanceof Node\Expr\ConstFetch
                ) {
                    return null;
                }

                /** @var Node\Expr\ConstFetch|Node\Expr\FuncCall $node */
                if (!$node->name instanceof Node\Name\FullyQualified) {
                    return null;
                }

                $originalName = $node->name->getAttribute('originalName');

                if (!$originalName instanceof Node\Name) {
                    return null;
                }

                if (!$originalName->isUnqualified()) {
                    return null;
                }

                if (self::normalizedName($originalName->toString(), $type) !== self::normalizedName($alias, $type)) {
                    return null;
                }

                $node->name = new Node\Name($name);

                return $node;
            });

            $useItem->alias = null;

            $changedUseItemIds[] = \spl_object_id($useItem);

            unset($unavailableNames[self::normalizedName($alias, $type)]);

            $unavailableNames[self::normalizedName($name, $type)] = true;

            $hasChanged = true;
        }

        return $hasChanged;
    }

    /**
     * Function names are case-insensitive, constant names are case-sensitive.
     *
     * @param Node\Stmt\Use_::TYPE_CONSTANT|Node\Stmt\Use_::TYPE_FUNCTION $type
     */
    private static function normalizedName(
        string $name,
        int $type
    ): string {
        if (Node\Stmt\Use_::TYPE_FUNCTION === $type) {
            return \strtolower($name);
        }

        return $name;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode                $containerNode
     * @param Node\Stmt\Use_::TYPE_CONSTANT|Node\Stmt\Use_::TYPE_FUNCTION $type
     *
     * @return list<string>
     */
    private static function declaredFunctionOrConstantNames(
        Node $containerNode,
        int $type
    ): array {
        $declaredNames = [];

        if (Node\Stmt\Use_::TYPE_FUNCTION === $type) {
            $nodeFinder = new NodeFinder();

            foreach ($nodeFinder->findInstanceOf(self::statementsWithoutImports($containerNode), Node\Stmt\Function_::class) as $function) {
                $declaredNames[] = $function->name->toString();
            }

            return $declaredNames;
        }

        foreach ($containerNode->stmts as $statement) {
            if (!$statement instanceof Node\Stmt\Const_) {
                continue;
            }

            foreach ($statement->consts as $const) {
                $declaredNames[] = $const->name->toString();
            }
        }

        return $declaredNames;
    }

    /**
     * Returns the names of unqualified function calls or constant fetches that PHP does not resolve through an import, and
     * that an import of a function or constant with the same name would capture.
     *
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode                $containerNode
     * @param Node\Stmt\Use_::TYPE_CONSTANT|Node\Stmt\Use_::TYPE_FUNCTION $type
     *
     * @return list<string>
     */
    private static function unqualifiedFunctionOrConstantNamesNotResolvedThroughImports(
        Node $containerNode,
        int $type
    ): array {
        $nodeFinder = new NodeFinder();

        $names = [];

        foreach ($nodeFinder->find(self::statementsWithoutImports($containerNode), static function (Node $node) use ($type): bool {
            if (Node\Stmt\Use_::TYPE_FUNCTION === $type) {
                return $node instanceof Node\Expr\FuncCall;
            }

            return $node instanceof Node\Expr\ConstFetch;
        }) as $node) {
            /** @var Node\Expr\ConstFetch|Node\Expr\FuncCall $node */
            if (!$node->name instanceof Node\Name) {
                continue;
            }

            if ($node->name instanceof Node\Name\FullyQualified) {
                continue;
            }

            if (!$node->name->isUnqualified()) {
                continue;
            }

            $names[] = $node->name->toString();
        }

        return $names;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param Node\Stmt\Use_::TYPE_*                       $type
     *
     * @return list<array{0: Node\UseItem, 1: string}>
     */
    private static function useItemsWithImportedNames(
        Node $containerNode,
        int $type
    ): array {
        $useItemsWithImportedNames = [];

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if ($type !== $statement->type) {
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
                    $type !== $statement->type
                    && $type !== $useItem->type
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
        $declaredNames = [];

        foreach ($containerNode->stmts as $statement) {
            if (!$statement instanceof Node\Stmt\ClassLike) {
                continue;
            }

            if (!$statement->name instanceof Node\Identifier) {
                continue;
            }

            $declaredNames[] = $statement->name->toString();
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
