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

namespace Ergebnis\Rector\Rules\Test\Unit\Expressions\Arrays;

use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Expressions\Arrays\SortAssociativeArrayByKeyRector
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\Configuration
 * @uses \Ergebnis\Rector\Rules\Configuration\Option
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionDescription
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\Options
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionValue
 * @uses \Ergebnis\Rector\Rules\Expressions\Arrays\ArrayItemWithKey
 * @uses \Ergebnis\Rector\Rules\Expressions\Arrays\Key
 */
final class SortAssociativeArrayByKeyRectorWithComparisonFunctionStrnatcmpTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideData
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../../Fixture/Expressions/Arrays/SortAssociativeArrayByKeyRector/WithComparisonFunctionStrnatcmp');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../../Fixture/Expressions/Arrays/SortAssociativeArrayByKeyRector/WithComparisonFunctionStrnatcmp/config.php';
    }
}
