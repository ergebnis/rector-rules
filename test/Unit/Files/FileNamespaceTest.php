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
 * @covers \Ergebnis\Rector\Rules\Files\FileNamespace
 *
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 */
final class FileNamespaceTest extends Framework\TestCase
{
    public function testGlobalReturnsFileNamespace(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::global();

        self::assertTrue($fileNamespace->isGlobal());
        self::assertSame('', $fileNamespace->toString());
    }

    public function testNamedReturnsFileNamespace(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertFalse($fileNamespace->isGlobal());
        self::assertSame('App\Mail', $fileNamespace->toString());
    }

    public function testIsReturnsFalseWhenFileNamespaceIsGlobal(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::global();

        self::assertFalse($fileNamespace->is(Rules\Files\FullyQualifiedName::fromString('App')));
    }

    public function testIsReturnsFalseWhenFullyQualifiedNameIsDifferent(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertFalse($fileNamespace->is(Rules\Files\FullyQualifiedName::fromString('App\Mail\Notifier')));
    }

    public function testIsReturnsTrueWhenFullyQualifiedNameIsSame(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertTrue($fileNamespace->is(Rules\Files\FullyQualifiedName::fromString('App\Mail')));
    }

    public function testContainsReturnsFalseWhenFileNamespaceIsGlobal(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::global();

        self::assertFalse($fileNamespace->contains(Rules\Files\FullyQualifiedName::fromString('App\Notifier')));
    }

    public function testContainsReturnsFalseWhenFullyQualifiedNameIsNotDeclaredInFileNamespace(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertFalse($fileNamespace->contains(Rules\Files\FullyQualifiedName::fromString('App\Log\Logger')));
    }

    public function testContainsReturnsTrueWhenFullyQualifiedNameIsDeclaredInFileNamespace(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertTrue($fileNamespace->contains(Rules\Files\FullyQualifiedName::fromString('App\Mail\Transport\Smtp')));
    }

    public function testIsOrContainsReturnsFalseWhenFileNamespaceIsGlobal(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::global();

        self::assertFalse($fileNamespace->isOrContains(Rules\Files\NamespacePrefix::fromString('App')));
    }

    public function testIsOrContainsReturnsFalseWhenFileNamespaceIsInsideNamespacePrefix(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertFalse($fileNamespace->isOrContains(Rules\Files\NamespacePrefix::fromString('App')));
    }

    public function testIsOrContainsReturnsTrueWhenFileNamespaceIsNamespacePrefix(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertTrue($fileNamespace->isOrContains(Rules\Files\NamespacePrefix::fromString('App\Mail')));
    }

    public function testIsOrContainsReturnsTrueWhenFileNamespaceContainsNamespacePrefix(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App'));

        self::assertTrue($fileNamespace->isOrContains(Rules\Files\NamespacePrefix::fromString('App\Mail')));
    }

    public function testIsInsideReturnsFalseWhenFileNamespaceIsGlobal(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::global();

        self::assertFalse($fileNamespace->isInside(Rules\Files\NamespacePrefix::fromString('App')));
    }

    public function testIsInsideReturnsFalseWhenFileNamespaceIsNamespacePrefix(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App'));

        self::assertFalse($fileNamespace->isInside(Rules\Files\NamespacePrefix::fromString('App')));
    }

    public function testIsInsideReturnsTrueWhenFileNamespaceIsInsideNamespacePrefix(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail'));

        self::assertTrue($fileNamespace->isInside(Rules\Files\NamespacePrefix::fromString('App')));
    }

    public function testQualifyReturnsNameWhenFileNamespaceIsGlobal(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::global();

        self::assertSame('Mailer\Transport', $fileNamespace->qualify(Rules\Files\Spelling::fromString('Mailer\Transport'))->toString());
    }

    public function testQualifyReturnsNameInFileNamespaceWhenFileNamespaceIsNamed(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App'));

        self::assertSame('App\Mailer\Transport', $fileNamespace->qualify(Rules\Files\Spelling::fromString('Mailer\Transport'))->toString());
    }

    public function testRelativeNameReturnsFullyQualifiedNameWhenFileNamespaceIsGlobal(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::global();

        self::assertSame('App\Mail\Notifier', $fileNamespace->relativeName(Rules\Files\FullyQualifiedName::fromString('App\Mail\Notifier'))->toString());
    }

    public function testRelativeNameReturnsNameRelativeToFileNamespaceWhenFileNamespaceIsNamed(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App'));

        self::assertSame('Mail\Notifier', $fileNamespace->relativeName(Rules\Files\FullyQualifiedName::fromString('App\Mail\Notifier'))->toString());
    }

    public function testQualifyReturnsFullyQualifiedNameWhenSpellingIsFullyQualified(): void
    {
        $fileNamespace = Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App'));

        self::assertSame('Smarty\Smarty', $fileNamespace->qualify(Rules\Files\Spelling::fromString('\Smarty\Smarty'))->toString());
    }
}
