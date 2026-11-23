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
 * @covers \Ergebnis\Rector\Rules\Files\NamespacePrefixPlan
 * @covers \Ergebnis\Rector\Rules\Files\NamespacePrefixPlanner
 * @covers \Ergebnis\Rector\Rules\Files\Respelling
 * @covers \Ergebnis\Rector\Rules\Files\Respellings
 *
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeName
 * @uses \Ergebnis\Rector\Rules\Files\ClassLikeNames
 * @uses \Ergebnis\Rector\Rules\Files\File
 * @uses \Ergebnis\Rector\Rules\Files\FileNamespace
 * @uses \Ergebnis\Rector\Rules\Files\FileReader
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedName
 * @uses \Ergebnis\Rector\Rules\Files\FullyQualifiedNames
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
final class NamespacePrefixPlannerTest extends AbstractFileTestCase
{
    public function testPlanReturnsNullWhenLastNamespaceSegmentOfNamespacePrefixIsReservedName(): void
    {
        $namespacePrefixPlan = $this->plan(
            <<<'PHP'
                <?php

                namespace App;

                final class Slugger
                {
                    public function __construct(\Symfony\Component\String\Slugger\SluggerInterface $slugger)
                    {
                    }
                }
                PHP,
            'Symfony\Component\String',
            true,
        );

        self::assertNull($namespacePrefixPlan);
    }

    public function testPlanReturnsNullWhenFileDoesNotReferenceNamespacePrefix(): void
    {
        $namespacePrefixPlan = $this->plan(
            <<<'PHP'
                <?php

                namespace App;

                final class Notifier
                {
                    public function __construct(\Psr\Log\LoggerInterface $logger)
                    {
                    }
                }
                PHP,
            'Symfony\Component\Mailer',
            true,
        );

        self::assertNull($namespacePrefixPlan);
    }

    public function testPlanReturnsNullWhenImportWithLocalNameCollidesWithLastNamespaceSegmentOfNamespacePrefix(): void
    {
        $namespacePrefixPlan = $this->plan(
            <<<'PHP'
                <?php

                namespace App;

                use Vendor\Templating as Smarty;

                final class View
                {
                    public function __construct(\Smarty\Smarty $smarty, Smarty\Engine $engine)
                    {
                    }
                }
                PHP,
            'Smarty\Smarty',
            true,
        );

        self::assertNull($namespacePrefixPlan);
    }

    public function testPlanReturnsNullWhenNameInDocBlockTextWouldResolveDifferently(): void
    {
        $namespacePrefixPlan = $this->plan(
            <<<'PHP'
                <?php

                namespace App;

                use Smarty;

                final class View
                {
                    /**
                     * @see Smarty\Exception
                     */
                    public function __construct(Smarty\Smarty $smarty)
                    {
                    }
                }
                PHP,
            'Smarty\Smarty',
            true,
        );

        self::assertNull($namespacePrefixPlan);
    }

    public function testPlanReturnsPlanThatReplacesImportOfParentNamespaceNamedLikeLastNamespaceSegment(): void
    {
        $namespacePrefixPlan = $this->plan(
            <<<'PHP'
                <?php

                namespace App;

                use Smarty;

                final class View
                {
                    public function __construct(Smarty\Smarty $smarty, Smarty\Smarty\Compiler\Template $template)
                    {
                    }
                }
                PHP,
            'Smarty\Smarty',
            true,
        );

        self::assertInstanceOf(Rules\Files\NamespacePrefixPlan::class, $namespacePrefixPlan);

        $expectedRespellings = [
            'Smarty\Smarty -> Smarty',
            'Smarty\Smarty\Compiler\Template -> Smarty\Compiler\Template',
        ];

        self::assertSame($expectedRespellings, self::describeRespellings($namespacePrefixPlan));
        self::assertFalse($namespacePrefixPlan->hasNamespacePrefixImport());
        self::assertTrue($namespacePrefixPlan->requiresNamespacePrefixImport());

        $replacedImport = $namespacePrefixPlan->replacedImport();

        self::assertInstanceOf(Rules\Files\Import::class, $replacedImport);
        self::assertSame('Smarty', $replacedImport->fullyQualifiedName()->toString());
    }

    public function testPlanReturnsPlanWithNamesRelativeToNamespaceOfFileWhenReferencesAreNotForcedToBeRelativeToNamespacePrefix(): void
    {
        $namespacePrefixPlan = $this->plan(
            <<<'PHP'
                <?php

                namespace App\Controller\User;

                final class ListAction
                {
                    public function __construct(\App\Controller\User\FindAction $findAction, \App\Controller\DashboardAction $dashboardAction)
                    {
                    }
                }
                PHP,
            'App\Controller',
            false,
        );

        self::assertInstanceOf(Rules\Files\NamespacePrefixPlan::class, $namespacePrefixPlan);

        $expectedRespellings = [
            '\App\Controller\User\FindAction -> FindAction',
            '\App\Controller\DashboardAction -> Controller\DashboardAction',
        ];

        self::assertSame($expectedRespellings, self::describeRespellings($namespacePrefixPlan));
        self::assertTrue($namespacePrefixPlan->requiresNamespacePrefixImport());
        self::assertNull($namespacePrefixPlan->replacedImport());
    }

    private function plan(
        string $code,
        string $namespacePrefix,
        bool $forceRelativeReferences
    ): ?Rules\Files\NamespacePrefixPlan {
        $file = $this->read($code);

        $namespacePrefixPlanner = new Rules\Files\NamespacePrefixPlanner(new Rules\Files\NameResolver());

        return $namespacePrefixPlanner->plan(
            $file,
            Rules\Files\NamespacePrefix::fromString($namespacePrefix),
            Rules\Files\NamespacePrefixes::create(),
            $file->symbolReferences()->fullyQualifiedClassLikeNamesInCode(),
            $forceRelativeReferences,
        );
    }

    /**
     * @return list<string>
     */
    private static function describeRespellings(Rules\Files\NamespacePrefixPlan $namespacePrefixPlan): array
    {
        return \array_map(static function (Rules\Files\Respelling $respelling): string {
            return \sprintf(
                '%s -> %s',
                $respelling->symbolReference()->spelling()->toString(),
                $respelling->spelling()->toString(),
            );
        }, $namespacePrefixPlan->respellings()->toArray());
    }
}
