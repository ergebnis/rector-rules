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
    private const CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES = 'forceRelativeReferences';
    private const CONFIGURATION_KEY_NAMESPACE_PREFIXES = 'namespacePrefixes';
    private const CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES = 'parentNamespacePrefixes';
    private BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory;
    private Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater;
    private bool $forceRelativeReferences = false;

    /**
     * @var list<NamespacePrefix>
     */
    private array $namespacePrefixes = [];

    /**
     * @var list<NamespacePrefix>
     */
    private array $parentNamespacePrefixes = [];

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
            self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES,
            self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
            self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
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

        $forceRelativeReferences = false;

        if (\array_key_exists(self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES, $configuration)) {
            if (!\is_bool($configuration[self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be a boolean.',
                    self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES,
                ));
            }

            $forceRelativeReferences = $configuration[self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES];
        }

        $namespacePrefixes = [];

        if (\array_key_exists(self::CONFIGURATION_KEY_NAMESPACE_PREFIXES, $configuration)) {
            if (!\is_array($configuration[self::CONFIGURATION_KEY_NAMESPACE_PREFIXES])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be a list of strings.',
                    self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                ));
            }

            if (\array_values($configuration[self::CONFIGURATION_KEY_NAMESPACE_PREFIXES]) !== $configuration[self::CONFIGURATION_KEY_NAMESPACE_PREFIXES]) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be a list of strings.',
                    self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                ));
            }

            foreach ($configuration[self::CONFIGURATION_KEY_NAMESPACE_PREFIXES] as $value) {
                if (!\is_string($value)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be a list of strings.',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                    ));
                }

                try {
                    $namespacePrefix = NamespacePrefix::fromString($value);
                } catch (\InvalidArgumentException $exception) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be a list of strings where each string is a valid namespace with at least two segments, got "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                if ($namespacePrefix->namespaceSegmentCount() < 2) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be a list of strings where each string is a valid namespace with at least two segments, got "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                if (\array_key_exists($value, $namespacePrefixes)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be a list of unique strings, got duplicate "%s".',
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                $namespacePrefixes[$value] = $namespacePrefix;
            }

            $namespacePrefixes = \array_values($namespacePrefixes);
        }

        $parentNamespacePrefixes = [];

        if (\array_key_exists(self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES, $configuration)) {
            if (!\is_array($configuration[self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be a list of strings.',
                    self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                ));
            }

            if (\array_values($configuration[self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES]) !== $configuration[self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES]) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be a list of strings.',
                    self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                ));
            }

            foreach ($configuration[self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES] as $value) {
                if (!\is_string($value)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be a list of strings.',
                        self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                    ));
                }

                try {
                    $parentNamespacePrefix = NamespacePrefix::fromString($value);
                } catch (\InvalidArgumentException $exception) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be a list of strings where each string is a valid namespace with at least one segment, got "%s".',
                        self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                if (\array_key_exists($value, $parentNamespacePrefixes)) {
                    throw new \InvalidArgumentException(\sprintf(
                        'Value for configuration option "%s" needs to be a list of unique strings, got duplicate "%s".',
                        self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                        $value,
                    ));
                }

                $parentNamespacePrefixes[$value] = $parentNamespacePrefix;
            }

            $parentNamespacePrefixes = \array_values($parentNamespacePrefixes);

            foreach ($parentNamespacePrefixes as $parentNamespacePrefix) {
                foreach ($parentNamespacePrefixes as $otherNamespacePrefix) {
                    if ($parentNamespacePrefix->toString() === $otherNamespacePrefix->toString()) {
                        continue;
                    }

                    if ($parentNamespacePrefix->isNamespacePrefixOf($otherNamespacePrefix)) {
                        throw new \InvalidArgumentException(\sprintf(
                            'Value for configuration option "%s" needs to be a list of strings where no string is a namespace prefix of another, got "%s" and "%s".',
                            self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                            $parentNamespacePrefix->toString(),
                            $otherNamespacePrefix->toString(),
                        ));
                    }
                }
            }
        }

        $this->forceRelativeReferences = $forceRelativeReferences;
        $this->namespacePrefixes = $namespacePrefixes;
        $this->parentNamespacePrefixes = $parentNamespacePrefixes;
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
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core\Routing\Attribute\Route;
use Example\Domain\UserRepository;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    private UserRepository $userRepository;

    #[Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core\Routing;
