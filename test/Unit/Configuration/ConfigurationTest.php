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
 * @covers \Ergebnis\Rector\Rules\Configuration\Configuration
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\InvalidConfigurationKey
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\UnknownOptionName
 */
final class ConfigurationTest extends Framework\TestCase
{
    public function testFromArrayRejectsNonStringKeys(): void
    {
        $this->expectException(Rules\Configuration\InvalidConfigurationKey::class);

        Rules\Configuration\Configuration::fromArray([
            0 => 'foo',
            1 => 'bar',
        ]);
    }

    public function testFromArrayReturnsConfiguration(): void
    {
        $values = [
            'baz' => true,
            'foo' => 'bar',
        ];

        $configuration = Rules\Configuration\Configuration::fromArray($values);

        self::assertSame($values, $configuration->toArray());
    }

    public function testGetReturnsValueForExistingKey(): void
    {
        $optionName = Rules\Configuration\OptionName::fromString('foo');

        $configuration = Rules\Configuration\Configuration::fromArray([
            'foo' => 'bar',
        ]);

        self::assertSame('bar', $configuration->get($optionName));
    }

    public function testGetThrowsRejectsNonExistingKey(): void
    {
        $optionName = Rules\Configuration\OptionName::fromString('unknown');

        $configuration = Rules\Configuration\Configuration::fromArray([
            'foo' => 'bar',
        ]);

        $this->expectException(Rules\Configuration\UnknownOptionName::class);

        $configuration->get($optionName);
    }
}
