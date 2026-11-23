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
 * @covers \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 *
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefixes
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 */
final class FullyQualifiedNameTest extends Framework\TestCase
{
    public function testFromStringReturnsFullyQualifiedName(): void
    {
        $value = 'Example\Core\Controller';

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString($value);

        self::assertSame($value, $fullyQualifiedName->toString());
    }

    public function testIsReturnsFalseWhenFullyQualifiedNameDoesNotEqualNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller');

        self::assertFalse($fullyQualifiedName->is($namespacePrefix));
    }

    public function testIsReturnsTrueWhenFullyQualifiedNameEqualsNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core');

        self::assertTrue($fullyQualifiedName->is($namespacePrefix));
    }

    public function testIsDeclaredInReturnsFalseWhenFullyQualifiedNameDoesNotStartWithNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Other\Core\Controller');

        self::assertFalse($fullyQualifiedName->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsFalseWhenFullyQualifiedNameSharesPartialSegment(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\CoreExtra\Controller');

        self::assertFalse($fullyQualifiedName->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsFalseWhenFullyQualifiedNameEqualsNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core');

        self::assertFalse($fullyQualifiedName->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsTrueWhenFullyQualifiedNameIsDirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller');

        self::assertTrue($fullyQualifiedName->isDeclaredIn($namespacePrefix));
    }

    public function testIsDeclaredInReturnsTrueWhenFullyQualifiedNameIsIndirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller\AbstractController');

        self::assertTrue($fullyQualifiedName->isDeclaredIn($namespacePrefix));
    }

    public function testIsOrIsDeclaredInReturnsFalseWhenFullyQualifiedNameIsNeitherNamespacePrefixNorDeclaredInIt(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\CoreExtension\Controller');

        self::assertFalse($fullyQualifiedName->isOrIsDeclaredIn($namespacePrefix));
    }

    public function testIsOrIsDeclaredInReturnsTrueWhenFullyQualifiedNameIsNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core');

        self::assertTrue($fullyQualifiedName->isOrIsDeclaredIn($namespacePrefix));
    }

    public function testIsOrIsDeclaredInReturnsTrueWhenFullyQualifiedNameIsDeclaredInNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller');

        self::assertTrue($fullyQualifiedName->isOrIsDeclaredIn($namespacePrefix));
    }

    public function testIsOrIsDeclaredInOneOfReturnsFalseWhenNoNamespacePrefixesGiven(): void
    {
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller');

        $isOrIsDeclaredInOneOf = $fullyQualifiedName->isOrIsDeclaredInOneOf(Rules\Files\NamespacePrefixes::create());

        self::assertFalse($isOrIsDeclaredInOneOf);
    }

    public function testIsOrIsDeclaredInOneOfReturnsFalseWhenFullyQualifiedNameDoesNotBelongToAny(): void
    {
        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(
            Rules\Files\NamespacePrefix::fromString('Example\Core'),
            Rules\Files\NamespacePrefix::fromString('Example\Http'),
        );

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Other\Controller');

        $isOrIsDeclaredInOneOf = $fullyQualifiedName->isOrIsDeclaredInOneOf($namespacePrefixes);

        self::assertFalse($isOrIsDeclaredInOneOf);
    }

    public function testIsOrIsDeclaredInOneOfReturnsTrueWhenDeclaredInOne(): void
    {
        $namespacePrefixes = Rules\Files\NamespacePrefixes::create(
            Rules\Files\NamespacePrefix::fromString('Example\Core'),
            Rules\Files\NamespacePrefix::fromString('Example\Http'),
        );

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller');

        $isOrIsDeclaredInOneOf = $fullyQualifiedName->isOrIsDeclaredInOneOf($namespacePrefixes);

        self::assertTrue($isOrIsDeclaredInOneOf);
    }

    public function testIsOrIsDeclaredInOneOfReturnsTrueWhenFullyQualifiedNameEqualsPrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core');

        $isOrIsDeclaredInOneOf = $fullyQualifiedName->isOrIsDeclaredInOneOf(Rules\Files\NamespacePrefixes::create($namespacePrefix));

        self::assertTrue($isOrIsDeclaredInOneOf);
    }

    public function testAppendReturnsFullyQualifiedNameWhenSingleSegmentIsSpecified(): void
    {
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core');

        $mutated = $fullyQualifiedName->append('Controller');

        self::assertNotSame($fullyQualifiedName, $mutated);
        self::assertEquals(Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller'), $mutated);
    }

    public function testAppendReturnsFullyQualifiedNameWhenMultipleSegmentsAreSpecified(): void
    {
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core');

        $mutated = $fullyQualifiedName->append(
            'Controller',
            'AbstractController',
        );

        self::assertNotSame($fullyQualifiedName, $mutated);
        self::assertEquals(Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller\AbstractController'), $mutated);
    }

    public function testRelativeToReturnsFullyQualifiedNameWhenFullyQualifiedNameIsDirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller');

        $mutated = $fullyQualifiedName->relativeTo($namespacePrefix);

        self::assertNotSame($fullyQualifiedName, $mutated);
        self::assertEquals(Rules\Files\FullyQualifiedName::fromString('Controller'), $mutated);
    }

    public function testRelativeToReturnsFullyQualifiedNameWhenFullyQualifiedNameIsIndirectChildOfNamespacePrefix(): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Example\Core');

        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Example\Core\Controller\AbstractController');

        $mutated = $fullyQualifiedName->relativeTo($namespacePrefix);

        self::assertNotSame($fullyQualifiedName, $mutated);
        self::assertEquals(Rules\Files\FullyQualifiedName::fromString('Controller\AbstractController'), $mutated);
    }
}
