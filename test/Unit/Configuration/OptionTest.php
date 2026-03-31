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
 * @covers \Ergebnis\Rector\Rules\Configuration\Option
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionDescription
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionValue
 */
final class OptionTest extends Framework\TestCase
{
    public function testCreateReturnsOption(): void
    {
        $name = Rules\Configuration\OptionName::fromString('direction');
        $description = Rules\Configuration\OptionDescription::fromString('The sorting direction.');
        $value = Rules\Configuration\OptionValue::string('asc');

        $option = Rules\Configuration\Option::create(
            $name,
            $description,
            $value,
        );

        self::assertSame($name, $option->name());
        self::assertSame($description, $option->description());
        self::assertSame($value, $option->value());
    }
}
