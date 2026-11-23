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

/**
 * @internal
 *
 * @see https://www.php.net/manual/en/language.namespaces.importing.php
 */
final class Import
{
    private SymbolKind $symbolKind;
    private FullyQualifiedName $fullyQualifiedName;
    private LocalName $localName;

    /**
     * @var null|Node\Stmt\GroupUse|Node\Stmt\Use_
     */
    private ?Node\Stmt $statement;
    private ?Node\UseItem $useItem;

    /**
     * @param null|Node\Stmt\GroupUse|Node\Stmt\Use_ $statement
     */
    private function __construct(
        SymbolKind $symbolKind,
        FullyQualifiedName $fullyQualifiedName,
        LocalName $localName,
        ?Node\Stmt $statement,
        ?Node\UseItem $useItem
    ) {
        $this->symbolKind = $symbolKind;
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->localName = $localName;
        $this->statement = $statement;
        $this->useItem = $useItem;
    }

    public static function create(
        SymbolKind $symbolKind,
        FullyQualifiedName $fullyQualifiedName,
        LocalName $localName
    ): self {
        return new self(
            $symbolKind,
            $fullyQualifiedName,
            $localName,
            null,
            null,
        );
    }

    /**
     * @param Node\Stmt\GroupUse|Node\Stmt\Use_ $statement
     */
    public static function fromUseItem(
        SymbolKind $symbolKind,
        Node\Stmt $statement,
        Node\UseItem $useItem
    ): self {
        $name = $useItem->name->toString();

        if ($statement instanceof Node\Stmt\GroupUse) {
            $name = \sprintf(
                '%s\\%s',
                $statement->prefix->toString(),
                $name,
            );
        }

        return new self(
            $symbolKind,
            FullyQualifiedName::fromString($name),
            LocalName::fromString($useItem->getAlias()->toString()),
            $statement,
            $useItem,
        );
    }

    public function symbolKind(): SymbolKind
    {
        return $this->symbolKind;
    }

    public function fullyQualifiedName(): FullyQualifiedName
    {
        return $this->fullyQualifiedName;
    }

    public function localName(): LocalName
    {
        return $this->localName;
    }

    /**
     * @return null|Node\Stmt\GroupUse|Node\Stmt\Use_
     */
    public function statement(): ?Node\Stmt
    {
        return $this->statement;
    }

    public function useItem(): ?Node\UseItem
    {
        return $this->useItem;
    }

    public function hasLocalName(LocalName $localName): bool
    {
        if ($this->symbolKind->isConstant()) {
            return $this->localName->toString() === $localName->toString();
        }

        return \strtolower($this->localName->toString()) === \strtolower($localName->toString());
    }
}
