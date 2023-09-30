<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/rector-rules
 */

namespace Arrays;

use Ergebnis\Rector\Rules;
use PHPUnit\Framework;
use Rector\Testing;

#[Framework\Attributes\CoversClass(Rules\Arrays\SortAssociativeArrayByKeyRector::class)]
final class SortAssociativeArrayByKeyRectorTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    #[Framework\Attributes\DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Arrays/SortAssociativeArrayByKeyRector');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Arrays/SortAssociativeArrayByKeyRector/config/configured_rule.php';
    }
}
