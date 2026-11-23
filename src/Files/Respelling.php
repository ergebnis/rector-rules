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

namespace Ergebnis\Rector\Rules\Files;

/**
 * @internal
 */
final class Respelling
{
    private SymbolReference $symbolReference;
    private Spelling $spelling;

    private function __construct(
        SymbolReference $symbolReference,
        Spelling $spelling
    ) {
        $this->symbolReference = $symbolReference;
        $this->spelling = $spelling;
    }

    public static function create(
        SymbolReference $symbolReference,
        Spelling $spelling
    ): self {
        return new self(
            $symbolReference,
            $spelling,
        );
    }

    public function symbolReference(): SymbolReference
    {
        return $this->symbolReference;
    }

    public function spelling(): Spelling
    {
        return $this->spelling;
    }
}
