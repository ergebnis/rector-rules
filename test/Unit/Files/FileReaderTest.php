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

/**
 * @covers \Ergebnis\Rector\Rules\Files\File
 * @covers \Ergebnis\Rector\Rules\Files\FileReader
 * @covers \Ergebnis\Rector\Rules\Files\SymbolReferences
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\FileNamespace
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\Import
 * @uses \Ergebnis\Rector\Rules\Files\Imports
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\NameResolver
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReference
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferenceLocation
 */
final class FileReaderTest extends AbstractFileTestCase
{
    public function testReadReturnsFileWithNamespaceAndDeclaredClassLikeNames(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App\Mail;

            final class Notifier {}

            interface Transport {}

            trait HasMailer {}
            PHP);

        self::assertEquals(Rules\Files\FileNamespace::named(Rules\Files\NamespacePrefix::fromString('App\Mail')), $file->namespace());

        $expected = [
            'Notifier',
            'Transport',
            'HasMailer',
        ];

        self::assertSame($expected, \array_map(static function (Rules\Files\ClassLikeName $classLikeName): string {
            return $classLikeName->toString();
        }, $file->declaredClassLikeNames()->toArray()));
    }

    public function testReadReturnsFileWithImports(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App;

            use Smarty;
            use Symfony\Component\Mailer as Mail;
            use Symfony\Component\{Console, HttpFoundation\Request};
            use Symfony\Component\Mailer\{MailerInterface, function send, const VERSION};
            use function Smarty\render;
            use const Smarty\SMARTY_DIR;
            PHP);

        $expected = [
            'class-like Smarty as Smarty',
            'class-like Symfony\Component\Mailer as Mail',
            'class-like Symfony\Component\Console as Console',
            'class-like Symfony\Component\HttpFoundation\Request as Request',
            'class-like Symfony\Component\Mailer\MailerInterface as MailerInterface',
            'function Symfony\Component\Mailer\send as send',
            'constant Symfony\Component\Mailer\VERSION as VERSION',
            'function Smarty\render as render',
            'constant Smarty\SMARTY_DIR as SMARTY_DIR',
        ];

        self::assertSame($expected, \array_map(static function (Rules\Files\Import $import): string {
            return \sprintf(
                '%s %s as %s',
                self::describeSymbolKind($import->symbolKind()),
                $import->fullyQualifiedName()->toString(),
                $import->localName()->toString(),
            );
        }, $file->imports()->toArray()));
    }

    public function testReadReturnsFileWithSymbolReferencesInCode(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App;

            use Smarty;

            final class View extends \Base\View
            {
                public function render(Smarty\Smarty $smarty, Mailer\Transport $transport): void
                {
                    strlen('');
                    Smarty\render($smarty);
                }
            }
            PHP);

        $expected = [
            'code class-like \Base\View -> Base\View',
            'code class-like Smarty\Smarty -> Smarty\Smarty',
            'code class-like Mailer\Transport -> App\Mailer\Transport',
            'code function strlen -> null',
            'code function Smarty\render -> Smarty\render',
        ];

        self::assertSame($expected, self::describeSymbolReferences($file));
    }

    public function testReadReturnsFileWithSymbolReferencesInDocBlocks(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App;

            use Smarty;

            final class View
            {
                /**
                 * Renders with Smarty\Smarty, see \Smarty\Template.
                 *
                 * @param array{smarty: Smarty\Smarty} $options
                 *
                 * @see Smarty\Exception
                 */
                public function render(array $options): void
                {
                }
            }
            PHP);

        $expected = [
            'doc-block-type class-like Smarty\Smarty -> Smarty\Smarty',
            'doc-block-text class-like \Smarty\Template -> Smarty\Template',
            'doc-block-text class-like Smarty\Smarty -> Smarty\Smarty',
            'doc-block-text class-like Smarty\Exception -> Smarty\Exception',
        ];

        self::assertSame($expected, self::describeSymbolReferences($file));
    }

    public function testReadReturnsFileWithoutSymbolReferencesForKeywordsAndLocalTypeNamesInDocBlocks(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App;

            use App\Http\Resource;

            /**
             * @template T
             *
             * @phpstan-type Row array{id: int}
             * @phpstan-import-type Filter from Query as QueryFilter
             */
            final class Collection
            {
                /**
                 * @template TKey of array-key
                 *
                 * @param list<Item> $items
                 * @param TKey $key
                 * @param T $value
                 * @param int<0, max> $count
                 * @param Row $row
                 * @param QueryFilter $filter
                 *
                 * @return $this|self|null|Resource
                 */
                public function add($items, $key, $value, $count, $row, $filter)
                {
                }

                /**
                 * @param TKey $key
                 */
                public function remove($key)
                {
                }
            }
            PHP);

        $expected = [
            'doc-block-type class-like Query -> App\Query',
            'doc-block-type class-like Item -> App\Item',
            'doc-block-type class-like Resource -> App\Http\Resource',
            'doc-block-type class-like TKey -> App\TKey',
        ];

        self::assertSame($expected, self::describeSymbolReferences($file));
    }

    /**
     * @return list<string>
     */
    private static function describeSymbolReferences(Rules\Files\File $file): array
    {
        return \array_map(static function (Rules\Files\SymbolReference $symbolReference): string {
            $location = 'code';

            if ($symbolReference->location()->isDocBlockType()) {
                $location = 'doc-block-type';
            } elseif ($symbolReference->location()->isDocBlockText()) {
                $location = 'doc-block-text';
            }

            $fullyQualifiedName = 'null';

            if ($symbolReference->fullyQualifiedName() instanceof Rules\Files\FullyQualifiedName) {
                $fullyQualifiedName = $symbolReference->fullyQualifiedName()->toString();
            }

            return \sprintf(
                '%s %s %s -> %s',
                $location,
                self::describeSymbolKind($symbolReference->symbolKind()),
                $symbolReference->spelling()->toString(),
                $fullyQualifiedName,
            );
        }, $file->symbolReferences()->toArray());
    }

    private static function describeSymbolKind(Rules\Files\SymbolKind $symbolKind): string
    {
        if ($symbolKind->isFunction()) {
            return 'function';
        }

        if ($symbolKind->isConstant()) {
            return 'constant';
        }

        return 'class-like';
    }
}
