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
use PHPStan\PhpDocParser\Ast;
use Rector\BetterPhpDocParser;
use Rector\Comments;
use Rector\Contract;
use Rector\NodeTypeResolver;
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

    /**
     * @see https://www.php.net/manual/en/reserved.other-reserved-words.php
     */
    private BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory;
    private Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater;
    private FileReader $fileReader;
    private NamespacePrefixDiscoverer $namespacePrefixDiscoverer;
    private NamespacePrefixPlanner $namespacePrefixPlanner;
    private bool $discoverNamespacePrefixes = false;
    private bool $forceRelativeReferences = false;
    private NamespacePrefixes $namespacePrefixes;
    private NamespacePrefixes $parentNamespacePrefixes;

    public function __construct(
        BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory,
        Comments\NodeDocBlock\DocBlockUpdater $docBlockUpdater
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;

        $nameResolver = new NameResolver();

        $this->fileReader = new FileReader(
            $phpDocInfoFactory,
            $nameResolver,
        );
        $this->namespacePrefixDiscoverer = new NamespacePrefixDiscoverer();
        $this->namespacePrefixPlanner = new NamespacePrefixPlanner($nameResolver);
        $this->namespacePrefixes = NamespacePrefixes::create();
        $this->parentNamespacePrefixes = NamespacePrefixes::create();
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
                $namespacePrefix = NamespacePrefix::fromString($value);
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
                $parentNamespacePrefix = NamespacePrefix::fromString($value);
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
        $this->namespacePrefixes = NamespacePrefixes::create(...$namespacePrefixes);
        $this->parentNamespacePrefixes = NamespacePrefixes::create(...$parentNamespacePrefixes);
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
                Rules\Configuration\OptionDescription::fromString('Force references to be expressed relative to the namespace prefix even when they could be expressed relative to the namespace of the file.'),
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
                                private Baz $baz,
                                private Baz\Qux $qux,
                                private Core\Quz $quz,
                            ) {
                            }
                        }
                        CODE_SAMPLE
                    ,
                    [
                        self::CONFIGURATION_KEY_NAMESPACE_PREFIXES => [
                            'Example\Core',
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

        $file = $this->fileReader->read($containerNode);

        $parentNamespacePrefixes = $this->parentNamespacePrefixes;

        if ($this->discoverNamespacePrefixes) {
            $parentNamespacePrefixes = $this->parentNamespacePrefixes->merge($this->namespacePrefixDiscoverer->discoverParentNamespacePrefixes(
                $file,
                $this->parentNamespacePrefixes,
                $this->namespacePrefixes,
            ));
        }

        $discoveredNamespacePrefixes = $this->namespacePrefixDiscoverer->discoverNamespacePrefixes(
            $file,
            $parentNamespacePrefixes,
            $this->namespacePrefixes,
        );

        $namespacePrefixes = $this->namespacePrefixes->merge($discoveredNamespacePrefixes);

        if ($namespacePrefixes->isEmpty()) {
            return null;
        }

        $changed = false;

        $pendingNamespacePrefixes = $namespacePrefixes->toArray();

        do {
            $changedInPass = false;

            foreach ($pendingNamespacePrefixes as $index => $namespacePrefix) {
                if ($this->processNamespacePrefix($containerNode, $namespacePrefix, $namespacePrefixes->moreSpecificThan($namespacePrefix))) {
                    $changed = true;
                    $changedInPass = true;

                    unset($pendingNamespacePrefixes[$index]);
                }
            }
        } while (
            $changedInPass
            && \count($pendingNamespacePrefixes) > 0
        );

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
        NamespacePrefix $namespacePrefix,
        NamespacePrefixes $moreSpecificNamespacePrefixes
    ): bool {
        $namespacePrefixPlan = $this->namespacePrefixPlanner->plan(
            $this->fileReader->read($containerNode),
            $namespacePrefix,
            $moreSpecificNamespacePrefixes,
            $this->forceRelativeReferences,
        );

        if (!$namespacePrefixPlan instanceof NamespacePrefixPlan) {
            return false;
        }

        $this->respell(
            $containerNode,
            $namespacePrefixPlan->respellings(),
        );

        $replacedImport = $namespacePrefixPlan->replacedImport();

        if ($replacedImport instanceof Import) {
            $useItem = $replacedImport->useItem();

            if ($useItem instanceof Node\UseItem) {
                self::removeImport(
                    $containerNode,
                    $useItem,
                );
            }
        }

        self::removeMatchingImportsAndAddNamespacePrefixImport(
            $containerNode,
            $namespacePrefix,
            $namespacePrefixPlan->hasNamespacePrefixImport(),
            $moreSpecificNamespacePrefixes,
            $namespacePrefixPlan->requiresNamespacePrefixImport(),
        );

        return true;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private function respell(
        Node $containerNode,
        Respellings $respellings
    ): void {
        /** @var array<int, Respelling> $respellingsOfNames */
        $respellingsOfNames = [];

        /** @var array<int, array{0: Node, 1: array<int, string>}> $spellingsOfTypeNodesByNode */
        $spellingsOfTypeNodesByNode = [];

        foreach ($respellings->toArray() as $respelling) {
            $symbolReference = $respelling->symbolReference();

            $phpDocNode = $symbolReference->phpDocNode();

            if ($phpDocNode instanceof Ast\Node) {
                $node = $symbolReference->node();

                $identifier = \spl_object_id($node);

                if (!\array_key_exists($identifier, $spellingsOfTypeNodesByNode)) {
                    $spellingsOfTypeNodesByNode[$identifier] = [
                        $node,
                        [],
                    ];
                }

                $spellingsOfTypeNodesByNode[$identifier][1][\spl_object_id($phpDocNode)] = $respelling->spelling()->toString();

                continue;
            }

            $respellingsOfNames[\spl_object_id($symbolReference->node())] = $respelling;
        }

        if ([] !== $respellingsOfNames) {
            $this->traverseNodesWithCallable($containerNode->stmts, static function (Node $node) use ($respellingsOfNames): ?Node {
                if (!$node instanceof Node\Name) {
                    return null;
                }

                $identifier = \spl_object_id($node);

                if (!\array_key_exists($identifier, $respellingsOfNames)) {
                    return null;
                }

                $respelling = $respellingsOfNames[$identifier];

                $name = new Node\Name($respelling->spelling()->toString());

                $fullyQualifiedName = $respelling->symbolReference()->fullyQualifiedName();

                if ($fullyQualifiedName instanceof FullyQualifiedName) {
                    $name->setAttribute(
                        NodeTypeResolver\Node\AttributeKey::NAMESPACED_NAME,
                        $fullyQualifiedName->toString(),
                    );
                }

                return $name;
            });
        }

        foreach ($spellingsOfTypeNodesByNode as [$node, $spellingsOfTypeNodes]) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                continue;
            }

            $phpDocNodeTraverser = new Ast\NodeTraverser([
                new PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor(static function (Ast\Node $phpDocNode) use ($spellingsOfTypeNodes): ?Ast\Node {
                    $identifier = \spl_object_id($phpDocNode);

                    if (!\array_key_exists($identifier, $spellingsOfTypeNodes)) {
                        return null;
                    }

                    if ($phpDocNode instanceof Ast\ConstExpr\ConstFetchNode) {
                        return new Ast\ConstExpr\ConstFetchNode(
                            $spellingsOfTypeNodes[$identifier],
                            $phpDocNode->name,
                        );
                    }

                    if ($phpDocNode instanceof Ast\Type\IdentifierTypeNode) {
                        return new Ast\Type\IdentifierTypeNode($spellingsOfTypeNodes[$identifier]);
                    }

                    return null;
                }),
            ]);

            $phpDocNodeTraverser->traverse([
                $phpDocInfo->getPhpDocNode(),
            ]);

            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        }
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private static function removeImport(
        Node $containerNode,
        Node\UseItem $import
    ): void {
        foreach ($containerNode->stmts as $index => $statement) {
            if (
                !$statement instanceof Node\Stmt\Use_
                && !$statement instanceof Node\Stmt\GroupUse
            ) {
                continue;
            }

            $remainingUses = \array_values(\array_filter($statement->uses, static function (Node\UseItem $use) use ($import): bool {
                return $use !== $import;
            }));

            if (\count($remainingUses) === \count($statement->uses)) {
                continue;
            }

            if (\count($remainingUses) === 0) {
                \array_splice(
                    $containerNode->stmts,
                    $index,
                    1,
                );

                return;
            }

            $statement->uses = $remainingUses;

            return;
        }
    }

    /**
     * @return list<Node\UseItem>
     */
    private static function classImportItems(Node\Stmt\GroupUse $groupUse): array
    {
        if (Node\Stmt\Use_::TYPE_NORMAL === $groupUse->type) {
            return \array_values($groupUse->uses);
        }

        if (Node\Stmt\Use_::TYPE_UNKNOWN !== $groupUse->type) {
            return [];
        }

        return \array_values(\array_filter($groupUse->uses, static function (Node\UseItem $useItem): bool {
            return Node\Stmt\Use_::TYPE_NORMAL === $useItem->type;
        }));
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    private static function removeMatchingImportsAndAddNamespacePrefixImport(
        Node $containerNode,
        NamespacePrefix $namespacePrefix,
        bool $hasNamespacePrefixImport,
        NamespacePrefixes $moreSpecificNamespacePrefixes,
        bool $requiresNamespacePrefixImport
    ): void {
        /** @var ?int $firstMatchIndex */
        $firstMatchIndex = null;

        /** @var list<int> $indicesToRemove */
        $indicesToRemove = [];

        foreach ($containerNode->stmts as $index => $stmt) {
            if ($stmt instanceof Node\Stmt\Use_) {
                if (
                    Node\Stmt\Use_::TYPE_FUNCTION === $stmt->type
                    || Node\Stmt\Use_::TYPE_CONSTANT === $stmt->type
                ) {
                    continue;
                }

                $remainingUses = [];

                foreach ($stmt->uses as $use) {
                    $fullyQualifiedName = FullyQualifiedName::fromString($use->name->toString());

                    if ($fullyQualifiedName->is($namespacePrefix)) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        if ($requiresNamespacePrefixImport) {
                            $remainingUses[] = $use;
                        }

                        continue;
                    }

                    if (
                        $fullyQualifiedName->isDeclaredIn($namespacePrefix)
                        && !$fullyQualifiedName->isOrIsDeclaredInOneOf($moreSpecificNamespacePrefixes)
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
                $classImportItems = self::classImportItems($stmt);

                if (\count($classImportItems) === 0) {
                    continue;
                }

                $prefix = $stmt->prefix->toString();

                $remainingUses = [];

                foreach ($stmt->uses as $use) {
                    if (!\in_array($use, $classImportItems, true)) {
                        $remainingUses[] = $use;

                        continue;
                    }

                    $fullyQualifiedName = FullyQualifiedName::fromString(\sprintf(
                        '%s\\%s',
                        $prefix,
                        $use->name->toString(),
                    ));

                    if ($fullyQualifiedName->is($namespacePrefix)) {
                        if (null === $firstMatchIndex) {
                            $firstMatchIndex = $index;
                        }

                        if ($requiresNamespacePrefixImport) {
                            $remainingUses[] = $use;
                        }

                        continue;
                    }

                    if (
                        $fullyQualifiedName->isDeclaredIn($namespacePrefix)
                        && !$fullyQualifiedName->isOrIsDeclaredInOneOf($moreSpecificNamespacePrefixes)
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
                } elseif (\count($remainingUses) !== \count($stmt->uses)) {
                    $containerNode->stmts[$index] = new Node\Stmt\GroupUse(
                        new Node\Name($stmt->prefix->toString()),
                        \array_map(static function (Node\UseItem $use): Node\UseItem {
                            return new Node\UseItem(
                                new Node\Name($use->name->toString()),
                                $use->alias,
                                $use->type,
                            );
                        }, $remainingUses),
                        $stmt->type,
                        [
                            'comments' => $stmt->getComments(),
                        ],
                    );
                }
            }
        }

        if (!$requiresNamespacePrefixImport) {
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
                    $prefixUseStatement = new Node\Stmt\Use_(
                        [
                            new Node\UseItem(new Node\Name($namespacePrefix->toString())),
                        ],
                        Node\Stmt\Use_::TYPE_NORMAL,
                        [
                            'comments' => $firstMatchNode->getComments(),
                        ],
                    );

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
                        $namespacePrefixFromUse = NamespacePrefix::fromString($use->name->toString());

                        if ($namespacePrefixFromUse->isNamespacePrefixOf($namespacePrefix)) {
                            $parentImportIndex = $index;

                            break 2;
                        }
                    }
                } elseif ($statement instanceof Node\Stmt\GroupUse) {
                    $prefix = $statement->prefix->toString();

                    foreach (self::classImportItems($statement) as $use) {
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
