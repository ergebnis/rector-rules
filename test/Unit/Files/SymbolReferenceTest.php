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
use PHPStan\PhpDocParser;
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Files\SymbolReference
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferenceLocation
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferences
 */
final class SymbolReferenceTest extends Framework\TestCase
{
    public function testInCodeReturnsSymbolReference(): void
    {
        $symbolKind = Rules\Files\SymbolKind::function();
        $spelling = Rules\Files\Spelling::fromString('Smarty\render');
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Smarty\render');
        $name = new Node\Name($spelling->toString());

        $symbolReference = Rules\Files\SymbolReference::inCode(
            $symbolKind,
            $spelling,
            $fullyQualifiedName,
            $name,
        );

        self::assertTrue($symbolReference->location()->isCode());
        self::assertSame($symbolKind, $symbolReference->symbolKind());
        self::assertSame($spelling, $symbolReference->spelling());
        self::assertSame($fullyQualifiedName, $symbolReference->fullyQualifiedName());
        self::assertSame($name, $symbolReference->node());
        self::assertNull($symbolReference->phpDocNode());
    }

    public function testInDocBlockTextReturnsSymbolReference(): void
    {
        $spelling = Rules\Files\Spelling::fromString('Smarty\Exception');
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Smarty\Exception');
        $node = new Node\Stmt\Nop();

        $symbolReference = Rules\Files\SymbolReference::inDocBlockText(
            $spelling,
            $fullyQualifiedName,
            $node,
        );

        self::assertTrue($symbolReference->location()->isDocBlockText());
        self::assertTrue($symbolReference->symbolKind()->isClassLike());
        self::assertSame($spelling, $symbolReference->spelling());
        self::assertSame($fullyQualifiedName, $symbolReference->fullyQualifiedName());
        self::assertSame($node, $symbolReference->node());
        self::assertNull($symbolReference->phpDocNode());
    }

    public function testInDocBlockTypeReturnsSymbolReferenceWhenPhpDocNodeIsIdentifierTypeNode(): void
    {
        $spelling = Rules\Files\Spelling::fromString('Smarty\Smarty');
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Smarty\Smarty');
        $node = new Node\Stmt\Nop();
        $typeNode = new PhpDocParser\Ast\Type\IdentifierTypeNode($spelling->toString());

        $symbolReference = Rules\Files\SymbolReference::inDocBlockType(
            $spelling,
            $fullyQualifiedName,
            $node,
            $typeNode,
        );

        self::assertTrue($symbolReference->location()->isDocBlockType());
        self::assertTrue($symbolReference->symbolKind()->isClassLike());
        self::assertSame($spelling, $symbolReference->spelling());
        self::assertSame($fullyQualifiedName, $symbolReference->fullyQualifiedName());
        self::assertSame($node, $symbolReference->node());
        self::assertSame($typeNode, $symbolReference->phpDocNode());
    }

    public function testInDocBlockTypeReturnsSymbolReferenceWhenPhpDocNodeIsConstFetchNode(): void
    {
        $spelling = Rules\Files\Spelling::fromString('Smarty\Smarty');
        $fullyQualifiedName = Rules\Files\FullyQualifiedName::fromString('Smarty\Smarty');
        $node = new Node\Stmt\Nop();
        $constFetchNode = new PhpDocParser\Ast\ConstExpr\ConstFetchNode(
            $spelling->toString(),
            'CACHING_*',
        );

        $symbolReference = Rules\Files\SymbolReference::inDocBlockType(
            $spelling,
            $fullyQualifiedName,
            $node,
            $constFetchNode,
        );

        self::assertTrue($symbolReference->location()->isDocBlockType());
        self::assertTrue($symbolReference->symbolKind()->isClassLike());
        self::assertSame($spelling, $symbolReference->spelling());
        self::assertSame($fullyQualifiedName, $symbolReference->fullyQualifiedName());
        self::assertSame($node, $symbolReference->node());
        self::assertSame($constFetchNode, $symbolReference->phpDocNode());
    }
}
