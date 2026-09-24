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

use Ergebnis\Rector\Rules;
use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\Configuration
 * @uses \Ergebnis\Rector\Rules\Configuration\InvalidOptionValue
 * @uses \Ergebnis\Rector\Rules\Configuration\Option
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionDescription
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\Options
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionValue
 * @uses \Ergebnis\Rector\Rules\Configuration\UnknownOptionName
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 */
final class ReferenceNamespacedSymbolsRelativeToNamespacePrefixRectorConfigureTest extends Testing\PHPUnit\AbstractLazyTestCase
{
    public function testConfigureRejectsUnknownKeys(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\UnknownOptionName::class);

        $rector->configure([
            'foo' => 'bar',
        ]);
    }

    public function testConfigureRejectsNonBoolForceRelativeReferences(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'forceRelativeReferences' => 'not-a-bool',
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsForceRelativeReferences(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'forceRelativeReferences' => true,
            'namespacePrefixes' => [
                'Example\Core',
            ],
        ]);
    }

    public function testConfigureRejectsNamespacePrefixesWhenValueIsNotList(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'namespacePrefixes' => 'not-an-array',
        ]);
    }

    /**
     * @dataProvider provideNamespacePrefixesWhereValueIsNotAListOfStrings
     */
    public function testConfigureRejectsNamespacePrefixesWhenValueIsNotAListOfStrings(array $value): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'namespacePrefixes' => $value,
        ]);
    }

    /**
     * @return \Generator<string, array{0: mixed}>
     */
    public static function provideNamespacePrefixesWhereValueIsNotAListOfStrings(): iterable
    {
        $values = [
            'associative-array' => [
                'foo' => 'Example\Core',
            ],
            'list-with-non-string-item' => [
                123,
            ],
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider provideInvalidNamespacePrefix
     */
    public function testConfigureRejectsNamespacePrefixesWhenValueIsNotAListOfValidNamespacePrefixes(string $namespacePrefix): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'namespacePrefixes' => [
                $namespacePrefix,
            ],
        ]);
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideInvalidNamespacePrefix(): iterable
    {
        $values = [
            'string-empty' => '',
            'string-ends-with-backslash' => 'Example\Core\\',
            'string-segment-starts-with-digit' => 'Example\1Core',
            'string-single-segment' => 'SingleSegment',
            'string-starts-with-backslash' => '\Example\Core',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    public function testConfigureRejectsNamespacePrefixesWhenValueIsAListWithDuplicateNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
                'Example\Core',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsNamespacePrefixesWhenItIsAListOfValidNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
                'Example\Domain',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsNamespacePrefixesWhenItIsAnEmptyList(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([]);
    }

    public function testConfigureRejectsParentNamespacePrefixesWhenValueIsNotList(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'parentNamespacePrefixes' => 'not-an-array',
        ]);
    }

    /**
     * @dataProvider provideParentNamespacePrefixesWhereValueIsNotAListOfStrings
     */
    public function testConfigureRejectsParentNamespacePrefixesWhereValueIsNotAListOfStrings(array $value): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'parentNamespacePrefixes' => $value,
        ]);
    }

    /**
     * @return \Generator<string, array{0: mixed}>
     */
    public static function provideParentNamespacePrefixesWhereValueIsNotAListOfStrings(): iterable
    {
        $values = [
            'associative-array' => [
                'foo' => 'Example',
            ],
            'list-with-non-string-item' => [
                123,
            ],
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider provideInvalidParentNamespacePrefix
     */
    public function testConfigureRejectsParentNamespacePrefixesWhenValueIsNotAListOfValidParentNamespacePrefixes(string $parentNamespacePrefix): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                $parentNamespacePrefix,
            ],
        ]);
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideInvalidParentNamespacePrefix(): iterable
    {
        $values = [
            'string-empty' => '',
            'string-ends-with-backslash' => 'Example\Core\\',
            'string-segment-starts-with-digit' => 'Example\1Core',
            'string-starts-with-backslash' => '\Example\Core',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    public function testConfigureRejectsParentNamespacePrefixesWhenValueIsAListWithDuplicateParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
                'Example',
            ],
        ]);
    }

    public function testConfigureRejectsParentNamespacePrefixesWhenValueIsAListWithOverlappingParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
                'Example\Core',
            ],
        ]);
    }

    public function testConfigureRejectsParentNamespacePrefixesWhenValueIsAListWithOverlappingParentNamespacePrefixesInReverseOrder(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example\Core',
                'Example',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsParentNamespacePrefixesWhenItIsAListOfValidParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
                'Symfony\Component',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsParentNamespacePrefixesWhenItIsAListWithASingleSegmentParentNamespacePrefix(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsCombinedNamespacePrefixesAndParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
            ],
            'parentNamespacePrefixes' => [
                'Symfony\Component',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsOverlapBetweenNamespacePrefixesAndParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
            ],
            'parentNamespacePrefixes' => [
                'Example',
            ],
        ]);
    }

    public function testConfigureRejectsNonBoolDiscoverNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(Rules\Configuration\InvalidOptionValue::class);

        $rector->configure([
            'discoverNamespacePrefixes' => 'not-a-bool',
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsDiscoverNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'discoverNamespacePrefixes' => true,
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsCombinedDiscoverNamespacePrefixesAndNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'discoverNamespacePrefixes' => true,
            'namespacePrefixes' => [
                'Example\Core',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsCombinedDiscoverNamespacePrefixesAndParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'discoverNamespacePrefixes' => true,
            'parentNamespacePrefixes' => [
                'Symfony\Component',
            ],
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConfigureAcceptsCombinedDiscoverNamespacePrefixesAndNamespacePrefixesAndParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'discoverNamespacePrefixes' => true,
            'namespacePrefixes' => [
                'Example\Core',
            ],
            'parentNamespacePrefixes' => [
                'Symfony\Component',
            ],
        ]);
    }
}
