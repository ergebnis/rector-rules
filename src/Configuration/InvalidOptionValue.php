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
final class InvalidOptionValue extends \InvalidArgumentException
{
    public static function typeMismatch(string $expectedType): self
    {
        return new self(\sprintf(
            'Value needs to be %s.',
            $expectedType,
        ));
    }

    /**
     * @param array<int, mixed> $allowedValues
     */
    public static function typeMismatchWithAllowedValues(array $allowedValues): self
    {
        return new self(\sprintf(
            'Value needs to be one of "%s".',
            \implode('", "', $allowedValues),
        ));
    }

    /**
     * @param array<int, mixed> $allowedValues
     */
    public static function notAllowed(
        string $value,
        array $allowedValues
    ): self {
        return new self(\sprintf(
            'Value needs to be one of "%s", got "%s" instead.',
            \implode('", "', $allowedValues),
            $value,
        ));
    }

    public static function forOption(
        string $optionName,
        self $exception
    ): self {
        return new self(\sprintf(
            'Configuration option "%s": %s',
            $optionName,
            $exception->getMessage(),
        ));
    }
}
