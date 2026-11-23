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
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Files\NamespacePrefixes
 *
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 */
final class NamespacePrefixesTest extends Framework\TestCase
{
    public function testCreateReturnsNamespacePrefixesWithoutDuplicates(): void
    {
        $namespacePrefixOne = Rules\Files\NamespacePrefix::fromString('Example\Core');
        $namespacePrefixTwo = Rules\Files\NamespacePrefix::fromString('Example\Http');

        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(
            $namespacePrefixOne,
            $namespacePrefixTwo,
            Rules\Files\NamespacePrefix::fromString('Example\Core'),
        );

        $expected = [
            $namespacePrefixOne,
            $namespacePrefixTwo,
        ];

        self::assertSame($expected, $namespacePrefixes->toArray());
    }

    public function testContainsReturnsFalseWhenNamespacePrefixIsNotContained(): void
    {
        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(Rules\Files\NamespacePrefix::fromString('Example\Core'));

        self::assertFalse($namespacePrefixes->contains(Rules\Files\NamespacePrefix::fromString('Example\Http')));
    }

    public function testContainsReturnsTrueWhenNamespacePrefixIsContained(): void
    {
        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(Rules\Files\NamespacePrefix::fromString('Example\Core'));

        self::assertTrue($namespacePrefixes->contains(Rules\Files\NamespacePrefix::fromString('Example\Core')));
    }

    public function testIsEmptyReturnsFalseWhenNamespacePrefixesAreNotEmpty(): void
    {
        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(Rules\Files\NamespacePrefix::fromString('Example\Core'));

        self::assertFalse($namespacePrefixes->isEmpty());
    }

    public function testIsEmptyReturnsTrueWhenNamespacePrefixesAreEmpty(): void
    {
        $namespacePrefixes = Rules\Files\NamespacePrefixes::create();

        self::assertTrue($namespacePrefixes->isEmpty());
    }

    public function testMergeReturnsNamespacePrefixesWithoutDuplicates(): void
    {
        $namespacePrefixOne = Rules\Files\NamespacePrefix::fromString('Example\Core');
        $namespacePrefixTwo = Rules\Files\NamespacePrefix::fromString('Example\Http');
        $namespacePrefixThree = Rules\Files\NamespacePrefix::fromString('Example\Mail');

        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(
            $namespacePrefixOne,
            $namespacePrefixTwo,
        );

        $merged = $namespacePrefixes->merge(Rules\Files\NamespacePrefixes::create(
            Rules\Files\NamespacePrefix::fromString('Example\Http'),
            $namespacePrefixThree,
        ));

        $expected = [
            $namespacePrefixOne,
            $namespacePrefixTwo,
            $namespacePrefixThree,
        ];

        self::assertNotSame($namespacePrefixes, $merged);
        self::assertSame($expected, $merged->toArray());
    }

    public function testMoreSpecificThanReturnsNamespacePrefixesThatNamespacePrefixIsNamespacePrefixOf(): void
    {
        $namespacePrefixOne = Rules\Files\NamespacePrefix::fromString('Example\Core\Http');
        $namespacePrefixTwo = Rules\Files\NamespacePrefix::fromString('Example\Core\Mail\Transport');

        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(
            Rules\Files\NamespacePrefix::fromString('Example\Core'),
            $namespacePrefixOne,
            Rules\Files\NamespacePrefix::fromString('Example\CoreExtension'),
            $namespacePrefixTwo,
            Rules\Files\NamespacePrefix::fromString('Other\Core\Http'),
        );

        $moreSpecific = $namespacePrefixes->moreSpecificThan(Rules\Files\NamespacePrefix::fromString('Example\Core'));

        $expected = [
            $namespacePrefixOne,
            $namespacePrefixTwo,
        ];

        self::assertSame($expected, $moreSpecific->toArray());
    }
}
