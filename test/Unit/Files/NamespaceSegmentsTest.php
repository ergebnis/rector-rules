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
 * @covers \Ergebnis\Rector\Rules\Files\NamespaceSegments
 *
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 */
final class NamespaceSegmentsTest extends Framework\TestCase
{
    /**
     * @dataProvider provideInvalidValue
     */
    public function testFromStringRejectsInvalidValue(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Rules\Files\NamespaceSegments::fromString($value);
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideInvalidValue(): iterable
    {
        $values = [
            'string-empty' => '',
            'string-segment-starts-with-digit' => 'Example\1Core',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    /**
     * @dataProvider provideValidValue
     */
    public function testFromStringReturnsNamespaceSegments(string $value): void
    {
        $namespaceSegments = Rules\Files\NamespaceSegments::fromString($value);

        self::assertSame($value, $namespaceSegments->toString());
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideValidValue(): iterable
    {
        $values = [
            'string-single-segment' => 'Example',
            'string-three-segments' => 'Example\Core\Http',
            'string-two-segments' => 'Example\Core',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    public function testCountReturnsNumberOfSegments(): void
    {
        $namespaceSegments = Rules\Files\NamespaceSegments::fromString('Example\Core\Http');

        self::assertSame(3, $namespaceSegments->count());
    }

    public function testCountReturnsOneForSingleSegment(): void
    {
        $namespaceSegments = Rules\Files\NamespaceSegments::fromString('Example');

        self::assertSame(1, $namespaceSegments->count());
    }

    public function testEqualsReturnsFalseWhenValuesAreDifferent(): void
    {
        $one = Rules\Files\NamespaceSegments::fromString('Example\Core');
        $two = Rules\Files\NamespaceSegments::fromString('Example\Http');

        self::assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueWhenValuesAreTheSame(): void
    {
        $one = Rules\Files\NamespaceSegments::fromString('Example\Core');
        $two = Rules\Files\NamespaceSegments::fromString('Example\Core');

        self::assertTrue($one->equals($two));
    }

    /**
     * @dataProvider provideValueAndExpectedLastSegment
     */
    public function testLastReturnsLastSegment(
        string $value,
        string $expectedLastSegment
    ): void {
        $namespaceSegments = Rules\Files\NamespaceSegments::fromString($value);

        self::assertEquals(Rules\Files\NamespaceSegment::fromString($expectedLastSegment), $namespaceSegments->last());
    }

    /**
     * @return \Generator<string, array{0: string, 1: string}>
     */
    public static function provideValueAndExpectedLastSegment(): iterable
    {
        $values = [
            'segments-one' => [
                'Example',
                'Example',
            ],
            'segments-three' => [
                'Example\Core\Http',
                'Http',
            ],
            'segments-two' => [
                'Example\Core',
                'Core',
            ],
        ];

        foreach ($values as $key => [$value, $expectedLastSegment]) {
            yield $key => [
                $value,
                $expectedLastSegment,
            ];
        }
    }
}
