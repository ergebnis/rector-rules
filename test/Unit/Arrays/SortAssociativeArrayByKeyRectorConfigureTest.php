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
 */
final class SortAssociativeArrayByKeyRectorConfigureTest extends Testing\PHPUnit\AbstractLazyTestCase
{
    public function testConfigureRejectsUnknownKeys(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration contains unknown keys: "foo".');

        $rector->configure([
            'foo' => 'bar',
        ]);
    }

    public function testConfigureRejectsNonStringComparisonFunction(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "comparison_function" needs to be one of "strcasecmp", "strcmp", "strnatcasecmp", "strnatcmp".');

        $rector->configure([
            'comparison_function' => 123,
        ]);
    }

    public function testConfigureRejectsInvalidComparisonFunction(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "comparison_function" needs to be one of "strcasecmp", "strcmp", "strnatcasecmp", "strnatcmp", got "invalid" instead.');

        $rector->configure([
            'comparison_function' => 'invalid',
        ]);
    }

    public function testConfigureRejectsNonStringDirection(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "direction" needs to be one of "asc", "desc".');

        $rector->configure([
            'direction' => 123,
        ]);
    }

    public function testConfigureRejectsInvalidDirection(): void
    {
        $rector = $this->make(Rules\Arrays\SortAssociativeArrayByKeyRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "direction" needs to be one of "asc", "desc", got "invalid" instead.');

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
