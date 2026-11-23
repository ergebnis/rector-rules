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
 * @covers \Ergebnis\Rector\Rules\Files\SymbolReferenceLocation
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedNames
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferences
 */
final class SymbolReferenceLocationTest extends Framework\TestCase
{
    public function testCodeReturnsSymbolReferenceLocation(): void
    {
        $location = Rules\Files\SymbolReferenceLocation::code();

        self::assertTrue($location->isCode());
        self::assertFalse($location->isDocBlockText());
        self::assertFalse($location->isDocBlockType());
    }

    public function testDocBlockTextReturnsSymbolReferenceLocation(): void
    {
        $location = Rules\Files\SymbolReferenceLocation::docBlockText();

        self::assertFalse($location->isCode());
        self::assertTrue($location->isDocBlockText());
        self::assertFalse($location->isDocBlockType());
    }

    public function testDocBlockTypeReturnsSymbolReferenceLocation(): void
    {
        $location = Rules\Files\SymbolReferenceLocation::docBlockType();

        self::assertFalse($location->isCode());
        self::assertFalse($location->isDocBlockText());
        self::assertTrue($location->isDocBlockType());
    }
}
