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
     * @var \Closure(string, string): int
     */
    private \Closure $comparator;

    public function __construct()
    {
        $this->configure([
            self::CONFIGURATION_KEY_COMPARISON_FUNCTION => 'strcasecmp',
            self::CONFIGURATION_KEY_DIRECTION => 'asc',
        ]);
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

        $comparator = $this->comparator;

        \usort($items, static function (Node\Expr\ArrayItem $a, Node\Expr\ArrayItem $b) use ($comparator): int {
            if (!$a->key instanceof Node\Scalar\String_) {
                throw new \RuntimeException('This should not happen.');
            }

            if (!$b->key instanceof Node\Scalar\String_) {
                throw new \RuntimeException('This should not happen.');
            }

            return $comparator(
                $a->key->value,
                $b->key->value,
            );
        });

        $node->items = $items;

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

        if ('desc' === $direction) {
            $this->comparator = static function (string $a, string $b) use ($comparisonFunction): int {
                return -1 * ($comparisonFunction)($a, $b);
            };

            return;
        }

        $this->comparator = static function (string $a, string $b) use ($comparisonFunction): int {
            return ($comparisonFunction)($a, $b);
        };
    }
}
