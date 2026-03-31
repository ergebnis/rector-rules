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
 * @covers \Ergebnis\Rector\Rules\Configuration\Options
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\Configuration
 * @uses \Ergebnis\Rector\Rules\Configuration\DuplicateOptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\InvalidOptionValue
 * @uses \Ergebnis\Rector\Rules\Configuration\Option
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionDescription
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionValue
 * @uses \Ergebnis\Rector\Rules\Configuration\UnknownOptionName
 */
final class OptionsTest extends Framework\TestCase
{
    public function testCreateRejectsOptionsWithDuplicateOptionNames(): void
    {
        $this->expectException(Rules\Configuration\DuplicateOptionName::class);

        Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('foo'),
                Rules\Configuration\OptionDescription::fromString('First.'),
                Rules\Configuration\OptionValue::string('bar'),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('foo'),
                Rules\Configuration\OptionDescription::fromString('Second.'),
                Rules\Configuration\OptionValue::string('baz'),
            ),
        );
    }

    public function testCreateReturnsOptions(): void
    {
        $values = [
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('foo'),
                Rules\Configuration\OptionDescription::fromString('First.'),
                Rules\Configuration\OptionValue::string('bar'),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('baz'),
                Rules\Configuration\OptionDescription::fromString('Second.'),
                Rules\Configuration\OptionValue::booleanDefaultingTo(false),
            ),
        ];

        $options = Rules\Configuration\Options::create(...$values);

        self::assertSame($values, $options->toArray());
    }

    public function testResolveFromRejectsUnknownOptionNames(): void
    {
        $options = Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('foo'),
                Rules\Configuration\OptionDescription::fromString('First.'),
                Rules\Configuration\OptionValue::string('bar'),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('baz'),
                Rules\Configuration\OptionDescription::fromString('Second.'),
                Rules\Configuration\OptionValue::booleanDefaultingTo(false),
            ),
        );

        $this->expectException(Rules\Configuration\UnknownOptionName::class);

        $options->resolveConfigurationFrom([
            'unknown' => 'value',
        ]);
    }

    public function testResolveFromRejectsInvalidOptionValues(): void
    {
        $options = Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('foo'),
                Rules\Configuration\OptionDescription::fromString('First.'),
                Rules\Configuration\OptionValue::string('default'),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('bar'),
                Rules\Configuration\OptionDescription::fromString('Second.'),
                Rules\Configuration\OptionValue::booleanDefaultingTo(false),
            ),
        );

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $options->resolveConfigurationFrom([
            'bar' => 'not-a-bool',
        ]);
    }

    public function testResolveFromAppliesDefaults(): void
    {
        $options = Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('foo'),
                Rules\Configuration\OptionDescription::fromString('First.'),
                Rules\Configuration\OptionValue::string('default-value'),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('bar'),
                Rules\Configuration\OptionDescription::fromString('Second.'),
                Rules\Configuration\OptionValue::booleanDefaultingTo(true),
            ),
        );

        $resolved = $options->resolveConfigurationFrom([]);

        $expected = [
            'bar' => true,
            'foo' => 'default-value',
        ];

        self::assertEquals($expected, $resolved->toArray());
    }

    public function testResolveFromUsesProvidedValues(): void
    {
        $options = Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('foo'),
                Rules\Configuration\OptionDescription::fromString('First.'),
                Rules\Configuration\OptionValue::string('default'),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString('bar'),
                Rules\Configuration\OptionDescription::fromString('Second.'),
                Rules\Configuration\OptionValue::booleanDefaultingTo(false),
            ),
        );

        $resolved = $options->resolveConfigurationFrom([
            'bar' => true,
            'foo' => 'custom',
        ]);

        $expected = [
            'bar' => true,
            'foo' => 'custom',
        ];

        self::assertEquals($expected, $resolved->toArray());
    }
}
