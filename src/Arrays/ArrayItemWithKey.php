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

namespace Ergebnis\Rector\Rules\Arrays;

use PhpParser\Node;

/**
 * @internal
 */
final class ArrayItemWithKey
{
    private Node\Expr\ArrayItem $arrayItem;
    private Key $key;

    private function __construct(
        Node\Expr\ArrayItem $arrayItem,
        Key $key
    ) {
        $this->arrayItem = $arrayItem;
        $this->key = $key;
    }

    public static function create(
        Node\Expr\ArrayItem $arrayItem,
        Key $key
    ): self {
        return new self(
            $arrayItem,
            $key,
        );
    }

    public function arrayItem(): Node\Expr\ArrayItem
    {
        return $this->arrayItem;
    }

    public function key(): Key
    {
        return $this->key;
    }
}
