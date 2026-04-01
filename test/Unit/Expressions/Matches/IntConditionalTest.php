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

use Ergebnis\Rector\Rules;
use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Expressions\Matches\IntConditional
 */
final class IntConditionalTest extends Testing\PHPUnit\AbstractLazyTestCase
{
    public function testFromIntReturnsIntConditional(): void
    {
        $value = 9000;

        $intConditional = Rules\Expressions\Matches\IntConditional::fromInt($value);

        self::assertSame($value, $intConditional->toInt());
    }
}
