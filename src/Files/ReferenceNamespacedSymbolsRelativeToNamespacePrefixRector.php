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

    /**
     * @var list<string>
     */
    private array $namespacePrefixes = [];
    private BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory;
    private Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater;

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

            foreach ($configuration[self::CONFIGURATION_KEY_NAMESPACE_PREFIXES] as $namespacePrefix) {
                if (!\is_string($namespacePrefix)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be an array of strings.',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                    ));
                }

                if (1 !== \preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\\\\[a-zA-Z_][a-zA-Z0-9_]*)+$/', $namespacePrefix)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be an array of strings where each string is a valid namespace with at least two segments, got "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $namespacePrefix,
                    ));
                }

                if (\in_array($namespacePrefix, $namespacePrefixes, true)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be an array of unique strings, got duplicate "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $namespacePrefix,
                    ));
                }

                $namespacePrefixes[] = $namespacePrefix;
            }
        }

        $this->namespacePrefixes = $namespacePrefixes;
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
            $moreSpecificNamespacePrefixes = [];

            foreach ($this->namespacePrefixes as $otherNamespacePrefix) {
                if (
                    $otherNamespacePrefix !== $namespacePrefix
                    && \strpos($otherNamespacePrefix, $namespacePrefix . '\\') === 0
                ) {
                    $moreSpecificNamespacePrefixes[] = $otherNamespacePrefix . '\\';
                }
            }

            if ($this->processNamespacePrefix($containerNode, $namespacePrefix, $moreSpecificNamespacePrefixes)) {
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
     * @param list<string>                                 $moreSpecificNamespacePrefixesWithSeparator
     */
    private function processNamespacePrefix(
        Node $containerNode,
        string $namespacePrefix,
        array $moreSpecificNamespacePrefixesWithSeparator
    ): bool {
        $parts = \explode(
            '\\',
            $namespacePrefix,
        );

        $namespacePrefixAlias = $parts[\count($parts) - 1];
        $namespacePrefixWithSeparator = $namespacePrefix . '\\';

        /** @var array<string, string> $importMap */
        $importMap = [];

        $hasPrefixImport = false;

        foreach ($containerNode->stmts as $statement) {
            if (!$statement instanceof Node\Stmt\Use_) {
                continue;
            }

            if (Node\Stmt\Use_::TYPE_NORMAL === $statement->type) {
                foreach ($statement->uses as $use) {
                    $alias = $use->getAlias()->toString();

                    $fqn = $use->name->toString();

                    $importMap[$alias] = $fqn;

                    if ($fqn === $namespacePrefix) {
                        $hasPrefixImport = true;
                    }
                }
            }
        }

        $hasDirectMatchingImports = self::hasMatchingImports(
            $containerNode,
            $namespacePrefixWithSeparator,
            $moreSpecificNamespacePrefixesWithSeparator,
        );

        $hasParentImport = self::hasParentImport(
            $containerNode,
            $namespacePrefix,
        );

        if (
            !$hasDirectMatchingImports
            && !$hasParentImport
        ) {
            return false;
        }

        if (self::prefixAliasCollidesWithExistingImport($containerNode, $namespacePrefix, $namespacePrefixAlias)) {
            return false;
        }

        $statementsRewritten = $this->rewriteNamesInStatements(
            $containerNode,
            $namespacePrefixAlias,
            $namespacePrefixWithSeparator,
            $moreSpecificNamespacePrefixesWithSeparator,
        );

        $docBlocksRewritten = $this->rewriteNamesInDocBlocks(
            $containerNode,
            $importMap,
            $namespacePrefix,
            $namespacePrefixAlias,
            $namespacePrefixWithSeparator,
            $moreSpecificNamespacePrefixesWithSeparator,
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
            $namespacePrefixWithSeparator,
            $hasPrefixImport,
            $moreSpecificNamespacePrefixesWithSeparator,
        );

        return true;
    }

    /**
     * @param list<string> $moreSpecificNamespacePrefixesWithSeparator
     */
    private static function matchesNamespacePrefixExclusively(
        string $name,
        string $namespacePrefixWithSeparator,
        array $moreSpecificNamespacePrefixesWithSeparator
    ): bool {
        if (\strpos($name, $namespacePrefixWithSeparator) !== 0) {
            return false;
        }

        foreach ($moreSpecificNamespacePrefixesWithSeparator as $moreSpecificNamespacePrefix) {
            if (
                \strpos($name, $moreSpecificNamespacePrefix) === 0
                || $name . '\\' === $moreSpecificNamespacePrefix
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<string>                                 $moreSpecificNamespacePrefixesWithSeparator
     */
    private static function hasMatchingImports(
        Node $containerNode,
        string $namespacePrefixWithSeparator,
        array $moreSpecificNamespacePrefixesWithSeparator
    ): bool {
        foreach ($containerNode->stmts as $statement) {
            if (!$statement instanceof Node\Stmt\Use_) {
                continue;
            }

            foreach ($statement->uses as $use) {
                $name = $use->name->toString();

                if (self::matchesNamespacePrefixExclusively($name, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator)) {
                    return true;
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
        string $namespacePrefix
    ): bool {
        $namespacePrefixWithSeparator = $namespacePrefix . '\\';

        foreach ($containerNode->stmts as $statement) {
            if (!$statement instanceof Node\Stmt\Use_) {
                continue;
            }

            if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                continue;
            }

            foreach ($statement->uses as $use) {
                $name = $use->name->toString();
                $nameWithSeparator = $name . '\\';

                if (
                    \strlen($nameWithSeparator) < \strlen($namespacePrefixWithSeparator)
                    && \strpos($namespacePrefixWithSeparator, $nameWithSeparator) === 0
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<string>                                 $moreSpecificNamespacePrefixesWithSeparator
     */
    private function rewriteNamesInStatements(
        Node $containerNode,
        string $namespacePrefixAlias,
        string $namespacePrefixWithSeparator,
        array $moreSpecificNamespacePrefixesWithSeparator
    ): bool {
        $hasChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, static function (Node $node) use ($namespacePrefixAlias, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator, &$hasChanged): ?Node {
            if (!$node instanceof Node\Name\FullyQualified) {
                return null;
            }

            $fullName = $node->toString();

            if (!self::matchesNamespacePrefixExclusively($fullName, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator)) {
                return null;
            }

            $relativePath = \substr(
                $fullName,
                \strlen($namespacePrefixWithSeparator),
            );

            $hasChanged = true;

            return new Node\Name($namespacePrefixAlias . '\\' . $relativePath);
        });

        return $hasChanged;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param array<string, string>                        $importMap
     * @param list<string>                                 $moreSpecificNamespacePrefixesWithSeparator
     */
    private function rewriteNamesInDocBlocks(
        Node $containerNode,
        array $importMap,
        string $namespacePrefix,
        string $namespacePrefixAlias,
        string $namespacePrefixWithSeparator,
        array $moreSpecificNamespacePrefixesWithSeparator
    ): bool {
        $anyDocBlockChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, function (Node $node) use (&$anyDocBlockChanged, $importMap, $namespacePrefix, $namespacePrefixAlias, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator): ?Node {
            if ($node instanceof Node\Stmt\Use_) {
                return null;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return null;
            }

            $hasChanged = false;

            $phpDocNodeTraverser = new PhpDocParser\PhpDocParser\PhpDocNodeTraverser();

            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', static function (Ast\Node $phpDocNode) use ($importMap, $namespacePrefix, $namespacePrefixAlias, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator, &$hasChanged): ?Ast\Type\IdentifierTypeNode {
                if (!$phpDocNode instanceof Ast\Type\IdentifierTypeNode) {
                    return null;
                }

                $name = $phpDocNode->name;

                if (
                    $phpDocNode instanceof BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode
                    || \strpos($name, '\\') === 0
                ) {
                    $name = \ltrim($name, '\\');

                    if (!self::matchesNamespacePrefixExclusively($name, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator)) {
                        return null;
                    }

                    $relativePath = \substr(
                        $name,
                        \strlen($namespacePrefixWithSeparator),
                    );

                    $hasChanged = true;

                    return new Ast\Type\IdentifierTypeNode($namespacePrefixAlias . '\\' . $relativePath);
                }

                $nameParts = \explode('\\', $name);

                $firstName = $nameParts[0];

                if (!\array_key_exists($firstName, $importMap)) {
                    return null;
                }

                $importFqn = $importMap[$firstName];

                $remainingParts = \array_slice(
                    $nameParts,
                    1,
                );

                $referenceFqn = $importFqn;

                if (\count($remainingParts) > 0) {
                    $referenceFqn .= '\\' . \implode('\\', $remainingParts);
                }

                if (
                    $referenceFqn !== $namespacePrefix
                    && !self::matchesNamespacePrefixExclusively($referenceFqn, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator)
                ) {
                    return null;
                }

                if ($referenceFqn === $namespacePrefix) {
                    $newName = $namespacePrefixAlias;
                } else {
                    $relativePath = \substr(
                        $referenceFqn,
                        \strlen($namespacePrefixWithSeparator),
                    );

                    $newName = $namespacePrefixAlias . '\\' . $relativePath;
                }

                if ($name === $newName) {
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
    private static function prefixAliasCollidesWithExistingImport(
        Node $containerNode,
        string $namespacePrefix,
        string $prefixAlias
    ): bool {
        $namespacePrefixWithSeparator = $namespacePrefix . '\\';

        foreach ($containerNode->stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\Use_) {
                continue;
            }

            if (Node\Stmt\Use_::TYPE_NORMAL !== $stmt->type) {
                continue;
            }

            foreach ($stmt->uses as $use) {
                $name = $use->name->toString();

                if ($name === $namespacePrefix) {
                    continue;
                }

                if (\strpos($name, $namespacePrefixWithSeparator) === 0) {
                    continue;
                }

                if ($use->getAlias()->toString() === $prefixAlias) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<string>                                 $moreSpecificNamespacePrefixesWithSeparator
     */
    private static function removeMatchingImportsAndAddPrefixImport(
        Node $containerNode,
        string $namespacePrefix,
        string $namespacePrefixWithSeparator,
        bool $hasPrefixImport,
        array $moreSpecificNamespacePrefixesWithSeparator
    ): void {
        /** @var ?int $firstMatchIndex */
        $firstMatchIndex = null;

        /** @var list<int> $indicesToRemove */
        $indicesToRemove = [];

        foreach ($containerNode->stmts as $index => $stmt) {
            if (!$stmt instanceof Node\Stmt\Use_) {
                continue;
            }

            $remainingUses = [];

            foreach ($stmt->uses as $use) {
                $name = $use->name->toString();

                if ($name === $namespacePrefix) {
                    if (null === $firstMatchIndex) {
                        $firstMatchIndex = $index;
                    }

                    $remainingUses[] = $use;

                    continue;
                }

                if (self::matchesNamespacePrefixExclusively($name, $namespacePrefixWithSeparator, $moreSpecificNamespacePrefixesWithSeparator)) {
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

        if (!$hasPrefixImport && null !== $firstMatchIndex) {
            /** @var Node\Stmt\Use_ $firstMatchNode */
            $firstMatchNode = $containerNode->stmts[(int) $firstMatchIndex];

            if (\in_array($firstMatchIndex, $indicesToRemove, true)) {
                $firstMatchNode->type = Node\Stmt\Use_::TYPE_NORMAL;
                $firstMatchNode->uses = [
                    new Node\UseItem(new Node\Name($namespacePrefix)),
                ];

                $indicesToRemove = \array_filter($indicesToRemove, static function (int $index) use ($firstMatchIndex): bool {
                    return $index !== $firstMatchIndex;
                });
            } else {
                $prefixUseStatement = new Node\Stmt\Use_([
                    new Node\UseItem(new Node\Name($namespacePrefix)),
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
                if (!$statement instanceof Node\Stmt\Use_) {
                    continue;
                }

                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $use) {
                    $name = $use->name->toString();
                    $nameWithSeparator = $name . '\\';

                    if (
                        \strlen($nameWithSeparator) < \strlen($namespacePrefixWithSeparator)
                        && \strpos($namespacePrefixWithSeparator, $nameWithSeparator) === 0
                    ) {
                        $parentImportIndex = $index;

                        break 2;
                    }
                }
            }

            if (null !== $parentImportIndex) {
                $prefixUseStatement = new Node\Stmt\Use_([
                    new Node\UseItem(new Node\Name($namespacePrefix)),
                ]);

                \array_splice(
                    $containerNode->stmts,
                    (int) $parentImportIndex + 1,
                    0,
                    [
                        $prefixUseStatement,
                    ],
                );
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
