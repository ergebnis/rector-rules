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
 * @covers \Ergebnis\Rector\Rules\Files\NamespacePrefix
 *
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 * @uses \Ergebnis\Rector\Rules\Files\Reference
 */
final class NamespacePrefixTest extends Framework\TestCase
{
    /**
     * @dataProvider provideInvalidValue
     */
    public function testFromStringRejectsInvalidValue(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Rules\Files\NamespacePrefix::fromString($value);
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideInvalidValue(): iterable
    {
        $values = [
            'string-contains-hyphen' => 'Example\my-core',
            'string-contains-space' => 'Example\ Core',
            'string-double-backslash' => 'Example\\\\Core',
            'string-empty' => '',
            'string-ends-with-backslash' => 'Example\Core\\',
            'string-segment-starts-with-digit' => 'Example\1Core',
            'string-starts-with-backslash' => '\Example\Core',
            'string-starts-with-digit' => '1Example\Core',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider provideValidValue
     */
    public function testFromStringReturnsNamespacePrefix(string $value): void
    {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString($value);

        self::assertSame($value, $namespacePrefix->toString());
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideValidValue(): iterable
    {
        $values = [
            'string-single-segment' => 'Example',
            'string-starts-with-underscore' => '_Example\Core',
            'string-three-segments' => 'Example\Core\Http',
            'string-two-segments' => 'Example\Core',
            'string-with-underscore' => 'Example\_Core',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    /**
     * @dataProvider provideValueAndNamespaceSegmentCount
     */
    public function testNamespaceSegmentCountReturnsNamespaceSegmentCount(
        string $value,
        int $namespaceSegmentCount
    ): void {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString($value);

        self::assertSame($namespaceSegmentCount, $namespacePrefix->namespaceSegmentCount());
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideValueAndNamespaceSegmentCount(): iterable
    {
        $values = [
            'string-segments-1' => [
                'Example',
                1,
            ],
            'string-segments-2' => [
                'Example\Core',
                2,
            ],
            'string-segments-3' => [
                'Example\Core\Http',
                3,
            ],
        ];

        foreach ($values as $key => [$value, $segmentCount]) {
            yield $key => [
                $value,
                $segmentCount,
            ];
        }
    }

    /**
     * @dataProvider provideValueAndLastNamespaceSegment
     */
    public function testLastNamespaceSegmentReturnsLastNamespaceSegment(
        string $value,
        string $lastNamespaceSegment
    ): void {
        $namespacePrefix = Rules\Files\NamespacePrefix::fromString($value);

        self::assertSame($lastNamespaceSegment, $namespacePrefix->lastNamespaceSegment()->toString());
    }

    /**
     * @return \Generator<string, array{0: string, 1: string}>
     */
    public static function provideValueAndLastNamespaceSegment(): iterable
    {
        $values = [
            'string-segments-one' => [
                'Example',
                'Example',
            ],
            'string-segments-three' => [
                'Example\Core\Http',
                'Http',
            ],
            'string-segments-two' => [
                'Example\Core',
                'Core',
            ],
        ];

        foreach ($values as $key => [$value, $lastSegment]) {
            yield $key => [
                $value,
                $lastSegment,
            ];
        }
    }

    public function testIsNamespacePrefixOfReturnsFalseWhenNamespaceSegmentsAreEqual(): void
    {
        $one = Rules\Files\NamespacePrefix::fromString('Example\Core');
        $two = Rules\Files\NamespacePrefix::fromString('Example\Core');

        self::assertFalse($one->isNamespacePrefixOf($two));
    }

    public function testIsNamespacePrefixOfReturnsFalseWhenNamespaceSegmentsAreDifferent(): void
    {
        $one = Rules\Files\NamespacePrefix::fromString('Example\Core');
        $two = Rules\Files\NamespacePrefix::fromString('Other\Http');

        self::assertFalse($one->isNamespacePrefixOf($two));
    }

    public function testIsNamespacePrefixOfReturnsFalseWhenNamespaceSegmentsArePartiallyEqual(): void
    {
        $one = Rules\Files\NamespacePrefix::fromString('Example\Core');
        $two = Rules\Files\NamespacePrefix::fromString('Example\CorelDraw');

        self::assertFalse($one->isNamespacePrefixOf($two));
    }

    public function testIsNamespacePrefixOfReturnsFalseWhenOtherIsFather(): void
    {
        $one = Rules\Files\NamespacePrefix::fromString('Example\Core\Routing');
        $two = Rules\Files\NamespacePrefix::fromString('Example\Core');

        self::assertFalse($one->isNamespacePrefixOf($two));
    }

    public function testIsNamespacePrefixOfReturnsTrueWhenOtherIsSon(): void
    {
        $one = Rules\Files\NamespacePrefix::fromString('Example\Core');
        $two = Rules\Files\NamespacePrefix::fromString('Example\Core\Routing');

        self::assertTrue($one->isNamespacePrefixOf($two));
    }

    public function testIsNamespacePrefixOfReturnsTrueWhenOtherIsGrandson(): void
    {
        $one = Rules\Files\NamespacePrefix::fromString('Example\Core');
        $two = Rules\Files\NamespacePrefix::fromString('Example\Core\Routing\Exception');

        self::assertTrue($one->isNamespacePrefixOf($two));
    }

    public function testAppendReturnsNamespacePrefixWithMultipleAppendedSegments(): void
    {
        $namespaceSegments = [
            Rules\Files\NamespaceSegment::fromString('Console'),
            Rules\Files\NamespaceSegment::fromString('Command'),
        ];

        $namespacePrefix = Rules\Files\NamespacePrefix::fromString('Symfony\Component');

        $mutated = $namespacePrefix->append(...$namespaceSegments);

        self::assertNotSame($namespacePrefix, $mutated);
        self::assertEquals(Rules\Files\NamespacePrefix::fromString('Symfony\Component\Console\Command'), $mutated);
    }
}
