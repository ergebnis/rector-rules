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
 * @covers \Ergebnis\Rector\Rules\Configuration\OptionDescription
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\InvalidOptionDescription
 */
final class OptionDescriptionTest extends Framework\TestCase
{
    /**
     * @dataProvider \Ergebnis\DataProvider\StringProvider::blank
     * @dataProvider \Ergebnis\DataProvider\StringProvider::empty
     */
    public function testFromStringRejectsBlankOrEmptyValue(string $value): void
    {
        $this->expectException(Rules\Configuration\InvalidOptionDescription::class);

        Rules\Configuration\OptionDescription::fromString($value);
    }

    /**
     * @dataProvider \Ergebnis\DataProvider\StringProvider::untrimmed
     */
    public function testFromStringRejectsUntrimmedValue(string $value): void
    {
        $this->expectException(Rules\Configuration\InvalidOptionDescription::class);

        Rules\Configuration\OptionDescription::fromString($value);
    }

    /**
     * @dataProvider \Ergebnis\DataProvider\StringProvider::trimmed
     */
    public function testFromStringReturnsDescription(string $value): void
    {
        $description = Rules\Configuration\OptionDescription::fromString($value);

        self::assertSame($value, $description->toString());
    }
}
