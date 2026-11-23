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
 * @covers \Ergebnis\Rector\Rules\Files\NameResolver
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\FileNamespace
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\Import
 * @uses \Ergebnis\Rector\Rules\Files\Imports
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferences
 */
final class NameResolverTest extends Framework\TestCase
{
    /**
     * @dataProvider provideNameAndSymbolKindThatCanNotBeResolved
     */
    public function testResolveReturnsNullWhenNameCanNotBeResolvedWithoutRunningCode(
        string $name,
        Rules\Files\SymbolKind $symbolKind
    ): void {
        $nameResolver = new Rules\Files\NameResolver();

        $fullyQualifiedName = $nameResolver->resolve(
            Rules\Files\Spelling::fromString($name),
            $symbolKind,
            Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App')),
            self::imports(),
        );

        self::assertNull($fullyQualifiedName);
    }

    /**
     * @return \Generator<string, array{0: string, 1: Rules\Files\SymbolKind}>
     */
    public static function provideNameAndSymbolKindThatCanNotBeResolved(): iterable
    {
        $values = [
            'empty' => [
                '',
                Rules\Files\SymbolKind::classLike(),
            ],
            'parent' => [
                'parent',
                Rules\Files\SymbolKind::classLike(),
            ],
            'self' => [
                'self',
                Rules\Files\SymbolKind::classLike(),
            ],
            'static-in-different-case' => [
                'Static',
                Rules\Files\SymbolKind::classLike(),
            ],
            'unqualified-constant-without-import' => [
                'PHP_EOL',
                Rules\Files\SymbolKind::constant(),
            ],
            'unqualified-function-without-import' => [
                'strlen',
                Rules\Files\SymbolKind::function(),
            ],
        ];

        foreach ($values as $key => [$name, $symbolKind]) {
            yield $key => [
                $name,
                $symbolKind,
            ];
        }
    }

    /**
     * @dataProvider provideNameSymbolKindAndExpectedReferenceInNamespace
     */
    public function testResolveReturnsReferenceWhenNameIsInNamespace(
        string $name,
        Rules\Files\SymbolKind $symbolKind,
        string $expectedReference
    ): void {
        $nameResolver = new Rules\Files\NameResolver();

        $fullyQualifiedName = $nameResolver->resolve(
            Rules\Files\Spelling::fromString($name),
            $symbolKind,
            Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App')),
            self::imports(),
        );

        self::assertEquals(Rules\Files\FullyQualifiedName::fromString($expectedReference), $fullyQualifiedName);
    }

    /**
     * @return \Generator<string, array{0: string, 1: Rules\Files\SymbolKind, 2: string}>
     */
    public static function provideNameSymbolKindAndExpectedReferenceInNamespace(): iterable
    {
        $values = [
            'fully-qualified-class' => [
                '\Smarty\Smarty',
                Rules\Files\SymbolKind::classLike(),
                'Smarty\Smarty',
            ],
            'fully-qualified-function' => [
                '\Smarty\render',
                Rules\Files\SymbolKind::function(),
                'Smarty\render',
            ],
            'qualified-class-relative-to-namespace-keyword' => [
                'namespace\Mailer\Transport',
                Rules\Files\SymbolKind::classLike(),
                'App\Mailer\Transport',
            ],
            'qualified-class-through-class-import' => [
                'Smarty\Smarty',
                Rules\Files\SymbolKind::classLike(),
                'Smarty\Smarty',
            ],
            'qualified-class-through-class-import-in-different-case' => [
                'smarty\Exception',
                Rules\Files\SymbolKind::classLike(),
                'Smarty\Exception',
            ],
            'qualified-class-through-class-import-with-alias' => [
                'Foo\HttpFoundation\Request',
                Rules\Files\SymbolKind::classLike(),
                'Symfony\Component\HttpFoundation\Request',
            ],
            'qualified-class-through-namespace' => [
                'Mailer\Transport',
                Rules\Files\SymbolKind::classLike(),
                'App\Mailer\Transport',
            ],
            'qualified-function-through-class-import' => [
                'Smarty\render',
                Rules\Files\SymbolKind::function(),
                'Smarty\render',
            ],
            'unqualified-class-through-class-import' => [
                'Request',
                Rules\Files\SymbolKind::classLike(),
                'Symfony\Component\HttpFoundation\Request',
            ],
            'unqualified-class-through-class-import-in-different-case' => [
                'request',
                Rules\Files\SymbolKind::classLike(),
                'Symfony\Component\HttpFoundation\Request',
            ],
            'unqualified-class-through-namespace' => [
                'Notifier',
                Rules\Files\SymbolKind::classLike(),
                'App\Notifier',
            ],
            'unqualified-constant-through-constant-import' => [
                'VERSION',
                Rules\Files\SymbolKind::constant(),
                'Smarty\VERSION',
            ],
            'unqualified-function-through-function-import' => [
                'render',
                Rules\Files\SymbolKind::function(),
                'Smarty\render',
            ],
            'unqualified-function-through-function-import-in-different-case' => [
                'Render',
                Rules\Files\SymbolKind::function(),
                'Smarty\render',
            ],
        ];

        foreach ($values as $key => [$name, $symbolKind, $expectedReference]) {
            yield $key => [
                $name,
                $symbolKind,
                $expectedReference,
            ];
        }
    }

