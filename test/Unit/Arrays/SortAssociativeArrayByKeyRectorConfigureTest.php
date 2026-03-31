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

namespace Ergebnis\Rector\Rules\Test\Unit\Arrays;

use Ergebnis\Rector\Rules;
use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Arrays\SortAssociativeArrayByKeyRector
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\Configuration
 * @uses \Ergebnis\Rector\Rules\Configuration\InvalidOptionValue
 * @uses \Ergebnis\Rector\Rules\Configuration\Option
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionDescription
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\Options
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionValue
 * @uses \Ergebnis\Rector\Rules\Configuration\UnknownOptionName
 */
final class SortAssociativeArrayByKeyRectorConfigureTest extends Testing\PHPUnit\AbstractLazyTestCase
{
    public function testConfigureRejectsUnknownKeys(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(Rules\Configuration\UnknownOptionName::class);

        $rector->configure([
            'foo' => 'bar',
        ]);
    }

    public function testConfigureRejectsNonStringComparisonFunction(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'comparison_function' => 123,
        ]);
    }

    public function testConfigureRejectsInvalidComparisonFunction(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'comparison_function' => 'invalid',
        ]);
    }

    public function testConfigureRejectsNonStringDirection(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'direction' => 123,
        ]);
    }

    public function testConfigureRejectsInvalidDirection(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'direction' => 'invalid',
        ]);
    }

    public function testConfigureAcceptsValidConfiguration(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $rector->configure([
            'comparison_function' => 'strnatcasecmp',
            'direction' => 'desc',
        ]);

        $this->addToAssertionCount(1);
    }

    public function testConfigureAcceptsEmptyConfiguration(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $rector->configure([]);

        $this->addToAssertionCount(1);
    }
}
