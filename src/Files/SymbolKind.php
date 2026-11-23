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
final class SymbolKind
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function classLike(): self
    {
        return new self('class-like');
    }

    public static function constant(): self
    {
        return new self('constant');
    }

    public static function function(): self
    {
        return new self('function');
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isClassLike(): bool
    {
        return 'class-like' === $this->value;
    }

    public function isConstant(): bool
    {
        return 'constant' === $this->value;
    }

    public function isFunction(): bool
    {
        return 'function' === $this->value;
    }
}