use Example\Domain;
use Psr\Http;

final class ExampleController
{
    private Domain\UserRepository $userRepository;

    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): Http\Message\ResponseInterface
    {
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES => [
                            'Example\Core\Routing',
                            'Example\Domain',
                            'Psr\Http',
                        ],
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core\Controller\AbstractController;

final class ExampleController extends AbstractController
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core;

final class ExampleController extends Core\Controller\AbstractController
{
}
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES => [
                            'Example',
                        ],
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core\Controller\AbstractController;
use Example\Core\Routing\Attribute\Route;

final class ExampleController extends AbstractController
{
    #[Route(path: '/example', name: 'example')]
    public function dashboard()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core;
use Example\Core\Routing;

final class ExampleController extends Core\Controller\AbstractController
{
    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard()
    {
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES => [
                            'Example\Core\Routing',
                        ],
                        self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES => [
                            'Example',
                        ],
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core\Caching\Redis\Connection;
use Example\Core\Controller\AbstractController;
use Example\Core\Routing\Attribute\Route;

final class ExampleController extends AbstractController
{
    #[Route(path: '/example', name: 'example')]
    #[Connection(host: 'localhost')]
    public function dashboard()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace Example\App;

use Example\Core\Caching\Redis;
use Example\Core;
use Example\Core\Routing;

final class ExampleController extends Core\Controller\AbstractController
{
    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    #[Redis\Connection(host: 'localhost')]
    public function dashboard()
    {
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES => [
                            'Example\Core\Routing',
                            'Example\Core',
                            'Example\Core\Caching\Redis',
                        ],
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace Example\Core\Bar;

use Example\Core\Bar\Baz;
use Example\Core\Bar\Baz\Qux;
use Example\Core\Quz;

final class ExampleService
{
    public function __construct(
        private Baz $baz,
        private Qux $qux,
        private Quz $quz,
    ) {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace Example\Core\Bar;

use Example\Core;

final class ExampleService
{
    public function __construct(
        private Core\Bar\Baz $baz,
        private Core\Bar\Baz\Qux $qux,
        private Core\Quz $quz,
    ) {
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES => true,
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

        $discoveredPrefixes = self::discoverNamespacePrefixesFromParents(
            $containerNode,
            $this->parentNamespacePrefixes,
            $this->namespacePrefixes,
        );

        $allNamespacePrefixes = \array_merge(
            $this->namespacePrefixes,
            $discoveredPrefixes,
        );

        if (\count($allNamespacePrefixes) === 0) {
            return null;
        }

        $changed = false;

        foreach ($allNamespacePrefixes as $namespacePrefix) {
            $otherNamespacePrefixes = [];

            foreach ($allNamespacePrefixes as $otherNamespacePrefix) {
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

        $namespacePrefixOfContainingFile = null;

        if (!$this->forceRelativeReferences) {
            $namespacePrefixOfContainingFile = self::namespacePrefixOfContainingFile(
                $containerNode,
                $namespacePrefix,
            );
        }

        if (
            !$namespacePrefixOfContainingFile instanceof NamespacePrefix
            && self::lastNamespaceSegmentOfNamespacePrefixCollidesWithExistingImport($containerNode, $namespacePrefix)
        ) {
            return false;
        }

        if (
            !$namespacePrefixOfContainingFile instanceof NamespacePrefix
            && self::lastNamespaceSegmentOfNamespacePrefixCollidesWithDeclaredSymbol($containerNode, $namespacePrefix)
        ) {
            return false;
        }

        $statementsRewritten = $this->rewriteNamesInStatements(
            $containerNode,
            $namespacePrefix,
            $otherNamespacePrefixes,
            $namespacePrefixOfContainingFile,
        );

        $docBlocksRewritten = $this->rewriteNamesInDocBlocks(
            $containerNode,
            $aliasesToReferences,
            $namespacePrefix,
            $otherNamespacePrefixes,
            $namespacePrefixOfContainingFile,
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
            $namespacePrefixOfContainingFile,
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
        array $otherNamespacePrefixes,
        ?NamespacePrefix $fileNamespaceAsPrefix
    ): bool {
        $lastNamespaceSegmentOfNamespacePrefix = $namespacePrefix->lastNamespaceSegment();

        $hasChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, static function (Node $node) use ($namespacePrefix, $lastNamespaceSegmentOfNamespacePrefix, $otherNamespacePrefixes, $fileNamespaceAsPrefix, &$hasChanged): ?Node {
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

            if (null !== $fileNamespaceAsPrefix) {
                if ($reference->is($fileNamespaceAsPrefix)) {
                    return null;
                }

                return new Node\Name($reference->relativeTo($fileNamespaceAsPrefix)->toString());
            }

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
        array $otherNamespacePrefixes,
        ?NamespacePrefix $fileNamespaceAsPrefix
    ): bool {
        $anyDocBlockChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, function (Node $node) use (&$anyDocBlockChanged, $aliasesToReferences, $namespacePrefix, $otherNamespacePrefixes, $fileNamespaceAsPrefix): ?Node {
            if ($node instanceof Node\Stmt\Use_) {
                return null;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return null;
            }

            $hasChanged = false;

            $phpDocNodeTraverser = new PhpDocParser\PhpDocParser\PhpDocNodeTraverser();

            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', static function (Ast\Node $phpDocNode) use ($aliasesToReferences, $namespacePrefix, $otherNamespacePrefixes, $fileNamespaceAsPrefix, &$hasChanged): ?Ast\Type\IdentifierTypeNode {
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

                    if (null !== $fileNamespaceAsPrefix) {
                        if ($reference->is($fileNamespaceAsPrefix)) {
                            return null;
                        }

                        return new Ast\Type\IdentifierTypeNode($reference->relativeTo($fileNamespaceAsPrefix)->toString());
                    }

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

                if (null !== $fileNamespaceAsPrefix) {
                    if ($reference->is($fileNamespaceAsPrefix)) {
                        return null;
                    }

                    $newName = $reference->relativeTo($fileNamespaceAsPrefix)->toString();
                } elseif ($reference->is($namespacePrefix)) {
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
     */
    private static function namespacePrefixOfContainingFile(
        Node $containerNode,
        NamespacePrefix $namespacePrefix
    ): ?NamespacePrefix {
        if (!$containerNode instanceof Node\Stmt\Namespace_) {
            return null;
        }

        if (null === $containerNode->name) {
            return null;
        }

        $fileNamespace = $containerNode->name->toString();

        if ($namespacePrefix->toString() === $fileNamespace) {
            return NamespacePrefix::fromString($fileNamespace);
        }

        if (\strpos($namespacePrefix->toString(), $fileNamespace . '\\') === 0) {
            return NamespacePrefix::fromString($fileNamespace);
        }

        return null;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $parentNamespacePrefixes
     * @param list<NamespacePrefix>                        $existingNamespacePrefixes
     *
     * @return list<NamespacePrefix>
     */
    private static function discoverNamespacePrefixesFromParents(
        Node $containerNode,
        array $parentNamespacePrefixes,
        array $existingNamespacePrefixes
    ): array {
        if (\count($parentNamespacePrefixes) === 0) {
            return [];
        }

        /** @var array<string, NamespacePrefix> $discovered */
        $discovered = [];

        $existingKeys = [];

        foreach ($existingNamespacePrefixes as $existingPrefix) {
            $existingKeys[$existingPrefix->toString()] = true;
        }

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $use) {
                    $reference = $use->name->toString();

                    self::discoverChildPrefix(
                        $reference,
                        $parentNamespacePrefixes,
                        $existingKeys,
                        $discovered,
                    );
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                $prefix = $statement->prefix->toString();

                foreach ($statement->uses as $use) {
                    $reference = \sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    );

                    self::discoverChildPrefix(
                        $reference,
                        $parentNamespacePrefixes,
                        $existingKeys,
                        $discovered,
                    );
                }
            }
        }

        $nodeFinder = new NodeFinder();

        $nodeFinder->find(
            $containerNode->stmts,
            static function (Node $node) use ($parentNamespacePrefixes, &$existingKeys, &$discovered): bool {
                if (!$node instanceof Node\Name\FullyQualified) {
                    return false;
                }

                self::discoverChildPrefix(
                    $node->toString(),
                    $parentNamespacePrefixes,
                    $existingKeys,
                    $discovered,
                );

                return false;
            },
        );

        $nodeFinder->find(
            $containerNode->stmts,
            static function (Node $node) use ($parentNamespacePrefixes, &$existingKeys, &$discovered): bool {
                $docComment = $node->getDocComment();

                if (null === $docComment) {
                    return false;
                }

                $text = $docComment->getText();

                foreach ($parentNamespacePrefixes as $parentNamespacePrefix) {
                    $pattern = \sprintf(
                        '/\\\\%s\\\\([a-zA-Z_][a-zA-Z0-9_]*)/',
                        \preg_quote($parentNamespacePrefix->toString(), '/'),
                    );

                    if (\preg_match_all($pattern, $text, $matches) > 0) {
                        foreach ($matches[1] as $segmentString) {
                            $segment = NamespaceSegment::fromString($segmentString);
                            $childPrefix = $parentNamespacePrefix->append($segment);
                            $childKey = $childPrefix->toString();

                            if (!\array_key_exists($childKey, $discovered) && !\array_key_exists($childKey, $existingKeys)) {
                                $discovered[$childKey] = $childPrefix;
                            }
                        }
                    }
                }

                return false;
            },
        );

        return \array_values($discovered);
    }

    /**
     * @param list<NamespacePrefix>          $parentNamespacePrefixes
     * @param array<string, true>            $existingKeys
     * @param array<string, NamespacePrefix> $discovered
     */
    private static function discoverChildPrefix(
        string $reference,
        array $parentNamespacePrefixes,
        array &$existingKeys,
        array &$discovered
    ): void {
        foreach ($parentNamespacePrefixes as $parentNamespacePrefix) {
            $parentString = $parentNamespacePrefix->toString();

            if (\strpos($reference, $parentString . '\\') !== 0) {
                continue;
            }

            $remaining = \substr($reference, \strlen($parentString) + 1);
            $parts = \explode('\\', $remaining);

            $segment = NamespaceSegment::fromString($parts[0]);
            $childPrefix = $parentNamespacePrefix->append($segment);
            $childKey = $childPrefix->toString();

            if (!\array_key_exists($childKey, $discovered) && !\array_key_exists($childKey, $existingKeys)) {
                $discovered[$childKey] = $childPrefix;
            }
        }
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $otherNamespacePrefixes
     */
    private static function removeMatchingImportsAndAddPrefixImport(
        Node $containerNode,
        NamespacePrefix $namespacePrefix,
        bool $hasPrefixImport,
        array $otherNamespacePrefixes,
        ?NamespacePrefix $fileNamespaceAsPrefix
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

        if (null !== $fileNamespaceAsPrefix) {
            foreach (\array_reverse($indicesToRemove) as $index) {
                \array_splice(
                    $containerNode->stmts,
                    $index,
                    1,
                );
            }

            return;
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
