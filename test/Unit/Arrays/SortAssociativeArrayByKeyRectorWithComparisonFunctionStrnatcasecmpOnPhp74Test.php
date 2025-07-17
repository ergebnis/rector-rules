<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/rector-rules
 */

namespace Arrays;

use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Arrays\SortAssociativeArrayByKeyRector
 *
 * @requires PHP <= 7.4
 *
 * @see https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.core.string-number-comparision
 */
final class SortAssociativeArrayByKeyRectorWithComparisonFunctionStrnatcasecmpOnPhp74Test extends Testing\PHPUnit\AbstractRectorTestCase
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
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Arrays/SortAssociativeArrayByKeyRector/WithComparisonFunctionStrnatcasecmpOnPhp74');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Arrays/SortAssociativeArrayByKeyRector/WithComparisonFunctionStrnatcasecmpOnPhp74/config.php';
    }
}
