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
final class InvalidOptionDescription extends \InvalidArgumentException
{
    public static function blankOrEmpty(): self
    {
        return new self('Option description must not be blank or empty.');
    }

    public static function notTrimmed(): self
    {
        return new self('Option description must not have leading or trailing whitespace.');
    }
}
