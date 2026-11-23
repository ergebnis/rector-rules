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
 * @covers \Ergebnis\Rector\Rules\Files\SymbolKind
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedNames
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferences
 */
final class SymbolKindTest extends Framework\TestCase
{
    public function testClassLikeReturnsSymbolKind(): void
    {
        $symbolKind = Rules\Files\SymbolKind::classLike();

        self::assertTrue($symbolKind->isClassLike());
        self::assertFalse($symbolKind->isConstant());
        self::assertFalse($symbolKind->isFunction());
    }

    public function testConstantReturnsSymbolKind(): void
    {
        $symbolKind = Rules\Files\SymbolKind::constant();

        self::assertFalse($symbolKind->isClassLike());
        self::assertTrue($symbolKind->isConstant());
        self::assertFalse($symbolKind->isFunction());
    }

    public function testFunctionReturnsSymbolKind(): void
    {
        $symbolKind = Rules\Files\SymbolKind::function();

        self::assertFalse($symbolKind->isClassLike());
        self::assertFalse($symbolKind->isConstant());
        self::assertTrue($symbolKind->isFunction());
    }

    public function testEqualsReturnsFalseWhenValuesAreDifferent(): void
    {
        $one = Rules\Files\SymbolKind::classLike();
        $two = Rules\Files\SymbolKind::function();

        self::assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueWhenValuesAreSame(): void
    {
        $one = Rules\Files\SymbolKind::constant();
        $two = Rules\Files\SymbolKind::constant();

        self::assertTrue($one->equals($two));
    }
}
