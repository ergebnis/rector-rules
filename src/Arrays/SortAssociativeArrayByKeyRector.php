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

namespace Ergebnis\Rector\Rules\Arrays;

use PhpParser\Node;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class SortAssociativeArrayByKeyRector extends Rector\AbstractRector
{
    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Sort associative arrays by key.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
$data = [
    'foo' => [
        'foo',
        'bar',
        'baz',
    ],
    'bar' => [
        'quz' => 'qux',
        'quux' => 'quuz',
    ],
];
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$data = [
    'bar' => [
        'quux' => 'quuz',
        'quz' => 'qux',
    ],
    'foo' => [
        'foo',
        'bar',
        'baz',
    ],
];
CODE_SAMPLE
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Expr\Array_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof Node\Expr\Array_) {
            return null;
        }

        /** @var array<int, Node\Expr\ArrayItem> $items */
        $items = \array_filter($node->items, static function ($item): bool {
            if (!$item instanceof Node\Expr\ArrayItem) {
                return false;
            }

            if (!$item->key instanceof Node\Scalar\String_) {
                return false;
            }

            return true;
        });

        if ($items !== $node->items) {
            return null;
        }

        \usort($items, static function (Node\Expr\ArrayItem $a, Node\Expr\ArrayItem $b): int {
            if (!$a->key instanceof Node\Scalar\String_) {
                throw new \RuntimeException('This should not happen.');
            }

            if (!$b->key instanceof Node\Scalar\String_) {
                throw new \RuntimeException('This should not happen.');
            }

            return \strcmp(
                $a->key->value,
                $b->key->value,
            );
        });

        $node->items = $items;

        return $node;
    }
}
