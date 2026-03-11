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

final class UseImportRelativeToNamespacePrefixRector extends Rector\AbstractRector implements Contract\Rector\ConfigurableRectorInterface
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
            'Replace sub-namespace imports like `use Foo\Bar\Baz;` with parent namespace import `use Foo\Bar;` and update references to use relative names.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
use Example\Core\AbstractController;
use Example\Core\Routing\Attribute\Route;

final class Foo extends AbstractController
{
    #[Route(path: '/foo', name: 'foo')]
    public function bar(): void {}
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Example\Core;

final class Foo extends Core\AbstractController
{
    #[Core\Routing\Attribute\Route(path: '/foo', name: 'foo')]
    public function bar(): void {}
}
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES => [
                            'Example\Core',
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
            if ($this->processNamespacePrefix($containerNode, $namespacePrefix)) {
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
     */
    private function processNamespacePrefix(
        Node $containerNode,
        string $namespacePrefix
    ): bool {
        /** @var array<string, string> $aliasToRelativePath */
        $aliasToRelativePath = [];

        $hasParentImport = false;
        $hasSubImports = false;
        $namespacePrefixWithSeparator = $namespacePrefix . '\\';

        $parts = \explode(
            '\\',
            $namespacePrefix,
        );

        $parentAlias = $parts[\count($parts) - 1];

        foreach ($containerNode->stmts as $statement) {
            if (!$statement instanceof Node\Stmt\Use_) {
                continue;
            }

            if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                continue;
            }

            foreach ($statement->uses as $use) {
                $name = $use->name->toString();

                if ($name === $namespacePrefix) {
                    $hasParentImport = true;

                    continue;
                }

                if (\strpos($name, $namespacePrefixWithSeparator) !== 0) {
                    continue;
                }

                $hasSubImports = true;

                $alias = $use->getAlias()->toString();

                $relativePath = \substr(
                    $name,
                    \strlen($namespacePrefixWithSeparator),
                );

                $aliasToRelativePath[$alias] = $parentAlias . '\\' . $relativePath;
            }
        }

        if (!$hasSubImports) {
            return false;
        }

        if (self::parentAliasCollidesWithExistingImport($containerNode, $namespacePrefix, $parentAlias)) {
            return false;
        }

        $this->replaceNamesInStatements(
            $containerNode,
            $aliasToRelativePath,
            $namespacePrefix,
            $parentAlias,
        );

        $this->replaceNamesInDocBlocks(
            $containerNode,
            $aliasToRelativePath,
            $namespacePrefix,
            $parentAlias,
        );

        self::removeSubImportsAndAddParentImport(
            $containerNode,
            $namespacePrefix,
            $hasParentImport,
        );

        return true;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param array<string, string>                        $aliasToRelativePath
     */
    private function replaceNamesInStatements(
        Node $containerNode,
        array $aliasToRelativePath,
        string $namespacePrefix,
        string $parentAlias
    ): void {
        $namespacePrefixWithSeparator = $namespacePrefix . '\\';

        $this->traverseNodesWithCallable($containerNode->stmts, static function (Node $node) use ($aliasToRelativePath, $namespacePrefixWithSeparator, $parentAlias): ?Node {
            if ($node instanceof Node\Stmt\Use_) {
                return null;
            }

            if (!$node instanceof Node\Name) {
                return null;
            }

            if ($node instanceof Node\Name\FullyQualified) {
                $fullName = $node->toString();

                if (\strpos($fullName, $namespacePrefixWithSeparator) !== 0) {
                    return null;
                }

                $relativePath = \substr(
                    $fullName,
                    \strlen($namespacePrefixWithSeparator),
                );

                return new Node\Name($parentAlias . '\\' . $relativePath);
            }

            $parts = $node->getParts();

            $firstName = $parts[0];

            if (!\array_key_exists($firstName, $aliasToRelativePath)) {
                return null;
            }

            $remainingParts = \array_slice(
                $parts,
                1,
            );

            $newName = $aliasToRelativePath[$firstName];

            if (\count($remainingParts) > 0) {
                $newName .= '\\' . \implode('\\', $remainingParts);
            }

            return new Node\Name(
                $newName,
                $node->getAttributes(),
            );
        });
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param array<string, string>                        $aliasToRelativePath
     */
    private function replaceNamesInDocBlocks(
        Node $containerNode,
        array $aliasToRelativePath,
        string $namespacePrefix,
        string $parentAlias
    ): void {
        $namespacePrefixWithSeparator = $namespacePrefix . '\\';

        $this->traverseNodesWithCallable($containerNode->stmts, function (Node $node) use ($aliasToRelativePath, $namespacePrefixWithSeparator, $parentAlias): ?Node {
            if ($node instanceof Node\Stmt\Use_) {
                return null;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return null;
            }

            $hasChanged = false;

            $phpDocNodeTraverser = new PhpDocParser\PhpDocParser\PhpDocNodeTraverser();

            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', static function (Ast\Node $phpDocNode) use ($aliasToRelativePath, $namespacePrefixWithSeparator, $parentAlias, &$hasChanged): ?Ast\Type\IdentifierTypeNode {
                if (!$phpDocNode instanceof Ast\Type\IdentifierTypeNode) {
                    return null;
                }

                $name = $phpDocNode->name;

                if (
                    $phpDocNode instanceof BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode
                    || \strpos($name, '\\') === 0
                ) {
                    $name = \ltrim($name, '\\');

                    if (\strpos($name, $namespacePrefixWithSeparator) !== 0) {
                        return null;
                    }

                    $relativePath = \substr(
                        $name,
                        \strlen($namespacePrefixWithSeparator),
                    );

                    $hasChanged = true;

                    return new Ast\Type\IdentifierTypeNode($parentAlias . '\\' . $relativePath);
                }

                $nameParts = \explode('\\', $name);
                $firstName = $nameParts[0];

                if (!\array_key_exists($firstName, $aliasToRelativePath)) {
                    return null;
                }

                $remainingParts = \array_slice(
                    $nameParts,
                    1,
                );

                $newName = $aliasToRelativePath[$firstName];

                if (\count($remainingParts) > 0) {
                    $newName .= '\\' . \implode('\\', $remainingParts);
                }

                $hasChanged = true;

                return new Ast\Type\IdentifierTypeNode($newName);
            });

            if ($hasChanged) {
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            }

            return null;
        });
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private static function parentAliasCollidesWithExistingImport(
        Node $containerNode,
        string $namespacePrefix,
        string $parentAlias
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

                if ($use->getAlias()->toString() === $parentAlias) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private static function removeSubImportsAndAddParentImport(
        Node $containerNode,
        string $namespacePrefix,
        bool $hasParentImport
    ): void {
        $namespacePrefixWithSeparator = $namespacePrefix . '\\';

        /** @var ?int $firstMatchIndex */
        $firstMatchIndex = null;

        /** @var list<int> $indicesToRemove */
        $indicesToRemove = [];

        foreach ($containerNode->stmts as $index => $stmt) {
            if (!$stmt instanceof Node\Stmt\Use_) {
                continue;
            }

            if (Node\Stmt\Use_::TYPE_NORMAL !== $stmt->type) {
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

                if (\strpos($name, $namespacePrefixWithSeparator) === 0) {
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

        if (!$hasParentImport && null !== $firstMatchIndex) {
            /** @var Node\Stmt\Use_ $firstMatchNode */
            $firstMatchNode = $containerNode->stmts[(int) $firstMatchIndex];

            $firstMatchNode->uses = [
                new Node\UseItem(new Node\Name($namespacePrefix)),
            ];

            $indicesToRemove = \array_filter($indicesToRemove, static function (int $index) use ($firstMatchIndex): bool {
                return $index !== $firstMatchIndex;
            });
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
