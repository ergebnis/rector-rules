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

namespace Ergebnis\Rector\Rules\Configuration;

/**
 * @internal
 */
final class OptionDescription
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws InvalidOptionDescription
     */
    public static function fromString(string $value): self
    {
        if ('' === $value) {
            throw InvalidOptionDescription::blankOrEmpty();
        }

        if (\trim($value) !== $value) {
            throw InvalidOptionDescription::notTrimmed();
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
