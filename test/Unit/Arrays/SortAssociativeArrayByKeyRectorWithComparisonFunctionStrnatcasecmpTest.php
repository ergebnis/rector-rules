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

use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Arrays\SortAssociativeArrayByKeyRector
 *
 * @uses \Ergebnis\Rector\Rules\Arrays\ArrayItemWithKey
 * @uses \Ergebnis\Rector\Rules\Arrays\Key
 */
final class SortAssociativeArrayByKeyRectorWithComparisonFunctionStrnatcasecmpTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideFilePathsPhp74
     *
     * @requires PHP < 8.0
     *
     * @see https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.core.string-number-comparision
     */
    public function testPhp74(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideFilePathsPhp74(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Arrays/SortAssociativeArrayByKeyRector/WithComparisonFunctionStrnatcasecmp/Php74');
    }

    /**
     * @dataProvider provideFilePathsPhp80
     *
     * @requires PHP >= 8.0
     *
     * @see https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.core.string-number-comparision
     */
    public function testPhp80(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideFilePathsPhp80(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Arrays/SortAssociativeArrayByKeyRector/WithComparisonFunctionStrnatcasecmp/Php80');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Arrays/SortAssociativeArrayByKeyRector/WithComparisonFunctionStrnatcasecmp/config.php';
    }
}
