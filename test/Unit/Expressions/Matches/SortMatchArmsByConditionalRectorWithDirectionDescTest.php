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

namespace Ergebnis\Rector\Rules\Test\Unit\Expressions\Matches;

use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Expressions\Matches\SortMatchArmsByConditionalRector
 *
 * @uses \Ergebnis\Rector\Rules\Configuration\Configuration
 * @uses \Ergebnis\Rector\Rules\Configuration\Option
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionDescription
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionName
 * @uses \Ergebnis\Rector\Rules\Configuration\Options
 * @uses \Ergebnis\Rector\Rules\Configuration\OptionValue
 * @uses \Ergebnis\Rector\Rules\Expressions\Matches\IntConditional
 * @uses \Ergebnis\Rector\Rules\Expressions\Matches\MatchArmWithConditional
 * @uses \Ergebnis\Rector\Rules\Expressions\Matches\StringConditional
 */
final class SortMatchArmsByConditionalRectorWithDirectionDescTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideData
     *
     * @requires PHP >= 8.0
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../../Fixture/Expressions/Matches/SortMatchArmsByConditionalRector/WithDirectionDesc');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../../Fixture/Expressions/Matches/SortMatchArmsByConditionalRector/WithDirectionDesc/config.php';
    }
}
