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
 * @covers \Ergebnis\Rector\Rules\Configuration\InvalidOptionValue
 */
final class InvalidValueTest extends Framework\TestCase
{
    public function testTypeMismatchReturnsException(): void
    {
        $expectedType = 'a string';

        $exception = Rules\Configuration\InvalidOptionValue::typeMismatch($expectedType);

        self::assertSame('Value needs to be a string.', $exception->getMessage());
    }

    public function testTypeMismatchWithAllowedValuesReturnsException(): void
    {
        $allowedValues = [
            'bar',
            'baz',
        ];

        $exception = Rules\Configuration\InvalidOptionValue::typeMismatchWithAllowedValues($allowedValues);

        $expected = \sprintf(
            'Value needs to be one of "%s".',
            \implode('", "', $allowedValues),
        );

        self::assertSame($expected, $exception->getMessage());
    }

    public function testNotAllowedReturnsException(): void
    {
        $value = 'qux';
        $allowedValues = [
            'bar',
            'baz',
        ];

        $exception = Rules\Configuration\InvalidOptionValue::notAllowed(
            $value,
            $allowedValues,
        );

        $expected = \sprintf(
            'Value needs to be one of "%s", got "%s" instead.',
            \implode('", "', $allowedValues),
            $value,
        );

        self::assertSame($expected, $exception->getMessage());
    }

    public function testForOptionReturnsException(): void
    {
        $optionName = 'direction';

        $invalidOptionValue = Rules\Configuration\InvalidOptionValue::typeMismatch('a boolean');

        $exception = Rules\Configuration\InvalidOptionValue::forOption(
            $optionName,
            $invalidOptionValue,
        );

        $expected = \sprintf(
            'Configuration option "%s": %s',
            $optionName,
            $invalidOptionValue->getMessage(),
        );

        self::assertSame($expected, $exception->getMessage());
    }
}
