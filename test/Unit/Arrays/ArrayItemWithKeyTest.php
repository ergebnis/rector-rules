<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/rector-rules
 */

namespace Arrays;

use Ergebnis\Rector\Rules;
use PhpParser\Node;
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Arrays\ArrayItemWithKey
 */
final class ArrayItemWithKeyTest extends Framework\TestCase
{
    public function testCreateReturnsNodeWithKey(): void
    {
        $arrayItem = new Node\Expr\ArrayItem(
            new Node\Scalar\String_('foo'),
            new Node\Scalar\String_('bar'),
        );

        $key = Rules\Arrays\Key::fromString('baz');

        $arrayItemWithKey = Rules\Arrays\ArrayItemWithKey::create(
            $arrayItem,
            $key,
        );

        self::assertSame($arrayItem, $arrayItemWithKey->arrayItem());
        self::assertSame($key, $arrayItemWithKey->key());
    }
}
