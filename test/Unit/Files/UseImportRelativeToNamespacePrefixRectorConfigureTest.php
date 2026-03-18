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
use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Files\UseImportRelativeToNamespacePrefixRector
 */
final class UseImportRelativeToNamespacePrefixRectorConfigureTest extends Testing\PHPUnit\AbstractLazyTestCase
{
    public function testConfigureRejectsUnknownKeys(): void
    {
        $rector = $this->make(Rules\Files\UseImportRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration contains unknown keys: "foo".');

        $rector->configure([
            'foo' => 'bar',
        ]);
    }

    public function testConfigureRejectsNamespacePrefixesWhenValueIsNotList(): void
    {
        $rector = $this->make(Rules\Files\UseImportRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "namespacePrefixes" needs to be a list of strings.');

        $rector->configure([
            'namespacePrefixes' => 'not-an-array',
        ]);
    }

    /**
     * @dataProvider provideNamespacePrefixesWhereValueIsNotAListOfStrings
     */
    public function testConfigureRejectsNamespacePrefixesWhenValueIsNotAListOfStrings(array $value): void
    {
        $rector = $this->make(Rules\Files\UseImportRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "namespacePrefixes" needs to be a list of strings.');

        $rector->configure([
            'namespacePrefixes' => $value,
        ]);
    }

    /**
     * @return \Generator<string, array{0: mixed}>
     */
    public static function provideNamespacePrefixesWhereValueIsNotAListOfStrings(): iterable
    {
        $values = [
            'associative-array' => [
                'foo' => 'Example\Core',
            ],
            'list-with-non-string-item' => [
                123,
            ],
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    public function testConfigureRejectsNamespacePrefixesWhenValueIsNotAListOfValidNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\UseImportRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "namespacePrefixes" needs to be a list of strings where each string is a valid namespace with at least two segments, got "SingleSegment".');

        $rector->configure([
            'namespacePrefixes' => [
                'SingleSegment',
            ],
        ]);
    }

    public function testConfigureRejectsNamespacePrefixesWhenValueIsAListWithDuplicateNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\UseImportRelativeToNamespacePrefixRector::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value for configuration option "namespacePrefixes" needs to be a list of unique strings, got duplicate "Example\Core".');

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
                'Example\Core',
            ],
        ]);
    }

    public function testConfigureAcceptsNamespacePrefixesWhenItIsAListOfValidNamespacePrefixes(): void
    {
        $rector = $this->make(Rules\Files\UseImportRelativeToNamespacePrefixRector::class);

        $rector->configure([
            'namespacePrefixes' => [
                'Example\Core',
                'Example\Domain',
            ],
        ]);

        $this->addToAssertionCount(1);
    }

    public function testConfigureAcceptsNamespacePrefixesWhenItIsAnEmptyList(): void
    {
        $rector = $this->make(Rules\Files\UseImportRelativeToNamespacePrefixRector::class);

        $rector->configure([]);

        $this->addToAssertionCount(1);
    }
}
