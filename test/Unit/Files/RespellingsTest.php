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
use PhpParser\Node;
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Files\Respellings
 *
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\Respelling
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReference
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferenceLocation
 */
final class RespellingsTest extends Framework\TestCase
{
    public function testCreateReturnsRespellings(): void
    {
        $respellingOne = Rules\Files\Respelling::create(
            self::symbolReference('Smarty\Smarty'),
            Rules\Files\Spelling::fromString('Smarty'),
        );
        $respellingTwo = Rules\Files\Respelling::create(
            self::symbolReference('Smarty\Template'),
            Rules\Files\Spelling::fromString('Template'),
        );

        $respellings = Rules\Files\Respellings::create(
            $respellingOne,
            $respellingTwo,
        );

        $expected = [
            $respellingOne,
            $respellingTwo,
        ];

        self::assertFalse($respellings->isEmpty());
        self::assertSame($expected, $respellings->toArray());
    }

    public function testIsEmptyReturnsTrueWhenThereAreNoRespellings(): void
    {
        $respellings = Rules\Files\Respellings::create();

        self::assertTrue($respellings->isEmpty());
    }

    public function testSpellingOfReturnsNullWhenSymbolReferenceIsNotRespelled(): void
    {
        $respellings = Rules\Files\Respellings::create(Rules\Files\Respelling::create(
            self::symbolReference('Smarty\Smarty'),
            Rules\Files\Spelling::fromString('Smarty'),
        ));

        self::assertNull($respellings->spellingOf(self::symbolReference('Smarty\Smarty')));
    }

    public function testSpellingOfReturnsSpellingWhenSymbolReferenceIsRespelled(): void
    {
        $symbolReference = self::symbolReference('Smarty\Smarty');
        $spelling = Rules\Files\Spelling::fromString('Smarty');

        $respellings = Rules\Files\Respellings::create(Rules\Files\Respelling::create(
            $symbolReference,
            $spelling,
        ));

        self::assertSame($spelling, $respellings->spellingOf($symbolReference));
    }

    private static function symbolReference(string $name): Rules\Files\SymbolReference
    {
        return Rules\Files\SymbolReference::inCode(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\Spelling::fromString($name),
            Rules\Files\FullyQualifiedName::fromString($name),
            new Node\Name($name),
        );
    }
}
