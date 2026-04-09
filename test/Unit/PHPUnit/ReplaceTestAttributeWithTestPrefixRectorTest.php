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

namespace Ergebnis\Rector\Rules\Test\Unit\PHPUnit;

use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\PHPUnit\ReplaceTestAttributeWithTestPrefixRector
 */
final class ReplaceTestAttributeWithTestPrefixRectorTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideData
     *
     * @requires PHP >= 8.0
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/PHPUnit/ReplaceTestAttributeWithTestPrefixRector/');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/PHPUnit/ReplaceTestAttributeWithTestPrefixRector/config.php';
    }
}
