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
 * @covers \Ergebnis\Rector\Rules\Configuration\InvalidConfigurationKey
 */
final class InvalidConfigurationKeyTest extends Framework\TestCase
{
    public function testWithReturnsException(): void
    {
        $types = [
            'foo',
            'bool',
            'int',
        ];

        $exception = Rules\Configuration\InvalidConfigurationKey::with(...$types);

        $expected = \sprintf(
            'Configuration keys need to be strings, got "%s".',
            \implode('", "', $types),
        );

        self::assertSame($expected, $exception->getMessage());
    }
}
