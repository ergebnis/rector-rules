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
 * @covers \Ergebnis\Rector\Rules\Configuration\DuplicateOptionName
 */
final class DuplicateOptionNamesTest extends Framework\TestCase
{
    public function testFromNamesReturnsException(): void
    {
        $names = [
            'bar',
            'foo',
        ];

        $exception = Rules\Configuration\DuplicateOptionName::create(...$names);

        self::assertSame('Configuration option names "bar", "foo" are used more than once.', $exception->getMessage());
    }
}
