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
 *
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 * @uses \Ergebnis\Rector\Rules\Files\Reference
 */
final class ReferenceNamespacedSymbolsRelativeToNamespacePrefixRectorWithNamespacePrefixesTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataOnPhp70
     */
    public function testOnPhp70(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideDataOnPhp70(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php70');
    }

    /**
     * @dataProvider provideDataOnPhp80
     *
     * @requires PHP >= 8.0
     */
    public function testOnPhp80(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideDataOnPhp80(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php80');
    }

    /**
     * @dataProvider provideDataOnPhp81
     *
     * @requires PHP >= 8.1
     */
    public function testOnPhp81(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideDataOnPhp81(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php81');
    }

    /**
     * @dataProvider provideDataOnPhp82
     *
     * @requires PHP >= 8.2
     */
    public function testOnPhp82(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideDataOnPhp82(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php82');
    }

    /**
     * @dataProvider provideDataOnPhp83
     *
     * @requires PHP >= 8.3
     */
    public function testOnPhp83(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideDataOnPhp83(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php83');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/config.php';
    }
}
