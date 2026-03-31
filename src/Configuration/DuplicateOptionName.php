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
final class DuplicateOptionName extends \InvalidArgumentException
{
    public static function create(string ...$optionNames): self
    {
        return new self(\sprintf(
            'Configuration option names "%s" are used more than once.',
            \implode('", "', $optionNames),
        ));
    }
}
