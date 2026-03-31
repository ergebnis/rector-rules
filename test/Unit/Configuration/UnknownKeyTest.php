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
 * @covers \Ergebnis\Rector\Rules\Configuration\UnknownOptionName
 */
final class UnknownKeyTest extends Framework\TestCase
{
    public function testCreateReturnsException(): void
    {
        $unknownOptionNames = [
            'foo',
            'bar',
        ];

        $exception = Rules\Configuration\UnknownOptionName::create(...$unknownOptionNames);

        $expected = \sprintf(
            'Configuration contains unknown option names "%s".',
            \implode('", "', $unknownOptionNames),
        );

        self::assertSame($expected, $exception->getMessage());
    }
}
