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

use Ergebnis\Rector\Rules;
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

final class ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector extends Rector\AbstractRector implements
    Contract\Rector\ConfigurableRectorInterface,
    Rules\Configuration\HasConfigurationOptions
{
    private const CONFIGURATION_KEY_DISCOVER_NAMESPACE_PREFIXES = 'discoverNamespacePrefixes';
    private const CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES = 'forceRelativeReferences';
    private const CONFIGURATION_KEY_NAMESPACE_PREFIXES = 'namespacePrefixes';
    private const CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES = 'parentNamespacePrefixes';
    private BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory;
    private Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater;
    private bool $discoverNamespacePrefixes = false;
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
        $resolvedConfiguration = $this->configurationOptions()->resolveConfigurationFrom($configuration);

        /** @var bool $discoverNamespacePrefixes */
        $discoverNamespacePrefixes = $resolvedConfiguration->get(Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_DISCOVER_NAMESPACE_PREFIXES));

        /** @var bool $forceRelativeReferences */
        $forceRelativeReferences = $resolvedConfiguration->get(Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES));

        $namespacePrefixes = [];

        /** @var list<string> $namespacePrefixValues */
        $namespacePrefixValues = $resolvedConfiguration->get(Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_NAMESPACE_PREFIXES));

        foreach ($namespacePrefixValues as $value) {
            try {
                $namespacePrefix = Rules\Files\NamespacePrefix::fromString($value);
            } catch (\InvalidArgumentException $exception) {
                throw new Rules\Configuration\InvalidOptionValue(\sprintf(
                    'Value for configuration option "%s" needs to be a list of strings where each string is a valid namespace with at least two segments, got "%s".',
                    self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                    $value,
                ));
            }

            if ($namespacePrefix->namespaceSegmentCount() < 2) {
                throw new Rules\Configuration\InvalidOptionValue(\sprintf(
                    'Value for configuration option "%s" needs to be a list of strings where each string is a valid namespace with at least two segments, got "%s".',
                    self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                    $value,
                ));
            }

            if (\array_key_exists($value, $namespacePrefixes)) {
                throw new Rules\Configuration\InvalidOptionValue(\sprintf(
                    'Value for configuration option "%s" needs to be a list of unique strings, got duplicate "%s".',
                    self::CONFIGURATION_KEY_NAMESPACE_PREFIXES,
                    $value,
                ));
            }

            $namespacePrefixes[$value] = $namespacePrefix;
        }

        $namespacePrefixes = \array_values($namespacePrefixes);

        $parentNamespacePrefixes = [];

        /** @var list<string> $parentNamespacePrefixValues */
        $parentNamespacePrefixValues = $resolvedConfiguration->get(Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES));

        foreach ($parentNamespacePrefixValues as $value) {
            try {
                $parentNamespacePrefix = Rules\Files\NamespacePrefix::fromString($value);
            } catch (\InvalidArgumentException $exception) {
                throw new Rules\Configuration\InvalidOptionValue(\sprintf(
                    'Value for configuration option "%s" needs to be a list of strings where each string is a valid namespace with at least one segment, got "%s".',
                    self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                    $value,
                ));
            }

            if (\array_key_exists($value, $parentNamespacePrefixes)) {
                throw new Rules\Configuration\InvalidOptionValue(\sprintf(
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
                    throw new Rules\Configuration\InvalidOptionValue(\sprintf(
                        'Value for configuration option "%s" needs to be a list of strings where no string is a namespace prefix of another, got "%s" and "%s".',
                        self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES,
                        $parentNamespacePrefix->toString(),
                        $otherNamespacePrefix->toString(),
                    ));
                }
            }
        }

        $this->discoverNamespacePrefixes = $discoverNamespacePrefixes;
        $this->forceRelativeReferences = $forceRelativeReferences;
        $this->namespacePrefixes = $namespacePrefixes;
        $this->parentNamespacePrefixes = $parentNamespacePrefixes;
    }

    public function configurationOptions(): Rules\Configuration\Options
    {
        return Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_DISCOVER_NAMESPACE_PREFIXES),
                Rules\Configuration\OptionDescription::fromString('Automatically discover namespace prefixes by scanning the file\'s references and extracting their first segment.'),
                Rules\Configuration\OptionValue::booleanDefaultingTo(false),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_FORCE_RELATIVE_REFERENCES),
                Rules\Configuration\OptionDescription::fromString('Force references to be expressed relative to the namespace prefix even when the file namespace matches the prefix.'),
                Rules\Configuration\OptionValue::booleanDefaultingTo(false),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_NAMESPACE_PREFIXES),
                Rules\Configuration\OptionDescription::fromString('A list of namespace prefixes to consolidate.'),
                Rules\Configuration\OptionValue::listOfStringsDefaultingTo([]),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_PARENT_NAMESPACE_PREFIXES),
                Rules\Configuration\OptionDescription::fromString('A list of parent namespace prefixes for automatic discovery of namespace prefixes per file.'),
                Rules\Configuration\OptionValue::listOfStringsDefaultingTo([]),
            ),
        );
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Replaces references to namespaced symbols (classes, functions, constants) whose fully-qualified name starts with a namespace prefix so they are relative to that prefix.',
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
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace App;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Kernel
{
    public function handle(Request $request): Response
    {
        $id = Uuid::uuid4();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App;

use Ramsey\Uuid;
use Symfony\Component;

final class Kernel
{
    public function handle(Component\HttpFoundation\Request $request): Component\HttpFoundation\Response
    {
        $id = Uuid\Uuid::uuid4();
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_DISCOVER_NAMESPACE_PREFIXES => true,
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

        $parentNamespacePrefixes = $this->parentNamespacePrefixes;

        if ($this->discoverNamespacePrefixes) {
            $discoveredParents = self::discoverParentNamespacePrefixesFromFile(
                $containerNode,
                $this->parentNamespacePrefixes,
                $this->namespacePrefixes,
            );

            $parentNamespacePrefixes = \array_merge(
                $this->parentNamespacePrefixes,
                $discoveredParents,
            );
        }

        $discoveredNamespacePrefixes = self::discoverNamespacePrefixesFromParentNamespacePrefixes(
            $containerNode,
            $parentNamespacePrefixes,
            $this->namespacePrefixes,
        );

        $namespacePrefixes = \array_merge(
            $this->namespacePrefixes,
            $discoveredNamespacePrefixes,
        );

        if (\count($namespacePrefixes) === 0) {
            return null;
        }

        $changed = false;

        foreach ($namespacePrefixes as $namespacePrefix) {
            $moreSpecificNamespacePrefixes = [];

            foreach ($namespacePrefixes as $otherNamespacePrefix) {
                if ($namespacePrefix->isNamespacePrefixOf($otherNamespacePrefix)) {
                    $moreSpecificNamespacePrefixes[] = $otherNamespacePrefix;
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
     * @param list<NamespacePrefix>                        $moreSpecificNamespacePrefixes
     */
    private function processNamespacePrefix(
        Node $containerNode,
        Rules\Files\NamespacePrefix $namespacePrefix,
        array $moreSpecificNamespacePrefixes
    ): bool {
        /** @var array<string, Reference> $aliasesToReferences */
        $aliasesToReferences = [];

        $hasNamespacePrefixImport = false;

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL === $statement->type) {
                    foreach ($statement->uses as $use) {
                        $alias = $use->getAlias()->toString();

                        $reference = Rules\Files\Reference::fromString($use->name->toString());

                        $aliasesToReferences[$alias] = $reference;

                        if ($reference->is($namespacePrefix)) {
                            $hasNamespacePrefixImport = true;
                        }
                    }
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                if (Node\Stmt\Use_::TYPE_NORMAL === $statement->type) {
                    $prefix = $statement->prefix->toString();

                    foreach ($statement->uses as $use) {
                        $alias = $use->getAlias()->toString();

                        $reference = Rules\Files\Reference::fromString(\sprintf(
                            '%s\\%s',
                            $prefix,
                            $use->name->toString(),
                        ));

                        $aliasesToReferences[$alias] = $reference;

                        if ($reference->is($namespacePrefix)) {
                            $hasNamespacePrefixImport = true;
                        }
                    }
                }
            }
        }

        $hasMatchingNamespacePrefixImports = self::hasMatchingNamespacePrefixImports(
            $containerNode,
            $namespacePrefix,
            $moreSpecificNamespacePrefixes,
        );

        $parentNamespacePrefixImport = self::hasParentNamespacePrefixImport(
            $containerNode,
            $namespacePrefix,
        );

        if (
            !$hasMatchingNamespacePrefixImports
            && !$parentNamespacePrefixImport
            && !$hasNamespacePrefixImport
            && !self::hasSourceWrittenFullyQualifiedReferencesMatchingPrefix($containerNode, $namespacePrefix, $moreSpecificNamespacePrefixes)
            && !(
                $this->forceRelativeReferences
                && self::hasPartiallyQualifiedReferencesMatchingNamespacePrefix($containerNode, $namespacePrefix, $moreSpecificNamespacePrefixes)
            )
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
            !$namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix
            && self::lastNamespaceSegmentOfNamespacePrefixCollidesWithExistingImport($containerNode, $namespacePrefix)
        ) {
            return false;
        }

        if (
            !$namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix
            && self::lastNamespaceSegmentOfNamespacePrefixCollidesWithDeclaredSymbol($containerNode, $namespacePrefix)
        ) {
            return false;
        }

        $statementsRewritten = $this->rewriteNamesInStatements(
            $containerNode,
            $namespacePrefix,
            $moreSpecificNamespacePrefixes,
            $namespacePrefixOfContainingFile,
        );

        $docBlocksRewritten = $this->rewriteNamesInDocBlocks(
            $containerNode,
            $aliasesToReferences,
            $namespacePrefix,
            $moreSpecificNamespacePrefixes,
            $namespacePrefixOfContainingFile,
        );

        if (
            !$hasMatchingNamespacePrefixImports
            && !$statementsRewritten
            && !$docBlocksRewritten
        ) {
            return false;
        }

        self::removeMatchingImportsAndAddNamespacePrefixImport(
            $containerNode,
            $namespacePrefix,
            $hasNamespacePrefixImport,
            $moreSpecificNamespacePrefixes,
            $namespacePrefixOfContainingFile,
        );

        return true;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $moreSpecificNamespacePrefixes
     */
    private static function hasMatchingNamespacePrefixImports(
        Node $containerNode,
        Rules\Files\NamespacePrefix $namespacePrefix,
        array $moreSpecificNamespacePrefixes
    ): bool {
        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                foreach ($statement->uses as $use) {
                    $reference = Rules\Files\Reference::fromString($use->name->toString());

                    if (
                        !$reference->is($namespacePrefix)
                        && $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)
                    ) {
                        return true;
                    }
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                $prefix = $statement->prefix->toString();

                foreach ($statement->uses as $use) {
                    $reference = Rules\Files\Reference::fromString(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    ));

                    if (
                        !$reference->is($namespacePrefix)
                        && $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)
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
    private static function hasParentNamespacePrefixImport(
        Node $containerNode,
        Rules\Files\NamespacePrefix $namespacePrefix
    ): bool {
        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $use) {
                    $namespacePrefixFromUse = Rules\Files\NamespacePrefix::fromString($use->name->toString());

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
                    $namespacePrefixFromUse = Rules\Files\NamespacePrefix::fromString(\sprintf(
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
     * @param list<NamespacePrefix>                        $moreSpecificNamespacePrefixes
     */
    private static function hasSourceWrittenFullyQualifiedReferencesMatchingPrefix(
        Node $containerNode,
        Rules\Files\NamespacePrefix $namespacePrefix,
        array $moreSpecificNamespacePrefixes
    ): bool {
        $nodeFinder = new NodeFinder();

        $match = $nodeFinder->findFirst(
            $containerNode->stmts,
            static function (Node $node) use ($namespacePrefix, $moreSpecificNamespacePrefixes): bool {
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

                $reference = Rules\Files\Reference::fromString($node->toString());

                return $reference->isOrIsDeclaredInOneOf($namespacePrefix)
                    && !$reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes);
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
     * @param list<NamespacePrefix>                        $moreSpecificNamespacePrefixes
     */
    private static function hasPartiallyQualifiedReferencesMatchingNamespacePrefix(
        Node $containerNode,
        Rules\Files\NamespacePrefix $namespacePrefix,
        array $moreSpecificNamespacePrefixes
    ): bool {
        $nodeFinder = new NodeFinder();

        $match = $nodeFinder->findFirst(
            $containerNode->stmts,
            static function (Node $node) use ($namespacePrefix, $moreSpecificNamespacePrefixes): bool {
                if (!$node instanceof Node\Name\FullyQualified) {
                    return false;
                }

                $originalName = $node->getAttribute('originalName');

                if (!$originalName instanceof Node\Name) {
                    return false;
                }

                if ($originalName instanceof Node\Name\FullyQualified) {
                    return false;
                }

                if (\strpos($originalName->toString(), '\\') === false) {
                    return false;
                }

                $reference = Rules\Files\Reference::fromString($node->toString());

                return $reference->isOrIsDeclaredInOneOf($namespacePrefix)
                    && !$reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes);
            },
        );

        if (null !== $match) {
            return true;
        }

        if (!$containerNode instanceof Node\Stmt\Namespace_) {
            return false;
        }

        if (null === $containerNode->name) {
            return false;
        }

        $fileNamespace = $containerNode->name->toString();

        $docBlockPartialPrefix = $namespacePrefix->toString();

        if (\strpos($docBlockPartialPrefix, $fileNamespace . '\\') === 0) {
            $docBlockPartialPrefix = \substr($docBlockPartialPrefix, \strlen($fileNamespace) + 1);
        }

        $matchInDocBlock = $nodeFinder->findFirst(
            $containerNode->stmts,
            static function (Node $node) use ($fileNamespace, $namespacePrefix, $moreSpecificNamespacePrefixes): bool {
                $docComment = $node->getDocComment();

                if (null === $docComment) {
                    return false;
                }

                $text = $docComment->getText();

                if (\preg_match_all('/(?:^|[^\\\\a-zA-Z0-9_])([A-Z][a-zA-Z0-9_]*(?:\\\\[A-Z][a-zA-Z0-9_]*)+)/', $text, $matches) === 0) {
                    return false;
                }

                foreach ($matches[1] as $partialName) {
                    $fullyQualified = $fileNamespace . '\\' . $partialName;

                    $reference = Rules\Files\Reference::fromString($fullyQualified);

                    if (
                        $reference->isOrIsDeclaredInOneOf($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)
                    ) {
                        return true;
                    }
                }

                return false;
            },
        );

        return null !== $matchInDocBlock;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $moreSpecificNamespacePrefixes
     */
    private function rewriteNamesInStatements(
        Node $containerNode,
        Rules\Files\NamespacePrefix $namespacePrefix,
        array $moreSpecificNamespacePrefixes,
        ?Rules\Files\NamespacePrefix $namespacePrefixOfContainingFile
    ): bool {
        $lastNamespaceSegmentOfNamespacePrefix = $namespacePrefix->lastNamespaceSegment();

        $hasChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, static function (Node $node) use ($namespacePrefix, $lastNamespaceSegmentOfNamespacePrefix, $moreSpecificNamespacePrefixes, $namespacePrefixOfContainingFile, &$hasChanged): ?Node {
            if (!$node instanceof Node\Name\FullyQualified) {
                return null;
            }

            $reference = Rules\Files\Reference::fromString($node->toString());

            if (
                !$reference->is($namespacePrefix)
                && !$reference->isDeclaredIn($namespacePrefix)
            ) {
                return null;
            }

            if ($reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)) {
                return null;
            }

            if ($namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix) {
                if ($reference->is($namespacePrefixOfContainingFile)) {
                    return null;
                }

                $rewrittenName = $reference->relativeTo($namespacePrefixOfContainingFile)->toString();

                $originalName = $node->getAttribute('originalName');

                if (
                    $originalName instanceof Node\Name
                    && $originalName->toString() === $rewrittenName
                ) {
                    return null;
                }

                $hasChanged = true;

                return new Node\Name($rewrittenName);
            }

            if ($reference->is($namespacePrefix)) {
                $rewrittenName = $lastNamespaceSegmentOfNamespacePrefix->toString();
            } else {
                $rewrittenName = \sprintf(
                    '%s\\%s',
                    $lastNamespaceSegmentOfNamespacePrefix->toString(),
                    $reference->relativeTo($namespacePrefix)->toString(),
                );
            }

            $originalName = $node->getAttribute('originalName');

            if (
                $originalName instanceof Node\Name
                && $originalName->toString() === $rewrittenName
            ) {
                return null;
            }

            $hasChanged = true;

            return new Node\Name($rewrittenName);
        });

        return $hasChanged;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param array<string, Reference>                     $aliasesToReferences
     * @param list<NamespacePrefix>                        $moreSpecificNamespacePrefixes
     */
    private function rewriteNamesInDocBlocks(
        Node $containerNode,
        array $aliasesToReferences,
        Rules\Files\NamespacePrefix $namespacePrefix,
        array $moreSpecificNamespacePrefixes,
        ?Rules\Files\NamespacePrefix $namespacePrefixOfContainingFile
    ): bool {
        $anyDocBlockChanged = false;

        $this->traverseNodesWithCallable($containerNode->stmts, function (Node $node) use (&$anyDocBlockChanged, $aliasesToReferences, $containerNode, $namespacePrefix, $moreSpecificNamespacePrefixes, $namespacePrefixOfContainingFile): ?Node {
            if ($node instanceof Node\Stmt\Use_) {
                return null;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                return null;
            }

            $hasChanged = false;

            $phpDocNodeTraverser = new PhpDocParser\PhpDocParser\PhpDocNodeTraverser();

            $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', static function (Ast\Node $phpDocNode) use ($aliasesToReferences, $containerNode, $namespacePrefix, $moreSpecificNamespacePrefixes, $namespacePrefixOfContainingFile, &$hasChanged): ?Ast\Type\IdentifierTypeNode {
                if (!$phpDocNode instanceof Ast\Type\IdentifierTypeNode) {
                    return null;
                }

                if (
                    $phpDocNode instanceof BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode
                    || \strpos($phpDocNode->name, '\\') === 0
                ) {
                    $reference = Rules\Files\Reference::fromString(\ltrim($phpDocNode->name, '\\'));

                    if (
                        !$reference->is($namespacePrefix)
                        && !$reference->isDeclaredIn($namespacePrefix)
                    ) {
                        return null;
                    }

                    if ($reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)) {
                        return null;
                    }

                    $hasChanged = true;

                    if ($namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix) {
                        if ($reference->is($namespacePrefixOfContainingFile)) {
                            return null;
                        }

                        return new Ast\Type\IdentifierTypeNode($reference->relativeTo($namespacePrefixOfContainingFile)->toString());
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
                    if (
                        \count($nameParts) < 2
                        || !$containerNode instanceof Node\Stmt\Namespace_
                        || null === $containerNode->name
                    ) {
                        return null;
                    }

                    $fullyQualifiedName = $containerNode->name->toString() . '\\' . $phpDocNode->name;

                    $reference = Rules\Files\Reference::fromString($fullyQualifiedName);

                    if (
                        !$reference->is($namespacePrefix)
                        && !$reference->isDeclaredIn($namespacePrefix)
                    ) {
                        return null;
                    }

                    if ($reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)) {
                        return null;
                    }

                    if ($namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix) {
                        if ($reference->is($namespacePrefixOfContainingFile)) {
                            return null;
                        }

                        $newName = $reference->relativeTo($namespacePrefixOfContainingFile)->toString();
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

                if ($reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)) {
                    return null;
                }

                if ($namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix) {
                    if ($reference->is($namespacePrefixOfContainingFile)) {
                        return null;
                    }

                    $newName = $reference->relativeTo($namespacePrefixOfContainingFile)->toString();
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
        Rules\Files\NamespacePrefix $namespacePrefix
    ): bool {
        $lastNamespaceSegment = $namespacePrefix->lastNamespaceSegment();

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $useStatement) {
                    $reference = Rules\Files\Reference::fromString($useStatement->name->toString());

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
                    $reference = Rules\Files\Reference::fromString(\sprintf(
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
        Rules\Files\NamespacePrefix $namespacePrefix
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
        Rules\Files\NamespacePrefix $namespacePrefix
    ): ?Rules\Files\NamespacePrefix {
        if (!$containerNode instanceof Node\Stmt\Namespace_) {
            return null;
        }

        if (null === $containerNode->name) {
            return null;
        }

        $fileNamespace = $containerNode->name->toString();

        if ($namespacePrefix->toString() === $fileNamespace) {
            return Rules\Files\NamespacePrefix::fromString($fileNamespace);
        }

        if (\strpos($namespacePrefix->toString(), $fileNamespace . '\\') === 0) {
            return Rules\Files\NamespacePrefix::fromString($fileNamespace);
        }

        return null;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $existingParentNamespacePrefixes
     * @param list<NamespacePrefix>                        $existingNamespacePrefixes
     *
     * @return list<NamespacePrefix>
     */
    private static function discoverParentNamespacePrefixesFromFile(
        Node $containerNode,
        array $existingParentNamespacePrefixes,
        array $existingNamespacePrefixes
    ): array {
        /** @var array<string, NamespacePrefix> $discovered */
        $discovered = [];

        $collectFirstSegment = static function (string $reference) use (&$discovered): void {
            $parts = \explode('\\', $reference);

            if (\count($parts) < 2) {
                return;
            }

            $firstSegment = $parts[0];

            if (\array_key_exists($firstSegment, $discovered)) {
                return;
            }

            try {
                $discovered[$firstSegment] = Rules\Files\NamespacePrefix::fromString($firstSegment);
            } catch (\InvalidArgumentException $exception) {
                return;
            }
        };

        if (
            $containerNode instanceof Node\Stmt\Namespace_
            && null !== $containerNode->name
        ) {
            $collectFirstSegment($containerNode->name->toString());
        }

        foreach ($containerNode->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                foreach ($statement->uses as $use) {
                    $collectFirstSegment($use->name->toString());
                }
            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                if (Node\Stmt\Use_::TYPE_NORMAL !== $statement->type) {
                    continue;
                }

                $prefix = $statement->prefix->toString();

                foreach ($statement->uses as $use) {
                    $collectFirstSegment(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    ));
                }
            }
        }

        $nodeFinder = new NodeFinder();

        $nodeFinder->find(
            $containerNode->stmts,
            static function (Node $node) use ($collectFirstSegment): bool {
                if (!$node instanceof Node\Name\FullyQualified) {
                    return false;
                }

                $collectFirstSegment($node->toString());

                return false;
            },
        );

        $nodeFinder->find(
            $containerNode->stmts,
            static function (Node $node) use (&$discovered): bool {
                $docComment = $node->getDocComment();

                if (null === $docComment) {
                    return false;
                }

                $text = $docComment->getText();

                $pattern = '/\\\\([a-zA-Z_][a-zA-Z0-9_]*)\\\\[a-zA-Z_]/';

                if (\preg_match_all($pattern, $text, $matches) > 0) {
                    foreach ($matches[1] as $segmentString) {
                        if (\array_key_exists($segmentString, $discovered)) {
                            continue;
                        }

                        try {
                            $discovered[$segmentString] = Rules\Files\NamespacePrefix::fromString($segmentString);
                        } catch (\InvalidArgumentException $exception) {
                            continue;
                        }
                    }
                }

                return false;
            },
        );

        foreach ($discovered as $key => $discoveredPrefix) {
            foreach ($existingParentNamespacePrefixes as $existingParent) {
                if (
                    $discoveredPrefix->isNamespacePrefixOf($existingParent)
                    || $existingParent->isNamespacePrefixOf($discoveredPrefix)
                    || $discoveredPrefix->toString() === $existingParent->toString()
                ) {
                    unset($discovered[$key]);

                    break;
                }
            }
        }

        foreach ($discovered as $key => $discoveredPrefix) {
            foreach ($existingNamespacePrefixes as $existingPrefix) {
                if (
                    $discoveredPrefix->isNamespacePrefixOf($existingPrefix)
                    || $existingPrefix->isNamespacePrefixOf($discoveredPrefix)
                    || $discoveredPrefix->toString() === $existingPrefix->toString()
                ) {
                    unset($discovered[$key]);

                    break;
                }
            }
        }

        return \array_values($discovered);
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $parentNamespacePrefixes
     * @param list<NamespacePrefix>                        $namespacePrefixes
     *
     * @return list<NamespacePrefix>
     */
    private static function discoverNamespacePrefixesFromParentNamespacePrefixes(
        Node $containerNode,
        array $parentNamespacePrefixes,
        array $namespacePrefixes
    ): array {
        if (\count($parentNamespacePrefixes) === 0) {
            return [];
        }

        /** @var array<string, NamespacePrefix> $discoveredNamespacePrefixes */
        $discoveredNamespacePrefixes = [];

        $existingKeys = [];

        foreach ($namespacePrefixes as $existingPrefix) {
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
                        $discoveredNamespacePrefixes,
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
                        $discoveredNamespacePrefixes,
                    );
                }
            }
        }

        $nodeFinder = new NodeFinder();

        $nodeFinder->find(
            $containerNode->stmts,
            static function (Node $node) use ($parentNamespacePrefixes, &$existingKeys, &$discoveredNamespacePrefixes): bool {
                if (!$node instanceof Node\Name\FullyQualified) {
                    return false;
                }

                self::discoverChildPrefix(
                    $node->toString(),
                    $parentNamespacePrefixes,
                    $existingKeys,
                    $discoveredNamespacePrefixes,
                );

                return false;
            },
        );

        $nodeFinder->find(
            $containerNode->stmts,
            static function (Node $node) use ($parentNamespacePrefixes, &$existingKeys, &$discoveredNamespacePrefixes): bool {
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
                            $segment = Rules\Files\NamespaceSegment::fromString($segmentString);
                            $childPrefix = $parentNamespacePrefix->append($segment);
                            $childKey = $childPrefix->toString();

                            if (!\array_key_exists($childKey, $discoveredNamespacePrefixes) && !\array_key_exists($childKey, $existingKeys)) {
                                $discoveredNamespacePrefixes[$childKey] = $childPrefix;
                            }
                        }
                    }
                }

                return false;
            },
        );

        return \array_values($discoveredNamespacePrefixes);
    }

    /**
     * @param list<NamespacePrefix>          $parentNamespacePrefixes
     * @param array<string, true>            $existingKeys
     * @param array<string, NamespacePrefix> $discovered
     */
    private static function discoverChildPrefix(
        string $reference,
        array $parentNamespacePrefixes,
        array $existingKeys,
        array &$discovered
    ): void {
        foreach ($parentNamespacePrefixes as $parentNamespacePrefix) {
            $parentString = $parentNamespacePrefix->toString();

            if (\strpos($reference, $parentString . '\\') !== 0) {
                continue;
            }

            $remaining = \substr($reference, \strlen($parentString) + 1);
            $parts = \explode('\\', $remaining);

            $segment = Rules\Files\NamespaceSegment::fromString($parts[0]);
            $childPrefix = $parentNamespacePrefix->append($segment);
            $childKey = $childPrefix->toString();

            if (!\array_key_exists($childKey, $discovered) && !\array_key_exists($childKey, $existingKeys)) {
                $discovered[$childKey] = $childPrefix;
            }
        }
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     * @param list<NamespacePrefix>                        $moreSpecificNamespacePrefixes
     */
    private static function removeMatchingImportsAndAddNamespacePrefixImport(
        Node $containerNode,
        Rules\Files\NamespacePrefix $namespacePrefix,
        bool $hasNamespacePrefixImport,
        array $moreSpecificNamespacePrefixes,
        ?Rules\Files\NamespacePrefix $namespacePrefixOfContainingFile
    ): void {
        /** @var ?int $firstMatchIndex */
        $firstMatchIndex = null;

        /** @var list<int> $indicesToRemove */
        $indicesToRemove = [];

        foreach ($containerNode->stmts as $index => $stmt) {
            if ($stmt instanceof Node\Stmt\Use_) {
                $remainingUses = [];

                foreach ($stmt->uses as $use) {
                    $reference = Rules\Files\Reference::fromString($use->name->toString());

                    if ($reference->is($namespacePrefix)) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        if (!$namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix) {
                            $remainingUses[] = $use;
                        }

                        continue;
                    }

                    if (
                        $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)
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
                    $reference = Rules\Files\Reference::fromString(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    ));

                    if ($reference->is($namespacePrefix)) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        if (!$namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix) {
                            $remainingUses[] = $use;
                        }

                        continue;
                    }

                    if (
                        $reference->isDeclaredIn($namespacePrefix)
                        && !$reference->isOrIsDeclaredInOneOf(...$moreSpecificNamespacePrefixes)
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

        if ($namespacePrefixOfContainingFile instanceof Rules\Files\NamespacePrefix) {
            foreach (\array_reverse($indicesToRemove) as $index) {
                \array_splice(
                    $containerNode->stmts,
                    $index,
                    1,
                );
            }

            return;
        }

        if (!$hasNamespacePrefixImport && null !== $firstMatchIndex) {
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
            !$hasNamespacePrefixImport
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
                        $namespacePrefixFromUse = Rules\Files\NamespacePrefix::fromString($use->name->toString());

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
                        $namespacePrefixFromUse = Rules\Files\NamespacePrefix::fromString(\sprintf(
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
                $insertBeforeIndex = null;
                $lastUseIndex = null;

                foreach ($containerNode->stmts as $index => $statement) {
                    if (
                        $statement instanceof Node\Stmt\Use_
                        || $statement instanceof Node\Stmt\GroupUse
                    ) {
                        $lastUseIndex = $index;

                        if (null === $insertBeforeIndex) {
                            $existingUseName = null;

                            if ($statement instanceof Node\Stmt\Use_) {
                                foreach ($statement->uses as $use) {
                                    $existingUseName = $use->name->toString();

                                    break;
                                }
                            } elseif ($statement instanceof Node\Stmt\GroupUse) {
                                $existingUseName = $statement->prefix->toString();
                            }

                            if (
                                null !== $existingUseName
                                && \strcmp($namespacePrefix->toString(), $existingUseName) < 0
                            ) {
                                $insertBeforeIndex = $index;
                            }
                        }
                    }
                }

                $prefixUseStatement = new Node\Stmt\Use_([
                    new Node\UseItem(new Node\Name($namespacePrefix->toString())),
                ]);

                if (null !== $insertBeforeIndex) {
                    \array_splice(
                        $containerNode->stmts,
                        (int) $insertBeforeIndex,
                        0,
                        [
                            $prefixUseStatement,
                        ],
                    );
                } elseif (null !== $lastUseIndex) {
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
