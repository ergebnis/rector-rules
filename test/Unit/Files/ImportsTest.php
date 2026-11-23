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
 * @covers \Ergebnis\Rector\Rules\Files\Imports
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\Import
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferences
 */
final class ImportsTest extends Framework\TestCase
{
    public function testCreateReturnsImports(): void
    {
        $importOne = Rules\Files\Import::create(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer'),
            Rules\Files\LocalName::fromString('Mailer'),
        );
        $importTwo = Rules\Files\Import::create(
            Rules\Files\SymbolKind::function(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer\send'),
            Rules\Files\LocalName::fromString('send'),
        );

        $imports = Rules\Files\Imports::create(
            $importOne,
            $importTwo,
        );

        $expected = [
            $importOne,
            $importTwo,
        ];

        self::assertSame($expected, $imports->toArray());
    }

    public function testOfSymbolKindReturnsImportsOfSymbolKind(): void
    {
        $importOne = Rules\Files\Import::create(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer'),
            Rules\Files\LocalName::fromString('Mailer'),
        );
        $importTwo = Rules\Files\Import::create(
            Rules\Files\SymbolKind::function(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer\send'),
            Rules\Files\LocalName::fromString('send'),
        );
        $importThree = Rules\Files\Import::create(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Console'),
            Rules\Files\LocalName::fromString('Console'),
        );

        $imports = Rules\Files\Imports::create(
            $importOne,
            $importTwo,
            $importThree,
        );

        $expected = [
            $importOne,
            $importThree,
        ];

        self::assertSame($expected, $imports->ofSymbolKind(Rules\Files\SymbolKind::classLike())->toArray());
    }

    public function testForLocalNameReturnsNullWhenNoImportHasLocalName(): void
    {
        $imports = Rules\Files\Imports::create(Rules\Files\Import::create(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer'),
            Rules\Files\LocalName::fromString('Mailer'),
        ));

        self::assertNull($imports->forLocalName(Rules\Files\LocalName::fromString('Console')));
    }

    public function testForLocalNameReturnsImportWhenImportHasLocalName(): void
    {
        $expected = Rules\Files\Import::create(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer'),
            Rules\Files\LocalName::fromString('Mailer'),
        );

        $imports = Rules\Files\Imports::create($expected);

        self::assertSame($expected, $imports->forLocalName(Rules\Files\LocalName::fromString('mailer')));
    }
}
