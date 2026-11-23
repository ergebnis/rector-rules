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
 * @uses \Ergebnis\Rector\Rules\Configuration\Configuration
 * @uses \Ergebnis\Rector\Rules\Configuration\Option
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionDescription
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\Options
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionValue
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\File
 * @uses \Ergebnis\Rector\Rules\Files\FileNamespace
 * @uses \Ergebnis\Rector\Rules\Files\FileReader
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\Import
 * @uses \Ergebnis\Rector\Rules\Files\Imports
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\NameResolver
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefixDiscoverer
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefixes
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefixPlan
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefixPlanner
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 * @uses \Ergebnis\Rector\Rules\Files\Respelling
 * @uses \Ergebnis\Rector\Rules\Files\Respellings
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReference
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferenceLocation
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferences
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

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp70(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php70');
    }

    /**
     * @dataProvider provideDataOnPhp80
     *
     * @requires PHP >= 8.0.0
     */
    public function testOnPhp80(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp80(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php80');
    }

    /**
     * @dataProvider provideDataOnPhp81
     *
     * @requires PHP >= 8.1.0
     */
    public function testOnPhp81(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp81(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php81');
    }

    /**
     * @dataProvider provideDataOnPhp82
     *
     * @requires PHP >= 8.2.0
     */
    public function testOnPhp82(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp82(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php82');
    }

    /**
     * @dataProvider provideDataOnPhp83
     *
     * @requires PHP >= 8.3.0
     */
    public function testOnPhp83(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp83(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php83');
    }

    /**
     * @dataProvider provideDataOnPhp84
     *
     * @requires PHP >= 8.4.0
     */
    public function testOnPhp84(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp84(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php84');
    }

    /**
     * @dataProvider provideDataOnPhp85
     *
     * @requires PHP >= 8.5.0
     */
    public function testOnPhp85(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp85(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/Php85');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithNamespacePrefixes/config.php';
    }
}
