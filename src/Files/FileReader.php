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

use PhpParser\Comment;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPStan\PhpDocParser\Ast;
use Rector\BetterPhpDocParser;
use Rector\PhpDocParser;
use Rector\PhpParser;

/**
 * @internal
 */
final class FileReader
{
    private const KEYWORDS_IN_DOC_BLOCK_TYPES = [
        'array',
        'bool',
        'boolean',
        'callable',
        'double',
        'false',
        'float',
        'int',
        'integer',
        'iterable',
        'list',
        'max',
        'min',
        'mixed',
        'never',
        'noreturn',
        'null',
        'numeric',
        'object',
        'parent',
        'resource',
        'scalar',
        'self',
        'static',
        'string',
        'true',
        'void',
    ];
    private const PATTERN_QUALIFIED_NAME_IN_TEXT = '/(?<![\\\\\w$])(\\\\?[a-zA-Z_][a-zA-Z0-9_]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)+)/';
    private BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory;
    private NameResolver $nameResolver;

    public function __construct(
        BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory,
        NameResolver $nameResolver
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @param Node\Stmt\Namespace_|PhpParser\Node\FileNode $containerNode
     */
    public function read(Node $containerNode): File
    {
        $namespace = FileNamespace::global();

        if (
            $containerNode instanceof Node\Stmt\Namespace_
            && null !== $containerNode->name
        ) {
            $namespace = FileNamespace::named(NamespacePrefix::fromString($containerNode->name->toString()));
        }

        $statements = \array_values($containerNode->stmts);

        $imports = self::imports($statements);

        $symbolReferences = \array_merge(
            $this->symbolReferencesInCode(
                $statements,
                $namespace,
                $imports,
            ),
            $this->symbolReferencesInDocBlocks(
                $statements,
                $namespace,
                $imports,
            ),
            $this->symbolReferencesInAttributeComments(
                $statements,
                $namespace,
                $imports,
            ),
        );

        return File::create(
            $namespace,
            $imports,
            self::declaredClassLikeNames($statements),
            SymbolReferences::create(...$symbolReferences),
        );
    }

    /**
     * @param list<Node\Stmt> $statements
     */
    private static function imports(array $statements): Imports
    {
        $imports = [];

        foreach ($statements as $statement) {
            if ($statement instanceof Node\Stmt\Use_) {
                $symbolKind = self::symbolKindOfImportType($statement->type);

                if (!$symbolKind instanceof SymbolKind) {
                    continue;
                }

                foreach ($statement->uses as $useItem) {
                    $imports[] = Import::fromUseItem(
                        $symbolKind,
                        $statement,
                        $useItem,
                    );
                }

                continue;
            }

            if ($statement instanceof Node\Stmt\GroupUse) {
                foreach ($statement->uses as $useItem) {
                    $type = $statement->type;

                    if (Node\Stmt\Use_::TYPE_UNKNOWN === $type) {
                        $type = $useItem->type;
                    }

                    $symbolKind = self::symbolKindOfImportType($type);

                    if (!$symbolKind instanceof SymbolKind) {
                        continue;
                    }

                    $imports[] = Import::fromUseItem(
                        $symbolKind,
                        $statement,
                        $useItem,
                    );
                }
            }
        }

        return Imports::create(...$imports);
    }

    private static function symbolKindOfImportType(int $type): ?SymbolKind
    {
        if (Node\Stmt\Use_::TYPE_NORMAL === $type) {
            return SymbolKind::classLike();
        }

        if (Node\Stmt\Use_::TYPE_FUNCTION === $type) {
            return SymbolKind::function();
        }

        if (Node\Stmt\Use_::TYPE_CONSTANT === $type) {
            return SymbolKind::constant();
        }

        return null;
    }

    /**
     * @param list<Node\Stmt> $statements
     */
    private static function declaredClassLikeNames(array $statements): ClassLikeNames
    {
        $declaredClassLikeNames = [];

        foreach ($statements as $statement) {
            if (
                !$statement instanceof Node\Stmt\Class_
                && !$statement instanceof Node\Stmt\Interface_
                && !$statement instanceof Node\Stmt\Trait_
                && !$statement instanceof Node\Stmt\Enum_
            ) {
                continue;
            }

            if (null === $statement->name) {
                continue;
            }

            $declaredClassLikeNames[] = ClassLikeName::fromString($statement->name->toString());
        }

        return ClassLikeNames::create(...$declaredClassLikeNames);
    }

    /**
     * @param list<Node\Stmt> $statements
     *
     * @return list<SymbolReference>
     */
    private function symbolReferencesInCode(
        array $statements,
        FileNamespace $namespace,
        Imports $imports
    ): array {
        $nodeFinder = new NodeFinder();

        /** @var array<int, SymbolKind> $symbolKinds */
        $symbolKinds = [];

        /** @var array<int, true> $namesInImports */
        $namesInImports = [];

        foreach ($nodeFinder->find($statements, static function (Node $node): bool {
            return $node instanceof Node\Expr\FuncCall
                || $node instanceof Node\Expr\ConstFetch
                || $node instanceof Node\UseItem
                || $node instanceof Node\Stmt\GroupUse;
        }) as $node) {
            if (
                $node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Name
            ) {
                $symbolKinds[\spl_object_id($node->name)] = SymbolKind::function();
            } elseif ($node instanceof Node\Expr\ConstFetch) {
                $symbolKinds[\spl_object_id($node->name)] = SymbolKind::constant();
            } elseif ($node instanceof Node\UseItem) {
                $namesInImports[\spl_object_id($node->name)] = true;
            } elseif ($node instanceof Node\Stmt\GroupUse) {
                $namesInImports[\spl_object_id($node->prefix)] = true;
            }
        }

        $symbolReferences = [];

        /** @var list<Node\Name> $names */
        $names = $nodeFinder->findInstanceOf(
            $statements,
            Node\Name::class,
        );

        foreach ($names as $name) {
            $identifier = \spl_object_id($name);

            if (\array_key_exists($identifier, $namesInImports)) {
                continue;
            }

            $symbolKind = SymbolKind::classLike();

            if (\array_key_exists($identifier, $symbolKinds)) {
                $symbolKind = $symbolKinds[$identifier];
            }

            $spelling = Spelling::fromString(self::spelling($name));

            $symbolReferences[] = SymbolReference::inCode(
                $symbolKind,
                $spelling,
                $this->nameResolver->resolve(
                    $spelling,
                    $symbolKind,
                    $namespace,
                    $imports,
                ),
                $name,
            );
        }

        return $symbolReferences;
    }

    /**
     * PHP 7.4 parses an attribute as a comment.
     *
     * @param list<Node\Stmt> $statements
     *
     * @return list<SymbolReference>
     */
    private function symbolReferencesInAttributeComments(
        array $statements,
        FileNamespace $namespace,
        Imports $imports
    ): array {
        $nodeFinder = new NodeFinder();

        $nodesWithAttributeComment = $nodeFinder->find($statements, static function (Node $node): bool {
            foreach ($node->getComments() as $comment) {
                if (self::isAttributeComment($comment)) {
                    return true;
                }
            }

            return false;
        });

        if ([] === $nodesWithAttributeComment) {
            return [];
        }

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromComponents(8, 0));

        $code = "<?php\n";

        $symbolReferences = [];

        /** @var array<int, true> $startFilePositionsOfAttributeComments */
        $startFilePositionsOfAttributeComments = [];

        foreach ($nodesWithAttributeComment as $nodeWithAttributeComment) {
            foreach ($nodeWithAttributeComment->getComments() as $comment) {
                if (!self::isAttributeComment($comment)) {
                    continue;
                }

                if (\array_key_exists($comment->getStartFilePos(), $startFilePositionsOfAttributeComments)) {
                    continue;
                }

                $startFilePositionsOfAttributeComments[$comment->getStartFilePos()] = true;

                try {
                    $statementsInAttributeComment = $parser->parse($code . $comment->getText() . "\nfunction f() {}\n");
                } catch (Error $exception) {
                    continue;
                }

                if (null === $statementsInAttributeComment) {
                    continue;
                }

                $symbolReferencesInAttributeComment = $this->symbolReferencesInCode(
                    \array_values($statementsInAttributeComment),
                    $namespace,
                    $imports,
                );

                foreach ($symbolReferencesInAttributeComment as $symbolReference) {
                    $name = $symbolReference->node();

                    if (!$name instanceof Node\Name) {
                        continue;
                    }

                    $name->setAttribute('startFilePos', $name->getStartFilePos() - \strlen($code));
                    $name->setAttribute('endFilePos', $name->getEndFilePos() - \strlen($code));

                    $symbolReferences[] = SymbolReference::inAttributeComment(
                        $symbolReference->symbolKind(),
                        $symbolReference->spelling(),
                        $symbolReference->fullyQualifiedName(),
                        $name,
                        $nodeWithAttributeComment,
                        $comment,
                    );
                }
            }
        }

        return $symbolReferences;
    }

