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
 *
 * @see https://www.php.net/manual/en/language.namespaces.definition.php
 */
final class NamespaceSegments
{
    /**
     * @var list<NamespaceSegment>
     */
    private array $values;
    private string $value;

    private function __construct(NamespaceSegment ...$values)
    {
        $this->values = $values;
        $this->value = \implode('\\', \array_map(static function (NamespaceSegment $segment): string {
            return $segment->toString();
        }, $values));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        if (1 !== \preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\\\\[a-zA-Z_][a-zA-Z0-9_]*)*$/', $value)) {
            throw new \InvalidArgumentException(\sprintf(
                'Value needs to be a valid namespace with at least one segment, got "%s".',
                $value,
            ));
        }

        $values = \explode(
            '\\',
            $value,
        );

        return new self(...\array_map(static function (string $value): NamespaceSegment {
            return NamespaceSegment::fromString($value);
        }, $values));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function count(): int
    {
        return \count($this->values);
    }

    public function last(): NamespaceSegment
    {
        return $this->values[\count($this->values) - 1];
    }

    public function append(NamespaceSegment ...$namespaceSegments): self
    {
        return new self(
            ...$this->values,
            ...$namespaceSegments,
        );
    }

    public function toString(): string
    {
        return $this->value;
    }
}
