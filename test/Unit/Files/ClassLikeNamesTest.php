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
 * @covers \Ergebnis\Rector\Rules\Files\ClassLikeNames
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 */
final class ClassLikeNamesTest extends Framework\TestCase
{
    public function testCreateReturnsClassLikeNames(): void
    {
        $classLikeNameOne = Rules\Files\ClassLikeName::fromString('Notifier');
        $classLikeNameTwo = Rules\Files\ClassLikeName::fromString('Transport');

        $classLikeNames = Rules\Files\ClassLikeNames::create(
            $classLikeNameOne,
            $classLikeNameTwo,
        );

        $expected = [
            $classLikeNameOne,
            $classLikeNameTwo,
        ];

        self::assertSame($expected, $classLikeNames->toArray());
    }

    public function testContainsReturnsFalseWhenNameIsNotContained(): void
    {
        $classLikeNames = Rules\Files\ClassLikeNames::create(Rules\Files\ClassLikeName::fromString('Notifier'));

        self::assertFalse($classLikeNames->contains(Rules\Files\ClassLikeName::fromString('Mailer')));
    }

    public function testContainsReturnsTrueWhenNameIsContained(): void
    {
        $classLikeNames = Rules\Files\ClassLikeNames::create(Rules\Files\ClassLikeName::fromString('mailer'));

        self::assertTrue($classLikeNames->contains(Rules\Files\ClassLikeName::fromString('Mailer')));
    }
}
