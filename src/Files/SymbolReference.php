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
    private ?PhpDocParser\Ast\Type\IdentifierTypeNode $typeNode;

    private function __construct(
        SymbolReferenceLocation $location,
        SymbolKind $symbolKind,
        Spelling $spelling,
        ?FullyQualifiedName $fullyQualifiedName,
        Node $node,
        ?PhpDocParser\Ast\Type\IdentifierTypeNode $typeNode
    ) {
        $this->location = $location;
        $this->symbolKind = $symbolKind;
        $this->spelling = $spelling;
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->node = $node;
        $this->typeNode = $typeNode;
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

    public static function inDocBlockType(
        Spelling $spelling,
        ?FullyQualifiedName $fullyQualifiedName,
        Node $node,
        PhpDocParser\Ast\Type\IdentifierTypeNode $typeNode
    ): self {
        return new self(
            SymbolReferenceLocation::docBlockType(),
            SymbolKind::classLike(),
            $spelling,
            $fullyQualifiedName,
            $node,
            $typeNode,
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

    public function typeNode(): ?PhpDocParser\Ast\Type\IdentifierTypeNode
    {
        return $this->typeNode;
    }
}
