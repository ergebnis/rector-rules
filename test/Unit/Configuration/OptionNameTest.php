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

use Ergebnis\DataProvider;
use Ergebnis\Rector\Rules;
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Configuration\OptionName
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\InvalidOptionName
 */
final class OptionNameTest extends Framework\TestCase
{
    /**
     * @dataProvider \Ergebnis\DataProvider\StringProvider::blank
     * @dataProvider \Ergebnis\DataProvider\StringProvider::empty
     */
    public function testFromStringRejectsBlankOrEmptyValue(string $value): void
    {
        $this->expectException(Rules\Configuration\InvalidOptionName::class);

        Rules\Configuration\OptionName::fromString($value);
    }

    /**
     * @dataProvider \Ergebnis\DataProvider\StringProvider::trimmed
     */
    public function testFromStringReturnsName(string $value): void
    {
        $name = Rules\Configuration\OptionName::fromString($value);

        self::assertSame($value, $name->toString());
    }

    /**
     * @dataProvider \Ergebnis\DataProvider\StringProvider::untrimmed
     */
    public function testFromStringRejectsUntrimmedValue(string $value): void
    {
        $this->expectException(Rules\Configuration\InvalidOptionName::class);

        Rules\Configuration\OptionName::fromString($value);
    }
}
