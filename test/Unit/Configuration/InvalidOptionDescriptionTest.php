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

namespace Ergebnis\Rector\Rules\Test\Unit\Configuration;

use Ergebnis\Rector\Rules;
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Configuration\InvalidOptionDescription
 */
final class InvalidOptionDescriptionTest extends Framework\TestCase
{
    public function testBlankOrEmptyReturnsException(): void
    {
        $exception = Rules\Configuration\InvalidOptionDescription::blankOrEmpty();

        self::assertSame('Option description must not be blank or empty.', $exception->getMessage());
    }

    public function testNotTrimmedReturnsException(): void
    {
        $exception = Rules\Configuration\InvalidOptionDescription::notTrimmed();

        self::assertSame('Option description must not have leading or trailing whitespace.', $exception->getMessage());
    }
}