    public function testResolveReturnsNullWhenConstantImportDiffersInCase(): void
    {
        $nameResolver = new Rules\Files\NameResolver();

        $fullyQualifiedName = $nameResolver->resolve(
            Rules\Files\Spelling::fromString('version'),
            Rules\Files\SymbolKind::constant(),
            Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App')),
            self::imports(),
        );

        self::assertNull($fullyQualifiedName);
    }

    /**
     * @dataProvider provideNameSymbolKindAndExpectedReferenceInGlobalNamespace
     */
    public function testResolveReturnsReferenceWhenNameIsInGlobalNamespace(
        string $name,
        Rules\Files\SymbolKind $symbolKind,
        string $expectedReference
    ): void {
        $nameResolver = new Rules\Files\NameResolver();

        $fullyQualifiedName = $nameResolver->resolve(
            Rules\Files\Spelling::fromString($name),
            $symbolKind,
            Rules\Files\FileNamespace::global(),
            self::imports(),
        );

        self::assertEquals(Rules\Files\FullyQualifiedName::fromString($expectedReference), $fullyQualifiedName);
    }

    /**
     * @return \Generator<string, array{0: string, 1: Rules\Files\SymbolKind, 2: string}>
     */
    public static function provideNameSymbolKindAndExpectedReferenceInGlobalNamespace(): iterable
    {
        $values = [
            'qualified-class-without-import' => [
                'Mailer\Transport',
                Rules\Files\SymbolKind::classLike(),
                'Mailer\Transport',
            ],
            'unqualified-class-without-import' => [
                'Notifier',
                Rules\Files\SymbolKind::classLike(),
                'Notifier',
            ],
            'unqualified-constant-without-import' => [
                'PHP_EOL',
                Rules\Files\SymbolKind::constant(),
                'PHP_EOL',
            ],
            'unqualified-function-without-import' => [
                'strlen',
                Rules\Files\SymbolKind::function(),
                'strlen',
            ],
        ];

        foreach ($values as $key => [$name, $symbolKind, $expectedReference]) {
            yield $key => [
                $name,
                $symbolKind,
                $expectedReference,
            ];
        }
    }

    private static function imports(): Rules\Files\Imports
    {
        return Rules\Files\Imports::create(
            Rules\Files\Import::create(
                Rules\Files\SymbolKind::classLike(),
                Rules\Files\FullyQualifiedName::fromString('Smarty'),
                Rules\Files\LocalName::fromString('Smarty'),
            ),
            Rules\Files\Import::create(
                Rules\Files\SymbolKind::classLike(),
                Rules\Files\FullyQualifiedName::fromString('Symfony\Component'),
                Rules\Files\LocalName::fromString('Foo'),
            ),
            Rules\Files\Import::create(
                Rules\Files\SymbolKind::classLike(),
                Rules\Files\FullyQualifiedName::fromString('Symfony\Component\HttpFoundation\Request'),
                Rules\Files\LocalName::fromString('Request'),
            ),
            Rules\Files\Import::create(
                Rules\Files\SymbolKind::constant(),
                Rules\Files\FullyQualifiedName::fromString('Smarty\VERSION'),
                Rules\Files\LocalName::fromString('VERSION'),
            ),
            Rules\Files\Import::create(
                Rules\Files\SymbolKind::function(),
                Rules\Files\FullyQualifiedName::fromString('Smarty\render'),
                Rules\Files\LocalName::fromString('render'),
            ),
        );
    }
}
