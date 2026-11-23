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
final class SymbolReferences
{
    /**
     * @var list<SymbolReference>
     */
    private array $values;

    private function __construct(SymbolReference ...$values)
    {
        $this->values = \array_values($values);
    }

    public static function create(SymbolReference ...$values): self
    {
        return new self(...$values);
    }

    /**
     * @return list<SymbolReference>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
