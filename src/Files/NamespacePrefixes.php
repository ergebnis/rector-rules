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
final class NamespacePrefixes
{
    /**
     * @var array<string, NamespacePrefix>
     */
    private array $values;

    private function __construct(NamespacePrefix ...$values)
    {
        $this->values = [];

        foreach ($values as $value) {
            if (\array_key_exists($value->toString(), $this->values)) {
                continue;
            }

            $this->values[$value->toString()] = $value;
        }
    }

    public static function create(NamespacePrefix ...$values): self
    {
        return new self(...$values);
    }

    public function contains(NamespacePrefix $other): bool
    {
        return \array_key_exists(
            $other->toString(),
            $this->values,
        );
    }

    public function isEmpty(): bool
    {
        return [] === $this->values;
    }

    public function merge(self $other): self
    {
        return new self(
            ...\array_values($this->values),
            ...\array_values($other->values),
        );
    }

    public function moreSpecificThan(NamespacePrefix $namespacePrefix): self
    {
        return new self(...\array_values(\array_filter($this->values, static function (NamespacePrefix $value) use ($namespacePrefix): bool {
            return $namespacePrefix->isNamespacePrefixOf($value);
        })));
    }

    /**
     * @return list<NamespacePrefix>
     */
    public function toArray(): array
    {
        return \array_values($this->values);
    }
}
