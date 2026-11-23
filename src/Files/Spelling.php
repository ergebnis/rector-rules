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
final class Spelling
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isFullyQualified(): bool
    {
        return \strpos($this->value, '\\') === 0;
    }

    public function isUnqualified(): bool
    {
        return \strpos($this->value, '\\') === false;
    }

    public function leadingSegment(): string
    {
        $segments = \explode(
            '\\',
            $this->withoutLeadingBackslash(),
        );

        return $segments[0];
    }

    public function withoutLeadingBackslash(): string
    {
        return \ltrim(
            $this->value,
            '\\',
        );
    }
}
