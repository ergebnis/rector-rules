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
use PhpParser\Node;
use PHPStan\PhpDocParser;

/**
 * @internal
 */
final class SymbolReference
{
    private SymbolReferenceLocation $location;
    private SymbolKind $symbolKind;
    private Spelling $spelling;
    private ?FullyQualifiedName $fullyQualifiedName;
    private Node $node;

    /**
     * @var null|PhpDocParser\Ast\ConstExpr\ConstFetchNode|PhpDocParser\Ast\Type\IdentifierTypeNode
     */
    private ?PhpDocParser\Ast\Node $phpDocNode;
    private ?Node $nodeWithAttributeComment;
    private ?Comment $attributeComment;

    /**
     * @param null|PhpDocParser\Ast\ConstExpr\ConstFetchNode|PhpDocParser\Ast\Type\IdentifierTypeNode $phpDocNode
     */
    private function __construct(
        SymbolReferenceLocation $location,
        SymbolKind $symbolKind,
        Spelling $spelling,
        ?FullyQualifiedName $fullyQualifiedName,
        Node $node,
        ?PhpDocParser\Ast\Node $phpDocNode,
        ?Node $nodeWithAttributeComment = null,
        ?Comment $attributeComment = null
    ) {
        $this->location = $location;
        $this->symbolKind = $symbolKind;
        $this->spelling = $spelling;
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->node = $node;
        $this->phpDocNode = $phpDocNode;
        $this->nodeWithAttributeComment = $nodeWithAttributeComment;
        $this->attributeComment = $attributeComment;
    }

    /**
     * PHP 7.4 parses an attribute as a comment. The name is then a node of the comment parsed as PHP 8.0 code, with
     * file positions relative to the text of the comment, and respelling it rewrites the text of the comment.
     */
    public static function inAttributeComment(
        SymbolKind $symbolKind,
        Spelling $spelling,
        ?FullyQualifiedName $fullyQualifiedName,
        Node\Name $name,
        Node $nodeWithAttributeComment,
        Comment $attributeComment
    ): self {
        return new self(
            SymbolReferenceLocation::code(),
            $symbolKind,
            $spelling,
            $fullyQualifiedName,
            $name,
            null,
            $nodeWithAttributeComment,
            $attributeComment,
        );
    }

    public static function inCode(
        SymbolKind $symbolKind,
        Spelling $spelling,
        ?FullyQualifiedName $fullyQualifiedName,
        Node\Name $name
    ): self {
        return new self(
            SymbolReferenceLocation::code(),
            $symbolKind,
            $spelling,
            $fullyQualifiedName,
            $name,
            null,
        );
    }

    /**
     * @param PhpDocParser\Ast\ConstExpr\ConstFetchNode|PhpDocParser\Ast\Type\IdentifierTypeNode $phpDocNode
     */
    public static function inDocBlockType(
        Spelling $spelling,
        ?FullyQualifiedName $fullyQualifiedName,
        Node $node,
        PhpDocParser\Ast\Node $phpDocNode
    ): self {
        return new self(
            SymbolReferenceLocation::docBlockType(),
            SymbolKind::classLike(),
            $spelling,
            $fullyQualifiedName,
            $node,
            $phpDocNode,
        );
    }

    public static function inDocBlockText(
        Spelling $spelling,
        ?FullyQualifiedName $fullyQualifiedName,
        Node $node
    ): self {
        return new self(
            SymbolReferenceLocation::docBlockText(),
            SymbolKind::classLike(),
            $spelling,
            $fullyQualifiedName,
            $node,
            null,
        );
    }

    public function location(): SymbolReferenceLocation
    {
        return $this->location;
    }

    public function symbolKind(): SymbolKind
    {
        return $this->symbolKind;
    }

    public function spelling(): Spelling
    {
        return $this->spelling;
    }

    public function fullyQualifiedName(): ?FullyQualifiedName
    {
        return $this->fullyQualifiedName;
    }

    public function node(): Node
    {
        return $this->node;
    }

    /**
     * @return null|PhpDocParser\Ast\ConstExpr\ConstFetchNode|PhpDocParser\Ast\Type\IdentifierTypeNode
     */
    public function phpDocNode(): ?PhpDocParser\Ast\Node
    {
        return $this->phpDocNode;
    }

    public function nodeWithAttributeComment(): ?Node
    {
        return $this->nodeWithAttributeComment;
    }

    public function attributeComment(): ?Comment
    {
        return $this->attributeComment;
    }
}
