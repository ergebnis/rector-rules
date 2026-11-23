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
final class Respellings
{
    /**
     * @var array<int, Respelling>
     */
    private array $values;

    private function __construct(Respelling ...$values)
    {
        $this->values = [];

        foreach ($values as $value) {
            $this->values[\spl_object_id($value->symbolReference())] = $value;
        }
    }

    public static function create(Respelling ...$values): self
    {
        return new self(...$values);
    }

    public function isEmpty(): bool
    {
        return [] === $this->values;
    }

    public function spellingOf(SymbolReference $symbolReference): ?Spelling
    {
        $identifier = \spl_object_id($symbolReference);

        if (!\array_key_exists($identifier, $this->values)) {
            return null;
        }

        return $this->values[$identifier]->spelling();
    }

    /**
     * @return list<Respelling>
     */
    public function toArray(): array
    {
        return \array_values($this->values);
    }
}
