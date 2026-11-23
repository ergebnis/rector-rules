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
 * @covers \Ergebnis\Rector\Rules\Files\Import
 *
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 */
final class ImportTest extends Framework\TestCase
{
    public function testCreateReturnsImport(): void
    {
        $symbolKind = Rules\Files\SymbolKind::classLike();
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer');
        $localName = Rules\Files\LocalName::fromString('Mailer');

        $import = Rules\Files\Import::create(
            $symbolKind,
            $fullyQualifiedName,
            $localName,
        );

        self::assertSame($symbolKind, $import->symbolKind());
        self::assertSame($fullyQualifiedName, $import->fullyQualifiedName());
        self::assertSame($localName, $import->localName());
        self::assertNull($import->statement());
        self::assertNull($import->useItem());
    }

    public function testFromUseItemReturnsImportFromItemOfGroupImport(): void
    {
        $useItem = new Node\UseItem(
            new Node\Name('send'),
            null,
            Node\Stmt\Use_::TYPE_FUNCTION,
        );

        $statement = new Node\Stmt\GroupUse(
            new Node\Name('Symfony\Component\Mailer'),
            [
                $useItem,
            ],
        );

        $import = Rules\Files\Import::fromUseItem(
            Rules\Files\SymbolKind::function(),
            $statement,
            $useItem,
        );

        self::assertTrue($import->symbolKind()->isFunction());
        self::assertSame('Symfony\Component\Mailer\send', $import->fullyQualifiedName()->toString());
        self::assertSame('send', $import->localName()->toString());
        self::assertSame($statement, $import->statement());
        self::assertSame($useItem, $import->useItem());
    }

    public function testHasLocalNameReturnsFalseWhenLocalNameIsDifferent(): void
    {
        $import = Rules\Files\Import::create(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer'),
            Rules\Files\LocalName::fromString('Mailer'),
        );

        self::assertFalse($import->hasLocalName(Rules\Files\LocalName::fromString('Transport')));
    }

    public function testHasLocalNameReturnsTrueWhenLocalNameIsEqual(): void
    {
        $import = Rules\Files\Import::create(
            Rules\Files\SymbolKind::classLike(),
            Rules\Files\FullyQualifiedName::fromString('Symfony\Component\Mailer'),
            Rules\Files\LocalName::fromString('Mailer'),
        );

        self::assertTrue($import->hasLocalName(Rules\Files\LocalName::fromString('mailer')));
    }

    public function testHasLocalNameReturnsFalseWhenLocalNameOfConstantImportDiffersInCase(): void
    {
        $import = Rules\Files\Import::create(
            Rules\Files\SymbolKind::constant(),
            Rules\Files\FullyQualifiedName::fromString('Smarty\VERSION'),
            Rules\Files\LocalName::fromString('VERSION'),
        );

        self::assertFalse($import->hasLocalName(Rules\Files\LocalName::fromString('version')));
    }

    public function testHasLocalNameReturnsTrueWhenLocalNameOfFunctionImportDiffersInCase(): void
    {
        $import = Rules\Files\Import::create(
            Rules\Files\SymbolKind::function(),
            Rules\Files\FullyQualifiedName::fromString('Smarty\render'),
            Rules\Files\LocalName::fromString('render'),
        );

        self::assertTrue($import->hasLocalName(Rules\Files\LocalName::fromString('Render')));
    }
}
