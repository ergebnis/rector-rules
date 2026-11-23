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
final class ReferenceNamespacedSymbolsRelativeToNamespacePrefixRectorWithDiscoverNamespacePrefixesAndForceRelativeReferencesAndParentNamespacePrefixesTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideData
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideData(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithDiscoverNamespacePrefixesAndForceRelativeReferencesAndParentNamespacePrefixes');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector/WithDiscoverNamespacePrefixesAndForceRelativeReferencesAndParentNamespacePrefixes/config.php';
    }
}
