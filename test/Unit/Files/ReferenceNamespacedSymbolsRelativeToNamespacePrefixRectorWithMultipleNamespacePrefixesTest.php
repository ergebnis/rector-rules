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

namespace Ergebnis\Rector\Rules\Test\Unit\Files;

use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector
 */
final class ReferenceNamespacedSymbolsRelativeToNamespacePrefixRectorWithMultipleNamespacePrefixesTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataOnPhp7
     *
     * @requires PHP < 8.0
     */
    public function testOnPhp7(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideDataOnPhp7(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithMultipleNamespacePrefixes/Php7');
    }

    /**
     * @dataProvider provideDataOnPhp8
     *
     * @requires PHP >= 8.0
     */
    public function testOnPhp8(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideDataOnPhp8(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithMultipleNamespacePrefixes/Php8');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithMultipleNamespacePrefixes/config.php';
    }
}
