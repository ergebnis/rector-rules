<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/rector-rules
 */

namespace Ergebnis\Rector\Rules\Test\Unit\Arrays;

use Ergebnis\DataProvider;
use Ergebnis\Rector\Rules;
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Arrays\Key
 */
final class KeyTest extends Framework\TestCase
{
    /**
     * @dataProvider \Ergebnis\DataProvider\StringProvider::arbitrary
     */
    public function testFromStringReturnsKey(string $value): void
    {
        $key = Rules\Arrays\Key::fromString($value);

        self::assertSame($value, $key->toString());
    }
}
