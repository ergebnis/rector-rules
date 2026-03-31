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
 * @covers \Ergebnis\Rector\Rules\Configuration\OptionValue
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\InvalidOptionValue
 */
final class OptionValueTest extends Framework\TestCase
{
    /**
     * @dataProvider \Ergebnis\DataProvider\BoolProvider::arbitrary
     */
    public function testBooleanDefaultingToReturnsValueWithBoolType(bool $default): void
    {
        $optionValue = Rules\Configuration\OptionValue::booleanDefaultingTo($default);

        self::assertSame('bool', $optionValue->type());
        self::assertSame($default, $optionValue->default());
        self::assertSame([], $optionValue->allowedValues());
    }

    /**
     * @dataProvider \Ergebnis\DataProvider\BoolProvider::arbitrary
     */
    public function testBooleanDefaultingToResolvesTo(bool $value): void
    {
        $optionValue = Rules\Configuration\OptionValue::booleanDefaultingTo(false);

        $resolvedValue = $optionValue->resolve($value);

        self::assertSame($value, $resolvedValue);
    }

    public function testBooleanDefaultingToResolveRejectsNonBool(): void
    {
        $optionValue = Rules\Configuration\OptionValue::booleanDefaultingTo(false);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $optionValue->resolve('not-a-bool');
    }

    public function testStringReturnsValueWithStringType(): void
    {
        $value = Rules\Configuration\OptionValue::string('default');

        self::assertSame('string', $value->type());
        self::assertSame('default', $value->default());
        self::assertSame([], $value->allowedValues());
    }

    public function testStringResolveReturnsString(): void
    {
        $value = Rules\Configuration\OptionValue::string('default');

        self::assertSame('any-string', $value->resolve('any-string'));
    }

    public function testStringResolveRejectsNonString(): void
    {
        $value = Rules\Configuration\OptionValue::string('default');

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $value->resolve(123);
    }

    public function testOneOfReturnsValueWithAllowedValues(): void
    {
        $allowedValues = [
            'bar',
            'baz',
        ];

        $value = Rules\Configuration\OptionValue::oneOf(
            $allowedValues,
            'bar',
        );

        self::assertSame('string', $value->type());
        self::assertSame('bar', $value->default());
        self::assertSame($allowedValues, $value->allowedValues());
    }

    public function testOneOfResolveReturnsAllowedValue(): void
    {
        $value = Rules\Configuration\OptionValue::oneOf(
            [
                'bar',
                'baz',
            ],
            'bar',
        );

        self::assertSame('baz', $value->resolve('baz'));
    }

    public function testOneOfResolveRejectsNonString(): void
    {
        $value = Rules\Configuration\OptionValue::oneOf(
            [
                'bar',
                'baz',
            ],
            'bar',
        );

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $value->resolve(123);
    }

    public function testOneOfResolveRejectsDisallowedValue(): void
    {
        $value = Rules\Configuration\OptionValue::oneOf(
            [
                'bar',
                'baz',
            ],
            'bar',
        );

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $value->resolve('qux');
    }

    public function testListOfStringsReturnsValueWithArrayType(): void
    {
        $default = [];

        $value = Rules\Configuration\OptionValue::listOfStringsDefaultingTo($default);

        self::assertSame('list<string>', $value->type());
        self::assertSame($default, $value->default());
        self::assertSame($default, $value->allowedValues());
    }

    public function testListOfStringsReturnsValueWithCustomDefault(): void
    {
        $default = [
            'foo',
            'bar',
        ];

        $value = Rules\Configuration\OptionValue::listOfStringsDefaultingTo($default);

        self::assertSame($default, $value->default());
    }

    public function testListOfStringsResolveReturnsList(): void
    {
        $value = Rules\Configuration\OptionValue::listOfStringsDefaultingTo();

        self::assertSame(['bar', 'baz'], $value->resolve(['bar', 'baz']));
    }

    public function testListOfStringsResolveReturnsEmptyList(): void
    {
        $value = Rules\Configuration\OptionValue::listOfStringsDefaultingTo();

        self::assertSame([], $value->resolve([]));
    }

    public function testListOfStringsResolveRejectsNonArray(): void
    {
        $value = Rules\Configuration\OptionValue::listOfStringsDefaultingTo();

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $value->resolve('not-an-array');
    }

    public function testListOfStringsResolveRejectsAssociativeArray(): void
    {
        $value = Rules\Configuration\OptionValue::listOfStringsDefaultingTo();

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $value->resolve(['key' => 'value']);
    }

    public function testListOfStringsResolveRejectsListWithNonStringItem(): void
    {
        $value = Rules\Configuration\OptionValue::listOfStringsDefaultingTo();

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $value->resolve([123]);
    }
}
