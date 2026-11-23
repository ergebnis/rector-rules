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
final class FullyQualifiedNames
{
    /**
     * @var array<string, FullyQualifiedName>
     */
    private array $values;

    private function __construct(FullyQualifiedName ...$values)
    {
        $this->values = [];

        foreach ($values as $value) {
            if (\array_key_exists($value->toString(), $this->values)) {
                continue;
            }

            $this->values[$value->toString()] = $value;
        }
    }

    public static function create(FullyQualifiedName ...$values): self
    {
        return new self(...$values);
    }

    public function contains(FullyQualifiedName $other): bool
    {
        return \array_key_exists(
            $other->toString(),
            $this->values,
        );
    }

    /**
     * @return list<FullyQualifiedName>
     */
    public function toArray(): array
    {
        return \array_values($this->values);
    }
}
