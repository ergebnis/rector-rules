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
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 */
final class ReferenceNamespacedSymbolsRelativeToNamespacePrefixRectorConfigureTest extends Testing\PHPUnit\AbstractLazyTestCase
{
    public function testConfigureRejectsUnknownKeys(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration contains unknown keys: "foo".');

        $rector->configure([
            'foo' => 'bar',
        ]);
    }

    public function testConfigureRejectsNonArrayNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "namespacePrefixes" needs to be an array of strings.');

        $rector->configure([
            'namespacePrefixes' => 'not-an-array',
        ]);
    }

    public function testConfigureRejectsNonStringNamespacePrefix(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "namespacePrefixes" needs to be an array of strings.');

        $rector->configure([
            'namespacePrefixes' => [
                123,
            ],
        ]);
    }

    /**
     * @dataProvider provideInvalidNamespacePrefix
     */
    public function testConfigureRejectsInvalidNamespacePrefix(string $namespacePrefix): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Value for configuration option "namespacePrefixes" needs to be an array of strings where each string is a valid namespace with at least two segments, got "%s".',
            $namespacePrefix,
        ));

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
            yield $key => [$value];
        }
    }

    public function testConfigureRejectsDuplicateNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "namespacePrefixes" needs to be an array of unique strings, got duplicate "Example\Core".');

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
                'Example\Core',
            ],
        ]);
    }

    public function testConfigureAcceptsValidConfiguration(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
                'Example\Domain',
            ],
        ]);

        $this->addToAssertionCount(1);
    }

    public function testConfigureAcceptsEmptyConfiguration(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([]);

        $this->addToAssertionCount(1);
    }

    public function testConfigureRejectsNonArrayParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "parentNamespacePrefixes" needs to be an array of strings.');

        $rector->configure([
            'parentNamespacePrefixes' => 'not-an-array',
        ]);
    }

    public function testConfigureRejectsNonStringParentNamespacePrefix(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "parentNamespacePrefixes" needs to be an array of strings.');

        $rector->configure([
            'parentNamespacePrefixes' => [
                123,
            ],
        ]);
    }

    /**
     * @dataProvider provideInvalidParentNamespacePrefix
     */
    public function testConfigureRejectsInvalidParentNamespacePrefix(string $parentNamespacePrefix): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Value for configuration option "parentNamespacePrefixes" needs to be an array of strings where each string is a valid namespace with at least one segment, got "%s".',
            $parentNamespacePrefix,
        ));

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
            yield $key => [$value];
        }
    }

    public function testConfigureRejectsDuplicateParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "parentNamespacePrefixes" needs to be an array of unique strings, got duplicate "Example".');

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
                'Example',
            ],
        ]);
    }

    public function testConfigureRejectsOverlappingParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "parentNamespacePrefixes" needs to be an array of strings where no string is a namespace prefix of another, got "Example" and "Example\Core".');

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
                'Example\Core',
            ],
        ]);
    }

    public function testConfigureRejectsOverlappingParentNamespacePrefixesInReverseOrder(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "parentNamespacePrefixes" needs to be an array of strings where no string is a namespace prefix of another, got "Example" and "Example\Core".');

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example\Core',
                'Example',
            ],
        ]);
    }

    public function testConfigureAcceptsValidParentNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
                'Symfony\Component',
            ],
        ]);

        $this->addToAssertionCount(1);
    }

    public function testConfigureAcceptsSingleSegmentParentNamespacePrefix(): void
    {
        $rector = $this->make(Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'parentNamespacePrefixes' => [
                'Example',
            ],
        ]);

        $this->addToAssertionCount(1);
    }

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

        $this->addToAssertionCount(1);
    }

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

        $this->addToAssertionCount(1);
    }
}
