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
use Rector\Contract;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class SortAssociativeArrayByKeyRector extends Rector\AbstractRector implements Contract\Rector\ConfigurableRectorInterface
{
    private const COMPARISON_FUNCTIONS_TO_DOCUMENTATION_URL = [
        'strcasecmp' => 'https://www.php.net/manual/en/function.strcasecmp.php',
        'strcmp' => 'https://www.php.net/manual/en/function.strcmp.php',
        'strnatcasecmp' => 'https://www.php.net/manual/en/function.strnatcasecmp.php',
        'strnatcmp' => 'https://www.php.net/manual/en/function.strnatcmp.php',
    ];
    private const DIRECTION_TO_MULTIPLIER = [
        'asc' => 1,
        'desc' => -1,
    ];
    private const CONFIGURATION_KEY_COMPARISON_FUNCTION = 'comparison_function';
    private const CONFIGURATION_KEY_DIRECTION = 'direction';

    /**
     * @var \Closure(Key, Key): int
     */
    private \Closure $comparator;

    public function __construct()
    {
        $this->configure([]);
    }

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
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
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
CODE_SAMPLE,
                    [
                        self::CONFIGURATION_KEY_DIRECTION => 'desc',
                    ],
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

        /** @var list<ArrayItemWithKey> $arrayItemsWithKeys */
        $arrayItemsWithKeys = \array_reduce(
            $node->items,
            static function (array $arrayItemsWithKeys, $arrayItem): array {
                if (!$arrayItem instanceof Node\Expr\ArrayItem) {
                    return $arrayItemsWithKeys;
                }

                $arrayItemWithKey = self::arrayItemWithKeyFrom($arrayItem);

                if (!$arrayItemWithKey instanceof ArrayItemWithKey) {
                    return $arrayItemsWithKeys;
                }

                $arrayItemsWithKeys[] = $arrayItemWithKey;

                return $arrayItemsWithKeys;
            },
            [],
        );

        if (\count($node->items) !== \count($arrayItemsWithKeys)) {
            return null;
        }

        $comparator = $this->comparator;

        \usort($arrayItemsWithKeys, static function (ArrayItemWithKey $a, ArrayItemWithKey $b) use ($comparator): int {
            return $comparator(
                $a->key(),
                $b->key(),
            );
        });

        $node->items = \array_map(static function (ArrayItemWithKey $arrayItemWithKey): Node\Expr\ArrayItem {
            return $arrayItemWithKey->arrayItem();
        }, $arrayItemsWithKeys);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $comparisonFunction = 'strcmp';

        if (\array_key_exists(self::CONFIGURATION_KEY_COMPARISON_FUNCTION, $configuration)) {
            if (!\is_string($configuration[self::CONFIGURATION_KEY_COMPARISON_FUNCTION])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be one of "%s".',
                    self::CONFIGURATION_KEY_COMPARISON_FUNCTION,
                    \implode('", "', \array_keys(self::COMPARISON_FUNCTIONS_TO_DOCUMENTATION_URL)),
                ));
            }

            if (!\array_key_exists($configuration[self::CONFIGURATION_KEY_COMPARISON_FUNCTION], self::COMPARISON_FUNCTIONS_TO_DOCUMENTATION_URL)) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be one of "%s", got "%s" instead.',
                    self::CONFIGURATION_KEY_COMPARISON_FUNCTION,
                    \implode('", "', \array_keys(self::COMPARISON_FUNCTIONS_TO_DOCUMENTATION_URL)),
                    $configuration[self::CONFIGURATION_KEY_COMPARISON_FUNCTION],
                ));
            }

            $comparisonFunction = $configuration[self::CONFIGURATION_KEY_COMPARISON_FUNCTION];
        }

        $direction = 'asc';

        if (\array_key_exists(self::CONFIGURATION_KEY_DIRECTION, $configuration)) {
            if (!\is_string($configuration[self::CONFIGURATION_KEY_DIRECTION])) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be one of "%s".',
                    self::CONFIGURATION_KEY_DIRECTION,
                    \implode('", "', \array_keys(self::DIRECTION_TO_MULTIPLIER)),
                ));
            }

            if (!\array_key_exists($configuration[self::CONFIGURATION_KEY_DIRECTION], self::DIRECTION_TO_MULTIPLIER)) {
                throw new \InvalidArgumentException(\sprintf(
                    'Value for configuration option "%s" needs to be one of "%s", got "%s" instead.',
                    self::CONFIGURATION_KEY_DIRECTION,
                    \implode('", "', \array_keys(self::DIRECTION_TO_MULTIPLIER)),
                    $configuration[self::CONFIGURATION_KEY_DIRECTION],
                ));
            }

            $direction = $configuration[self::CONFIGURATION_KEY_DIRECTION];
        }

        $multiplier = self::DIRECTION_TO_MULTIPLIER[$direction];

        $this->comparator = static function (Key $a, Key $b) use ($comparisonFunction, $multiplier): int {
            return $multiplier * ($comparisonFunction)(
                $a->toString(),
                $b->toString()
            );
        };
    }

    private static function arrayItemWithKeyFrom(Node\Expr\ArrayItem $arrayItem): ?ArrayItemWithKey
    {
        if (
            $arrayItem->key instanceof Node\Expr\ClassConstFetch
            && $arrayItem->key->name instanceof Node\Identifier
            && $arrayItem->key->name->toString() === 'class'
            && $arrayItem->key->class instanceof Node\Name
        ) {
            return ArrayItemWithKey::create(
                $arrayItem,
                Key::fromString($arrayItem->key->class->toString()),
            );
        }

        if ($arrayItem->key instanceof Node\Scalar\String_) {
            return ArrayItemWithKey::create(
                $arrayItem,
                Key::fromString($arrayItem->key->value),
            );
        }

        return null;
    }
}
