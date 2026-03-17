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
 * @covers \Ergebnis\Rector\Rules\Files\NamespaceSegment
 */
final class NamespaceSegmentTest extends Framework\TestCase
{
    /**
     * @dataProvider provideInvalidValue
     */
    public function testFromStringRejectsInvalidValue(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Rules\Files\NamespaceSegment::fromString($value);
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideInvalidValue(): iterable
    {
        $values = [
            'string-contains-backslash' => 'Example\\Core',
            'string-contains-hyphen' => 'my-core',
            'string-contains-space' => 'my core',
            'string-empty' => '',
            'string-starts-with-digit' => '1Example',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    /**
     * @dataProvider provideValidValue
     */
    public function testFromStringReturnsNamespaceSegment(string $value): void
    {
        $namespaceSegment = Rules\Files\NamespaceSegment::fromString($value);

        self::assertSame($value, $namespaceSegment->toString());
    }

    /**
     * @return \Generator<string, array{0: string}>
     */
    public static function provideValidValue(): iterable
    {
        $values = [
            'string-letters' => 'Example',
            'string-letters-and-digits' => 'Example1',
            'string-starts-with-underscore' => '_Example',
            'string-with-underscore' => 'Example_Core',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    public function testEqualsReturnsFalseWhenValuesAreDifferent(): void
    {
        $one = Rules\Files\NamespaceSegment::fromString('Core');
        $two = Rules\Files\NamespaceSegment::fromString('Http');

        self::assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueWhenValuesAreTheSame(): void
    {
        $one = Rules\Files\NamespaceSegment::fromString('Core');
        $two = Rules\Files\NamespaceSegment::fromString('Core');

        self::assertTrue($one->equals($two));
    }
}
