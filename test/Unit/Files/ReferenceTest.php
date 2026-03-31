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
 * @covers \Ergebnis\Rector\Rules\Files\Reference
 *
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 */
final class ReferenceTest extends Framework\TestCase
{
    public function testFromStringReturnsReference(): void
    {
        $value = 'Example\Core\Controller';

        $reference = Rules\Files\Reference::fromString($value);

        self::assertSame($value, $reference->toString());
    }

    public function testIsReturnsFalseWhenReferenceDoesNotEqualNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core\Controller');

        self::assertFalse($reference->is($namespacePrefix));
    }

    public function testIsReturnsTrueWhenReferenceEqualsNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core');

        self::assertTrue($reference->is($namespacePrefix));
    }

    public function testIsDeclaredInReturnsFalseWhenReferenceDoesNotStartWithNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Other\Core\Controller');

        self::assertFalse($reference->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsFalseWhenReferenceSharesPartialSegment(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\CoreExtra\Controller');

        self::assertFalse($reference->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsFalseWhenReferenceEqualsNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core');

        self::assertFalse($reference->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsTrueWhenReferenceIsDirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core\Controller');

        self::assertTrue($reference->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsTrueWhenReferenceIsIndirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core\Controller\AbstractController');

        self::assertTrue($reference->isDeclaredIn($namespacePrefix));
    }

    public function testIsOrIsDeclaredInOneOfReturnsFalseWhenNoNamespacePrefixesGiven(): void
    {
        $reference = Rules\Files\Reference::fromString('Example\Core\Controller');

        $isOrIsDeclaredInOneOf = $reference->isOrIsDeclaredInOneOf();

        self::assertFalse($isOrIsDeclaredInOneOf);
    }

    public function testIsOrIsDeclaredInOneOfReturnsFalseWhenReferenceDoesNotBelongToAny(): void
    {
        $namespacePrefixes = [
            Rules\Files\NamespacePrefix::fromString('Example\Core'),
            Rules\Files\NamespacePrefix::fromString('Example\Http'),
        ];

        $reference = Rules\Files\Reference::fromString('Other\Controller');

        $isOrIsDeclaredInOneOf = $reference->isOrIsDeclaredInOneOf(...$namespacePrefixes);

        self::assertFalse($isOrIsDeclaredInOneOf);
    }

    public function testIsOrIsDeclaredInOneOfReturnsTrueWhenDeclaredInOne(): void
    {
        $namespacePrefixes = [
            Rules\Files\NamespacePrefix::fromString('Example\Core'),
            Rules\Files\NamespacePrefix::fromString('Example\Http'),
        ];

        $reference = Rules\Files\Reference::fromString('Example\Core\Controller');

        $isOrIsDeclaredInOneOf = $reference->isOrIsDeclaredInOneOf(...$namespacePrefixes);

        self::assertTrue($isOrIsDeclaredInOneOf);
    }

    public function testIsOrIsDeclaredInOneOfReturnsTrueWhenReferenceEqualsPrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core');

        $isOrIsDeclaredInOneOf = $reference->isOrIsDeclaredInOneOf($namespacePrefix);

        self::assertTrue($isOrIsDeclaredInOneOf);
    }

    public function testRelativeToReturnsReferenceWhenReferenceIsDirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core\Controller');

        $mutated = $reference->relativeTo($namespacePrefix);

        self::assertNotSame($reference, $mutated);
        self::assertEquals(Rules\Files\Reference::fromString('Controller'), $mutated);
    }

    public function testRelativeToReturnsReferenceWhenReferenceIsIndirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $reference = Rules\Files\Reference::fromString('Example\Core\Controller\AbstractController');

        $mutated = $reference->relativeTo($namespacePrefix);

        self::assertNotSame($reference, $mutated);
        self::assertEquals(Rules\Files\Reference::fromString('Controller\AbstractController'), $mutated);
    }
}