    private static function isAttributeComment(Comment $comment): bool
    {
        return !$comment instanceof Comment\Doc
            && 0 === \strpos($comment->getText(), '#[');
    }

    private static function spelling(Node\Name $name): string
    {
        $originalName = $name->getAttribute('originalName');

        if ($originalName instanceof Node\Name) {
            $name = $originalName;
        }

        if ($name instanceof Node\Name\FullyQualified) {
            return '\\' . $name->toString();
        }

        if ($name instanceof Node\Name\Relative) {
            return 'namespace\\' . $name->toString();
        }

        return $name->toString();
    }

    /**
     * @param list<Node\Stmt> $statements
     *
     * @return list<SymbolReference>
     */
    private function symbolReferencesInDocBlocks(
        array $statements,
        FileNamespace $namespace,
        Imports $imports
    ): array {
        $nodeFinder = new NodeFinder();

        $nodesWithDocBlock = $nodeFinder->find($statements, static function (Node $node): bool {
            if (
                $node instanceof Node\Stmt\Use_
                || $node instanceof Node\Stmt\GroupUse
            ) {
                return false;
            }

            return null !== $node->getDocComment();
        });

        $localTypeNamesOfNodes = $this->localTypeNamesOfNodes($statements);

        $symbolReferences = [];

        foreach ($nodesWithDocBlock as $node) {
            $docComment = $node->getDocComment();

            if (null === $docComment) {
                continue;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

            /** @var list<Ast\ConstExpr\ConstFetchNode|Ast\Type\IdentifierTypeNode> $phpDocNodes */
            $phpDocNodes = [];

            /**
             * @var array<int, true> $keysOfShapes
             */
            $keysOfShapes = [];

            if ($phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                $phpDocNodeTraverser = new Ast\NodeTraverser([
                    new PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor(static function (Ast\Node $phpDocNode) use (&$keysOfShapes, &$phpDocNodes): ?Ast\Node {
                        if (
                            $phpDocNode instanceof Ast\Type\ArrayShapeItemNode
                            || $phpDocNode instanceof Ast\Type\ObjectShapeItemNode
                        ) {
                            if (null !== $phpDocNode->keyName) {
                                $keysOfShapes[\spl_object_id($phpDocNode->keyName)] = true;
                            }

                            return null;
                        }

                        if (
                            $phpDocNode instanceof Ast\Type\IdentifierTypeNode
                            && !\array_key_exists(\spl_object_id($phpDocNode), $keysOfShapes)
                        ) {
                            $phpDocNodes[] = $phpDocNode;

                            return null;
                        }

                        if (
                            $phpDocNode instanceof Ast\ConstExpr\ConstFetchNode
                            && '' !== $phpDocNode->className
                        ) {
                            $phpDocNodes[] = $phpDocNode;
                        }

                        return null;
                    }),
                ]);

                $phpDocNodeTraverser->traverse([
                    $phpDocInfo->getPhpDocNode(),
                ]);
            }

            /** @var array<string, int> $spellingsInTypes */
            $spellingsInTypes = [];

            $localTypeNames = [];

            if (\array_key_exists(\spl_object_id($node), $localTypeNamesOfNodes)) {
                $localTypeNames = $localTypeNamesOfNodes[\spl_object_id($node)];
            }

            foreach ($phpDocNodes as $phpDocNode) {
                $name = $phpDocNode->name;

                if ($phpDocNode instanceof Ast\ConstExpr\ConstFetchNode) {
                    $name = $phpDocNode->className;
                }

                $spelling = Spelling::fromString($name);

                if (
                    $spelling->isUnqualified()
                    && !$imports->ofSymbolKind(SymbolKind::classLike())->forLocalName(LocalName::fromString($name)) instanceof Import
                    && (
                        self::isKeywordInDocBlockType($name)
                        || \array_key_exists($name, $localTypeNames)
                    )
                ) {
                    continue;
                }

                $symbolReferences[] = SymbolReference::inDocBlockType(
                    $spelling,
                    $this->nameResolver->resolve(
                        $spelling,
                        SymbolKind::classLike(),
                        $namespace,
                        $imports,
                    ),
                    $node,
                    $phpDocNode,
                );

                if (!\array_key_exists($name, $spellingsInTypes)) {
                    $spellingsInTypes[$name] = 0;
                }

                ++$spellingsInTypes[$name];
            }

            if (\preg_match_all(self::PATTERN_QUALIFIED_NAME_IN_TEXT, $docComment->getText(), $matches) === 0) {
                continue;
            }

            foreach ($matches[1] as $name) {
                if (
                    \array_key_exists($name, $spellingsInTypes)
                    && 0 < $spellingsInTypes[$name]
                ) {
                    --$spellingsInTypes[$name];

                    continue;
                }

                $spelling = Spelling::fromString($name);

                $symbolReferences[] = SymbolReference::inDocBlockText(
                    $spelling,
                    $this->nameResolver->resolve(
                        $spelling,
                        SymbolKind::classLike(),
                        $namespace,
                        $imports,
                    ),
                    $node,
                );
            }
        }

        return $symbolReferences;
    }

    private static function isKeywordInDocBlockType(string $name): bool
    {
        if (\strpos($name, '-') !== false) {
            return true;
        }

        if (\strpos($name, '$') === 0) {
            return true;
        }

        return \in_array(
            \strtolower($name),
            self::KEYWORDS_IN_DOC_BLOCK_TYPES,
            true,
        );
    }

    /**
     * @param list<Node\Stmt> $statements
     *
     * @return array<int, array<string, true>>
     */
    private function localTypeNamesOfNodes(array $statements): array
    {
        $nodeFinder = new NodeFinder();

        $scopes = $nodeFinder->find($statements, static function (Node $node): bool {
            return $node instanceof Node\Stmt\ClassLike
                || $node instanceof Node\FunctionLike;
        });

        $localTypeNamesOfNodes = [];

        foreach ($scopes as $scope) {
            $localTypeNames = $this->localTypeNames($scope);

            if ([] === $localTypeNames) {
                continue;
            }

            foreach ($nodeFinder->find([$scope], static function (Node $node): bool {
                return true;
            }) as $node) {
                $identifier = \spl_object_id($node);

                if (!\array_key_exists($identifier, $localTypeNamesOfNodes)) {
                    $localTypeNamesOfNodes[$identifier] = [];
                }

                $localTypeNamesOfNodes[$identifier] += $localTypeNames;
            }
        }

        return $localTypeNamesOfNodes;
    }

    /**
     * @return array<string, true>
     */
    private function localTypeNames(Node $node): array
    {
        if (null === $node->getDocComment()) {
            return [];
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

        if (!$phpDocInfo instanceof BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return [];
        }

        $localTypeNames = [];

        foreach ($phpDocInfo->getPhpDocNode()->getTags() as $tag) {
            $value = $tag->value;

            if ($value instanceof Ast\PhpDoc\TemplateTagValueNode) {
                $localTypeNames[$value->name] = true;

                continue;
            }

            if ($value instanceof Ast\PhpDoc\TypeAliasTagValueNode) {
                $localTypeNames[$value->alias] = true;

                continue;
            }

            if ($value instanceof Ast\PhpDoc\TypeAliasImportTagValueNode) {
                if (null === $value->importedAs) {
                    $localTypeNames[$value->importedAlias] = true;

                    continue;
                }

                $localTypeNames[$value->importedAs] = true;

                continue;
            }

            if ($value instanceof Ast\PhpDoc\MethodTagValueNode) {
                foreach ($value->templateTypes as $templateType) {
                    $localTypeNames[$templateType->name] = true;
                }
            }
        }

        return $localTypeNames;
    }
}
