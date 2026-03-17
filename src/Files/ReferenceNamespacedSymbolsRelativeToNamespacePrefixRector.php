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
use Rector\Contract;
use Rector\PhpDocParser;
use Rector\PhpParser;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector extends Rector\AbstractRector implements Contract\Rector\ConfigurableRectorInterface
{
    private const CONFIGURATION_KEY_NAMESPACE_PREFIXES = 'namespacePrefixes';
    private BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory;
    private Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater;

    /**
     * @var list<NamespacePrefix>
     */
    private array $namespacePrefixes = [];

    public function __construct(
        BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory,
        Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }

    public function getNodeTypes(): array
    {
        return [
            PhpParser\Node\FileNode::class,
        ];
    }

    public function configure(array $configuration): void
    {
        $configurationKeys = [
            self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
        ];

        $unknownConfigurationKeys = \array_diff(
            \array_keys($configuration),
            $configurationKeys,
        );

        if (\count($unknownConfigurationKeys) > 0) {
            throw new \InvalidArgumentException(\sprintf(
                'Configuration contains unknown keys: "%s".',
                \implode('", "', $unknownConfigurationKeys),
            ));
        }

        $namespacePrefixes = [];

        if (\array_key_exists(self::CONFIGURATION_KEY_NAMESPACE_PREFIXES, $configuration)) {
            if (!\is_array($configuration[self::CONFIGURATION_KEY_NAMESPACE_PREFIXES])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be an array of strings.',
                    self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                ));
            }

            foreach ($configuration[self::CONFIGURATION_KEY_NAMESPACE_PREFIXES] as $value) {
                if (!\is_string($value)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be an array of strings.',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                    ));
                }

                try {
                    $namespacePrefix = NamespacePrefix::fromString($value);
                } catch (\InvalidArgumentException $exception) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be an array of strings where each string is a valid namespace with at least two segments, got "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                if ($namespacePrefix->namespaceSegmentCount() < 2) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be an array of strings where each string is a valid namespace with at least two segments, got "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                if (\array_key_exists($value, $namespacePrefixes)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be an array of unique strings, got duplicate "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                $namespacePrefixes[$value] = $namespacePrefix;
            }
        }

        $this->namespacePrefixes = \array_values($namespacePrefixes);
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Replace references to namespaced symbols whose fully-qualified name starts with a namespace prefix so they are relative to that prefix.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
use Foo\Bar;
use Foo\Bar\Baz\Qux;

new Bar\Baz\Qux\Quuz();
new Qux\Quuz\Grauply();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Foo\Bar\Baz;

new Baz\Qux\Quuz();
new Baz\Qux\Quuz\Grauply();
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES => [
                            'Foo\Bar\Baz',
                        ],
                    ],
                ),
            ],
        );
    }

    /**
     * @param PhpParser\Node\FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var PhpParser\Node\FileNode $node */
        if ($node->isNamespaced()) {
            $containerNode = $node->getNamespace();

            if (!$containerNode instanceof Node\Stmt\Namespace_) {
                return null;
            }
        } else {
            $containerNode = $node;
        }

        $changed = false;

        foreach ($this->namespacePrefixes as $namespacePrefix) {
            $otherNamespacePrefixes = [];

            foreach ($this->namespacePrefixes as $otherNamespacePrefix) {
                if ($namespacePrefix->isNamespacePrefixOf($otherNamespacePrefix)) {
                    $otherNamespacePrefixes[] = $otherNamespacePrefix;
                }
            }

            if ($this->processNamespacePrefix($containerNode, $namespacePrefix, $otherNamespacePrefixes)) {
                $changed = true;
            }
        }

        if (!$changed) {
            return null;
        }

        return $node;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $otherNamespacePrefixes
     */
    private function processNamespacePrefix(
        Node $containerNode,
        NamespacePrefix $namespacePrefix,
        array $otherNamespacePrefixes
    ): bool {
        /** @var array<string, Reference> $aliasesToReferences */
        $aliasesToReferences = [];

        $hasPrefixImport = false;

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL === $statement->type) {
                    foreach ($statement->uses as $use) {
                        $alias = $use->getAlias()->toString();

                        $reference = Reference::fromString($use->name->toString());

                        $aliasesToReferences[$alias] = $reference;

                        if ($reference->is($namespacePrefix)) {
                            $hasPrefixImport = true;
                        }
                    }
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                if (Node\Stmt\Use_::TYPE_NORMAL === $statement->type) {
                    $prefix = $statement->prefix->toString();

                    foreach ($statement->uses as $use) {
                        $alias = $use->getAlias()->toString();

                        $reference = Reference::fromString(\sprintf(
                            '%s\\%s',
                            $prefix,
                            $use->name->toString(),
                        ));

                        $aliasesToReferences[$alias] = $reference;

                        if ($reference->is($namespacePrefix)) {
                            $hasPrefixImport = true;
                        }
                    }
                }
            }
        }

        $hasDirectMatchingImports = self::hasMatchingImports(
            $containerNode,
            $namespacePrefix,
            $otherNamespacePrefixes,
        );

        $hasParentImport = self::hasParentImport(
            $containerNode,
            $namespacePrefix,
        );

        if (
            !$hasDirectMatchingImports
            && !$hasParentImport
            && !self::hasSourceWrittenFullyQualifiedReferencesMatchingPrefix($containerNode, $namespacePrefix, $otherNamespacePrefixes)
        ) {
            return false;
        }

        if (self::lastNamespaceSegmentOfNamespacePrefixCollidesWithExistingImport($containerNode, $namespacePrefix)) {
            return false;
        }

        if (self::lastNamespaceSegmentOfNamespacePrefixCollidesWithDeclaredSymbol($containerNode, $namespacePrefix)) {
            return false;
        }

        $statementsRewritten = $this->rewriteNamesInStatements(
            $containerNode,
            $namespacePrefix,
            $otherNamespacePrefixes,
        );

        $docBlocksRewritten = $this->rewriteNamesInDocBlocks(
            $containerNode,
            $aliasesToReferences,
            $namespacePrefix,
            $otherNamespacePrefixes,
        );

        if (
            !$hasDirectMatchingImports
            && !$statementsRewritten
            && !$docBlocksRewritten
        ) {
            return false;
        }

        self::removeMatchingImportsAndAddPrefixImport(
            $containerNode,
            $namespacePrefix,
            $hasPrefixImport,
            $otherNamespacePrefixes,
        );

        return true;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $otherNamespacePrefixes
     */
    private static function hasMatchingImports(
        Node $containerNode,
        NamespacePrefix $namespacePrefix,
        array $otherNamespacePrefixes
    ): bool {
        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                foreach ($statement->uses as $use) {
                    $reference = Reference::fromString($use->name->toString());

                    if (
                        !$reference->is($namespacePrefix)
                        && $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes)
                    ) {
                        return true;
                    }
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                $prefix = $statement->prefix->toString();

                foreach ($statement->uses as $use) {
                    $reference = Reference::fromString(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    ));

                    if (
                        !$reference->is($namespacePrefix)
                        && $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes)
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private static function hasParentImport(
        Node $containerNode,
        NamespacePrefix $namespacePrefix
    ): bool {
        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $use) {
                    $namespacePrefixFromUse = NamespacePrefix::fromString($use->name->toString());

                    if ($namespacePrefixFromUse->isNamespacePrefixOf($namespacePrefix)) {
                        return true;
                    }
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                $prefix = $statement->prefix->toString();

                foreach ($statement->uses as $use) {
                    $namespacePrefixFromUse = NamespacePrefix::fromString(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    ));

                    if ($namespacePrefixFromUse->isNamespacePrefixOf($namespacePrefix)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $otherNamespacePrefixes
     */
    private static function hasSourceWrittenFullyQualifiedReferencesMatchingPrefix(
        Node $containerNode,
        NamespacePrefix $namespacePrefix,
        array $otherNamespacePrefixes
    ): bool {
        $nodeFinder = new NodeFinder();

        $match = $nodeFinder->findFirst(
            $containerNode->stmts,
            static function (Node $node) use ($namespacePrefix, $otherNamespacePrefixes): bool {
                if (!$node instanceof Node\Name\FullyQualified) {
                    return false;
                }

                $originalName = $node->getAttribute('originalName');

                if (
                    $originalName instanceof Node\Name
                    && !$originalName instanceof Node\Name\FullyQualified
                ) {
                    return false;
                }

                $reference = Reference::fromString($node->toString());

                return $reference->isOrIsDeclaredInOneOf($namespacePrefix)
                    && !$reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes);
            },
        );

        if (null !== $match) {
            return true;
        }

        $docBlockPrefix = \sprintf(
            '\\%s\\',
            $namespacePrefix->toString(),
        );

        $matchInDocBlock = $nodeFinder->findFirst(
            $containerNode->stmts,
            static function (Node $node) use ($docBlockPrefix): bool {
                $docComment = $node->getDocComment();

                if (null === $docComment) {
                    return false;
                }

                return \strpos($docComment->getText(), $docBlockPrefix) !== false;
            },
        );

        return null !== $matchInDocBlock;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $otherNamespacePrefixes
     */
    private function rewriteNamesInStatements(
        Node $containerNode,
        NamespacePrefix $namespacePrefix,
        array $otherNamespacePrefixes
    ): bool {
        $lastNamespaceSegmentOfNamespacePrefix = $namespacePrefix->lastNamespaceSegment();

        $hasChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, static function (Node $node) use ($namespacePrefix, $lastNamespaceSegmentOfNamespacePrefix, $otherNamespacePrefixes, &$hasChanged): ?Node {
            if (!$node instanceof Node\Name\FullyQualified) {
                return null;
            }

            $reference = Reference::fromString($node->toString());

            if (
                !$reference->is($namespacePrefix)
                && !$reference->isDeclaredIn($namespacePrefix)
            ) {
                return null;
            }

            if ($reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes)) {
                return null;
            }

            $hasChanged = true;

            if ($reference->is($namespacePrefix)) {
                return new Node\Name($lastNamespaceSegmentOfNamespacePrefix->toString());
            }

            return new Node\Name(\sprintf(
                '%s\\%s',
                $lastNamespaceSegmentOfNamespacePrefix->toString(),
                $reference->relativeTo($namespacePrefix)->toString(),
            ));
        });

        return $hasChanged;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param array<string, Reference>                     $aliasesToReferences
     * @param list<NamespacePrefix>                        $otherNamespacePrefixes
     */
    private function rewriteNamesInDocBlocks(
        Node $containerNode,
        array $aliasesToReferences,
        NamespacePrefix $namespacePrefix,
        array $otherNamespacePrefixes
    ): bool {
        $anyDocBlockChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, function (Node $node) use (&$anyDocBlockChanged, $aliasesToReferences, $namespacePrefix, $otherNamespacePrefixes): ?Node {
            if ($node instanceof Node\Stmt\Use_) {
                return null;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return null;
            }

            $hasChanged = false;

            $phpDocNodeTraverser = new PhpDocParser\PhpDocParser\PhpDocNodeTraverser();

            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', static function (Ast\Node $phpDocNode) use ($aliasesToReferences, $namespacePrefix, $otherNamespacePrefixes, &$hasChanged): ?Ast\Type\IdentifierTypeNode {
                if (!$phpDocNode instanceof Ast\Type\IdentifierTypeNode) {
                    return null;
                }

                if (
                    $phpDocNode instanceof BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode
                    || \strpos($phpDocNode->name, '\\') === 0
                ) {
                    $reference = Reference::fromString(\ltrim($phpDocNode->name, '\\'));

                    if (
                        !$reference->is($namespacePrefix)
                        && !$reference->isDeclaredIn($namespacePrefix)
                    ) {
                        return null;
                    }

                    if ($reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes)) {
                        return null;
                    }

                    $hasChanged = true;

                    if ($reference->is($namespacePrefix)) {
                        return new Ast\Type\IdentifierTypeNode($namespacePrefix->lastNamespaceSegment()->toString());
                    }

                    return new Ast\Type\IdentifierTypeNode(\sprintf(
                        '%s\\%s',
                        $namespacePrefix->lastNamespaceSegment()->toString(),
                        $reference->relativeTo($namespacePrefix)->toString(),
                    ));
                }

                $nameParts = \explode(
                    '\\',
                    $phpDocNode->name,
                );

                $firstName = $nameParts[0];

                if (!\array_key_exists($firstName, $aliasesToReferences)) {
                    return null;
                }

                $importReference = $aliasesToReferences[$firstName];

                $remainingParts = \array_slice(
                    $nameParts,
                    1,
                );

                if (\count($remainingParts) > 0) {
                    $reference = $importReference->append(...$remainingParts);
                } else {
                    $reference = $importReference;
                }

                if (
                    !$reference->is($namespacePrefix)
                    && !$reference->isDeclaredIn($namespacePrefix)
                ) {
                    return null;
                }

                if ($reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes)) {
                    return null;
                }

                if ($reference->is($namespacePrefix)) {
                    $newName = $namespacePrefix->lastNamespaceSegment()->toString();
                } else {
                    $newName = \sprintf(
                        '%s\\%s',
                        $namespacePrefix->lastNamespaceSegment()->toString(),
                        $reference->relativeTo($namespacePrefix)->toString(),
                    );
                }

                if ($phpDocNode->name === $newName) {
                    return null;
                }

                $hasChanged = true;

                return new Ast\Type\IdentifierTypeNode($newName);
            });

            if ($hasChanged) {
                $anyDocBlockChanged = true;

                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            }

            return null;
        });

        return $anyDocBlockChanged;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private static function lastNamespaceSegmentOfNamespacePrefixCollidesWithExistingImport(
        Node $containerNode,
        NamespacePrefix $namespacePrefix
    ): bool {
        $lastNamespaceSegment = $namespacePrefix->lastNamespaceSegment();

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $useStatement) {
                    $reference = Reference::fromString($useStatement->name->toString());

                    if (
                        $reference->is($namespacePrefix)
                        || $reference->isDeclaredIn($namespacePrefix)
                    ) {
                        continue;
                    }

                    if ($useStatement->getAlias()->toString() === $lastNamespaceSegment->toString()) {
                        return true;
                    }
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                $prefix = $statement->prefix->toString();

                foreach ($statement->uses as $useStatement) {
                    $reference = Reference::fromString(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $useStatement->name->toString(),
                    ));

                    if (
                        $reference->is($namespacePrefix)
                        || $reference->isDeclaredIn($namespacePrefix)
                    ) {
                        continue;
                    }

                    if ($useStatement->getAlias()->toString() === $lastNamespaceSegment->toString()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private static function lastNamespaceSegmentOfNamespacePrefixCollidesWithDeclaredSymbol(
        Node $containerNode,
        NamespacePrefix $namespacePrefix
    ): bool {
        $lastNamespaceSegmentOfNamespacePrefix = $namespacePrefix->lastNamespaceSegment();

        foreach ($containerNode->stmts as $statement) {
            if (
                $statement instanceof Node\Stmt\Class_
                || $statement instanceof Node\Stmt\Interface_
                || $statement instanceof Node\Stmt\Trait_
                || $statement instanceof Node\Stmt\Enum_
            ) {
                if (null === $statement->name) {
                    continue;
                }

                if ($statement->name->toString() !== $lastNamespaceSegmentOfNamespacePrefix->toString()) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $otherNamespacePrefixes
     */
    private static function removeMatchingImportsAndAddPrefixImport(
        Node $containerNode,
        NamespacePrefix $namespacePrefix,
        bool $hasPrefixImport,
        array $otherNamespacePrefixes
    ): void {
        /** @var ?int $firstMatchIndex */
        $firstMatchIndex = null;

        /** @var list<int> $indicesToRemove */
        $indicesToRemove = [];

        foreach ($containerNode->stmts as $index => $stmt) {
            if ($stmt instanceof Node\Stmt\Use_) {
                $remainingUses = [];

                foreach ($stmt->uses as $use) {
                    $reference = Reference::fromString($use->name->toString());

                    if ($reference->is($namespacePrefix)) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        $remainingUses[] = $use;

                        continue;
                    }

                    if (
                        $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes)
                    ) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        continue;
                    }

                    $remainingUses[] = $use;
                }

                if (\count($remainingUses) === 0) {
                    $indicesToRemove[] = $index;
                } else {
                    $stmt->uses = $remainingUses;
                }
            } elseif ($stmt instanceof Node\Stmt\GroupUse) {
                $prefix = $stmt->prefix->toString();

                $remainingUses = [];

                foreach ($stmt->uses as $use) {
                    $reference = Reference::fromString(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    ));

                    if ($reference->is($namespacePrefix)) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        $remainingUses[] = $use;

                        continue;
                    }

                    if (
                        $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$otherNamespacePrefixes)
                    ) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        continue;
                    }

                    $remainingUses[] = $use;
                }

                if (\count($remainingUses) === 0) {
                    $indicesToRemove[] = $index;
                } else {
                    $stmt->uses = $remainingUses;
                }
            }
        }

        if (!$hasPrefixImport && null !== $firstMatchIndex) {
            $firstMatchNode = $containerNode->stmts[(int) $firstMatchIndex];

            if (\in_array($firstMatchIndex, $indicesToRemove, true)) {
                if ($firstMatchNode instanceof Node\Stmt\Use_) {
                    $firstMatchNode->type = Node\Stmt\Use_::TYPE_NORMAL;
                    $firstMatchNode->uses = [
                        new Node\UseItem(new Node\Name($namespacePrefix->toString())),
                    ];
                } else {
                    $prefixUseStatement = new Node\Stmt\Use_([
                        new Node\UseItem(new Node\Name($namespacePrefix->toString())),
                    ]);

                    $containerNode->stmts[(int) $firstMatchIndex] = $prefixUseStatement;
                }

                $indicesToRemove = \array_filter($indicesToRemove, static function (int $index) use ($firstMatchIndex): bool {
                    return $index !== $firstMatchIndex;
                });
            } else {
                $prefixUseStatement = new Node\Stmt\Use_([
                    new Node\UseItem(new Node\Name($namespacePrefix->toString())),
                ]);

                \array_splice(
                    $containerNode->stmts,
                    (int) $firstMatchIndex,
                    0,
                    [$prefixUseStatement],
                );

                $indicesToRemove = \array_map(static function (int $index) use ($firstMatchIndex): int {
                    if ($index >= $firstMatchIndex) {
                        return $index + 1;
                    }

                    return $index;
                }, $indicesToRemove);
            }
        }

        if (
            !$hasPrefixImport
            && null === $firstMatchIndex
        ) {
            /** @var ?int $parentImportIndex */
            $parentImportIndex = null;

            foreach ($containerNode->stmts as $index => $statement) {
                if ($statement instanceof Node\Stmt\Use_) {
                    if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                        continue;
                    }

                    foreach ($statement->uses as $use) {
                        $namespacePrefixFromUse = NamespacePrefix::fromString($use->name->toString());

                        if ($namespacePrefixFromUse->isNamespacePrefixOf($namespacePrefix)) {
                            $parentImportIndex = $index;

                            break 2;
                        }
                    }
                } elseif ($statement instanceof Node\Stmt\GroupUse) {
                    if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                        continue;
                    }

                    $prefix = $statement->prefix->toString();

                    foreach ($statement->uses as $use) {
                        $namespacePrefixFromUse = NamespacePrefix::fromString(\sprintf(
                            '%s\\%s',
                            $prefix,
                            $use->name->toString(),
                        ));

                        if ($namespacePrefixFromUse->isNamespacePrefixOf($namespacePrefix)) {
                            $parentImportIndex = $index;

                            break 2;
                        }
                    }
                }
            }

            if (null !== $parentImportIndex) {
                $prefixUseStatement = new Node\Stmt\Use_([
                    new Node\UseItem(new Node\Name($namespacePrefix->toString())),
                ]);

                \array_splice(
                    $containerNode->stmts,
                    (int) $parentImportIndex + 1,
                    0,
                    [
                        $prefixUseStatement,
                    ],
                );
            } else {
                $lastUseIndex = null;

                foreach ($containerNode->stmts as $index => $statement) {
                    if (
                        $statement instanceof Node\Stmt\Use_
                        || $statement instanceof Node\Stmt\GroupUse
                    ) {
                        $lastUseIndex = $index;
                    }
                }

                $prefixUseStatement = new Node\Stmt\Use_([
                    new Node\UseItem(new Node\Name($namespacePrefix->toString())),
                ]);

                if (null !== $lastUseIndex) {
                    \array_splice(
                        $containerNode->stmts,
                        (int) $lastUseIndex + 1,
                        0,
                        [
                            $prefixUseStatement,
                        ],
                    );
                } else {
                    $insertIndex = 0;

                    foreach ($containerNode->stmts as $index => $statement) {
                        if (
                            !$statement instanceof Node\Stmt\Declare_
                            && !$statement instanceof Node\Stmt\Nop
                        ) {
                            $insertIndex = $index;

                            break;
                        }

                        $insertIndex = $index + 1;
                    }

                    \array_splice(
                        $containerNode->stmts,
                        $insertIndex,
                        0,
                        [
                            $prefixUseStatement,
                        ],
                    );
                }
            }
        }

        foreach (\array_reverse($indicesToRemove) as $index) {
            \array_splice(
                $containerNode->stmts,
                $index,
                1,
            );
        }
    }
}
