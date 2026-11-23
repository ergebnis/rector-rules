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
 * @covers \Ergebnis\Rector\Rules\Files\NamespacePrefixDiscoverer
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\File
 * @uses \Ergebnis\Rector\Rules\Files\FileNamespace
 * @uses \Ergebnis\Rector\Rules\Files\FileReader
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\Import
 * @uses \Ergebnis\Rector\Rules\Files\Imports
 * @uses \Ergebnis\Rector\Rules\Files\LocalName
 * @uses \Ergebnis\Rector\Rules\Files\NameResolver
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefix
 * @uses \Ergebnis\Rector\Rules\Files\NamespacePrefixes
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegment
 * @uses \Ergebnis\Rector\Rules\Files\NamespaceSegments
 * @uses \Ergebnis\Rector\Rules\Files\Spelling
 * @uses \Ergebnis\Rector\Rules\Files\SymbolKind
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReference
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferenceLocation
 * @uses \Ergebnis\Rector\Rules\Files\SymbolReferences
 */
final class NamespacePrefixDiscovererTest extends AbstractFileTestCase
{
    public function testDiscoverParentNamespacePrefixesReturnsFirstSegmentsExceptConfiguredOnes(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App\Mail;

            use Psr\Log\LoggerInterface;
            use Smarty;

            /**
             * @see \Twig\Environment
             */
            final class Notifier extends \Symfony\Component\Mailer\Mailer
            {
            }
            PHP);

        $namespacePrefixDiscoverer = new Rules\Files\NamespacePrefixDiscoverer();

        $parentNamespacePrefixes = $namespacePrefixDiscoverer->discoverParentNamespacePrefixes(
            $file,
            Rules\Files\NamespacePrefixes::create(Rules\Files\NamespacePrefix::fromString('Symfony\Component')),
            Rules\Files\NamespacePrefixes::create(Rules\Files\NamespacePrefix::fromString('Psr\Log')),
        );

        $expected = [
            'App',
            'Twig',
        ];

        self::assertSame($expected, self::toStrings($parentNamespacePrefixes));
    }

    public function testDiscoverNamespacePrefixesReturnsEmptyArrayWhenThereAreNoParentNamespacePrefixes(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App;

            use Symfony\Component\Mailer\MailerInterface;
            PHP);

        $namespacePrefixDiscoverer = new Rules\Files\NamespacePrefixDiscoverer();

        $namespacePrefixes = $namespacePrefixDiscoverer->discoverNamespacePrefixes(
            $file,
            Rules\Files\NamespacePrefixes::create(),
            Rules\Files\NamespacePrefixes::create(),
        );

        self::assertTrue($namespacePrefixes->isEmpty());
    }

    public function testDiscoverNamespacePrefixesReturnsNamespacesBelowParentNamespacePrefixesExceptConfiguredOnes(): void
    {
        $file = $this->read(<<<'PHP'
            <?php

            namespace App;

            use Symfony\Component;
            use Symfony\Component\Mailer\MailerInterface;

            final class Notifier
            {
                /**
                 * @var Component\HttpFoundation\Request
                 * @see \Symfony\Component\Routing\Router
                 * @see Console\Application
                 */
                private $request;

                public function __construct(\Symfony\Component\Clock\ClockInterface $clock)
                {
                    \Symfony\Component\String\u('');
                }
            }
            PHP);

        $namespacePrefixDiscoverer = new Rules\Files\NamespacePrefixDiscoverer();

        $namespacePrefixes = $namespacePrefixDiscoverer->discoverNamespacePrefixes(
            $file,
            Rules\Files\NamespacePrefixes::create(Rules\Files\NamespacePrefix::fromString('Symfony\Component')),
            Rules\Files\NamespacePrefixes::create(Rules\Files\NamespacePrefix::fromString('Symfony\Component\Clock')),
        );

        $expected = [
            'Symfony\Component\Mailer',
            'Symfony\Component\String',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\Routing',
        ];

        self::assertSame($expected, self::toStrings($namespacePrefixes));
    }

    /**
     * @return list<string>
     */
    private static function toStrings(Rules\Files\NamespacePrefixes $namespacePrefixes): array
    {
        return \array_map(static function (Rules\Files\NamespacePrefix $namespacePrefix): string {
            return $namespacePrefix->toString();
        }, $namespacePrefixes->toArray());
    }
}
