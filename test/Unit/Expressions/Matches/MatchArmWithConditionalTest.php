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
use PhpParser\Node;
use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Expressions\Matches\MatchArmWithConditional
 *
 * @uses \Ergebnis\Rector\Rules\Expressions\Matches\Conditional
 */
final class MatchArmWithConditionalTest extends Testing\PHPUnit\AbstractLazyTestCase
{
    public function testCreateReturnsMatchArmWithConditional(): void
    {
        $matchArm = self::createStub(Node\MatchArm::class);
        $conditional = self::createStub(Rules\Expressions\Matches\Conditional::class);

        $matchArmWithConditional = Rules\Expressions\Matches\MatchArmWithConditional::create(
            $matchArm,
            $conditional,
        );

        self::assertSame($matchArm, $matchArmWithConditional->arm());
        self::assertSame($conditional, $matchArmWithConditional->conditional());
    }
}
