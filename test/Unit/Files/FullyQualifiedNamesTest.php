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
 * @covers \Ergebnis\Rector\Rules\Files\FullyQualifiedNames
 *
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 */
final class FullyQualifiedNamesTest extends Framework\TestCase
{
    public function testCreateReturnsFullyQualifiedNamesWithoutDuplicates(): void
    {
        $fullyQualifiedNameOne = Rules\Files\FullyQualifiedName::fromString('Smarty\Smarty');
        $fullyQualifiedNameTwo = Rules\Files\FullyQualifiedName::fromString('Smarty\Template');

        $fullyQualifiedNames = Rules\Files\FullyQualifiedNames::create(
            $fullyQualifiedNameOne,
            $fullyQualifiedNameTwo,
            Rules\Files\FullyQualifiedName::fromString('Smarty\Smarty'),
        );

        $expected = [
            $fullyQualifiedNameOne,
            $fullyQualifiedNameTwo,
        ];

        self::assertSame($expected, $fullyQualifiedNames->toArray());
    }

    public function testContainsReturnsFalseWhenFullyQualifiedNameIsNotContained(): void
    {
        $fullyQualifiedNames = Rules\Files\FullyQualifiedNames::create(Rules\Files\FullyQualifiedName::fromString('Smarty\Smarty'));

        self::assertFalse($fullyQualifiedNames->contains(Rules\Files\FullyQualifiedName::fromString('Smarty\Template')));
    }

    public function testContainsReturnsTrueWhenFullyQualifiedNameIsContained(): void
    {
        $fullyQualifiedNames = Rules\Files\FullyQualifiedNames::create(Rules\Files\FullyQualifiedName::fromString('Smarty\Smarty'));

        self::assertTrue($fullyQualifiedNames->contains(Rules\Files\FullyQualifiedName::fromString('Smarty\Smarty')));
    }
}
