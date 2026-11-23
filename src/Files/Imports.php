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

/**
 * @internal
 */
final class Imports
{
    /**
     * @var list<Import>
     */
    private array $values;

    private function __construct(Import ...$values)
    {
        $this->values = \array_values($values);
    }

    public static function create(Import ...$values): self
    {
        return new self(...$values);
    }

    public function ofSymbolKind(SymbolKind $symbolKind): self
    {
        return new self(...\array_filter($this->values, static function (Import $value) use ($symbolKind): bool {
            return $value->symbolKind()->equals($symbolKind);
        }));
    }

    public function forLocalName(LocalName $localName): ?Import
    {
        foreach ($this->values as $value) {
            if ($value->hasLocalName($localName)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return list<Import>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
