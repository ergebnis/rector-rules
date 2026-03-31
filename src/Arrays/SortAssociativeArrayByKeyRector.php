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

use Ergebnis\Rector\Rules;
use PhpParser\Node;
use Rector\Contract;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class SortAssociativeArrayByKeyRector extends Rector\AbstractRector implements
    Contract\Rector\ConfigurableRectorInterface,
    Rules\Configuration\HasConfigurationOptions
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

    public function configurationOptions(): Rules\Configuration\Options
    {
        return Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_COMPARISON_FUNCTION),
                Rules\Configuration\OptionDescription::fromString('The comparison function to use for sorting keys.'),
                Rules\Configuration\OptionValue::oneOf(
                    \array_keys(self::COMPARISON_FUNCTIONS_TO_DOCUMENTATION_URL),
                    'strcmp',
                ),
            ),
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_DIRECTION),
                Rules\Configuration\OptionDescription::fromString('The sorting direction.'),
                Rules\Configuration\OptionValue::oneOf(
                    \array_keys(self::DIRECTION_TO_MULTIPLIER),
                    'asc',
                ),
            ),
        );
    }

    public function configure(array $configuration): void
    {
        $resolvedConfiguration = $this->configurationOptions()->resolveConfigurationFrom($configuration);

        /** @var callable(string, string): int $comparisonFunction */
        $comparisonFunction = $resolvedConfiguration->get(Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_COMPARISON_FUNCTION));

        /** @var string $direction */
        $direction = $resolvedConfiguration->get(Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_DIRECTION));

        $multiplier = self::DIRECTION_TO_MULTIPLIER[$direction];

        $this->comparator = static function (Rules\Arrays\Key $a, Rules\Arrays\Key $b) use ($comparisonFunction, $multiplier): int {
            return $multiplier * ($comparisonFunction)(
                $a->toString(),
                $b->toString()
            );
        };
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Sorts associative arrays by key.',
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
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$data = [
    'Quz' => 'qux',
    'QuZ' => 'qux',
    'quz' => 'qux',
    'Quux' => 'quuz',
    'quux' => 'quuz',
];
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$data = [
    'Quux' => 'quuz',
    'quux' => 'quuz',
    'Quz' => 'qux',
    'QuZ' => 'qux',
    'quz' => 'qux',
];
CODE_SAMPLE,
                    [
                        self::CONFIGURATION_KEY_COMPARISON_FUNCTION => 'strcasecmp',
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$data = [
    'Quz10' => 'qux',
    'Quz2' => 'qux',
    'Quz' => 'qux',
    'Quux' => 'quuz',
];
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$data = [
    'Quux' => 'quuz',
    'Quz' => 'qux',
    'Quz2' => 'qux',
    'Quz10' => 'qux',
];
CODE_SAMPLE,
                    [
                        self::CONFIGURATION_KEY_COMPARISON_FUNCTION => 'strnatcmp',
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$data = [
    'Quz10' => 'qux',
    'Quz2' => 'qux',
    'Quz' => 'qux',
    'QuZ' => 'qux',
    'quz' => 'qux',
    'Quux' => 'quuz',
    'quux' => 'quuz',
];
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$data = [
    'Quux' => 'quuz',
    'quux' => 'quuz',
    'Quz' => 'qux',
    'QuZ' => 'qux',
    'quz' => 'qux',
    'Quz2' => 'qux',
    'Quz10' => 'qux',
];
CODE_SAMPLE,
                    [
                        self::CONFIGURATION_KEY_COMPARISON_FUNCTION => 'strnatcasecmp',
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
                $arrayItemWithKey = self::arrayItemWithKeyFrom($arrayItem);

                if (!$arrayItemWithKey instanceof Rules\Arrays\ArrayItemWithKey) {
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

        \usort($arrayItemsWithKeys, static function (Rules\Arrays\ArrayItemWithKey $a, Rules\Arrays\ArrayItemWithKey $b) use ($comparator): int {
            return $comparator(
                $a->key(),
                $b->key(),
            );
        });

        $node->items = \array_map(static function (Rules\Arrays\ArrayItemWithKey $arrayItemWithKey): Node\Expr\ArrayItem {
            return $arrayItemWithKey->arrayItem();
        }, $arrayItemsWithKeys);

        return $node;
    }

    private static function arrayItemWithKeyFrom(Node\Expr\ArrayItem $arrayItem): ?Rules\Arrays\ArrayItemWithKey
    {
        $key = $arrayItem->key;

        if (
            $key instanceof Node\Expr\ClassConstFetch
            && $key->name instanceof Node\Identifier
            && $key->name->toString() === 'class'
            && $key->class instanceof Node\Name
        ) {
            $name = $key->class;

            if ($name->hasAttribute('originalName')) {
                $originalName = $name->getAttribute('originalName');

                if ($originalName instanceof Node\Name) {
                    $name = $originalName;
                }
            }

            return Rules\Arrays\ArrayItemWithKey::create(
                $arrayItem,
                Rules\Arrays\Key::fromString($name->toString()),
            );
        }

        if ($key instanceof Node\Scalar\String_) {
            return Rules\Arrays\ArrayItemWithKey::create(
                $arrayItem,
                Rules\Arrays\Key::fromString($key->value),
            );
        }

        return null;
    }
}
