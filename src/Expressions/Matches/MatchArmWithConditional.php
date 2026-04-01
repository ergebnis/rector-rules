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

namespace Ergebnis\Rector\Rules\Expressions\Matches;

use PhpParser\Node;

/**
 * @internal
 */
final class MatchArmWithConditional
{
    private Node\MatchArm $arm;
    private Conditional $conditional;

    private function __construct(
        Node\MatchArm $arm,
        Conditional $conditional
    ) {
        $this->arm = $arm;
        $this->conditional = $conditional;
    }

    public static function create(
        Node\MatchArm $arm,
        Conditional $conditional
    ): self {
        return new self(
            $arm,
            $conditional,
        );
    }

    public function arm(): Node\MatchArm
    {
        return $this->arm;
    }

    public function conditional(): Conditional
    {
        return $this->conditional;
    }
}
