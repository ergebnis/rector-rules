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

use Ergebnis\Rector\Rules;
use PhpParser\Node;
use Rector\Contract;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class SortMatchArmsByConditionalRector extends Rector\AbstractRector implements
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
     * @var \Closure(IntConditional, IntConditional): int
     */
    private \Closure $intConditionalComparator;

    /**
     * @var \Closure(StringConditional, StringConditional): int
     */
    private \Closure $stringConditionalComparator;
    private int $multiplier;

    public function __construct()
    {
        $this->configure([]);
    }

    public function configurationOptions(): Rules\Configuration\Options
    {
        return Rules\Configuration\Options::create(
            Rules\Configuration\Option::create(
                Rules\Configuration\OptionName::fromString(self::CONFIGURATION_KEY_COMPARISON_FUNCTION),
                Rules\Configuration\OptionDescription::fromString('The comparison function to use for sorting conditionals when conditionals are strings.'),
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

        $this->multiplier = self::DIRECTION_TO_MULTIPLIER[$direction];

        $this->intConditionalComparator = static function (Rules\Expressions\Matches\IntConditional $a, Rules\Expressions\Matches\IntConditional $b): int {
            return $a->toInt() <=> $b->toInt();
        };

        $this->stringConditionalComparator = static function (Rules\Expressions\Matches\StringConditional $a, Rules\Expressions\Matches\StringConditional $b) use ($comparisonFunction): int {
            return $comparisonFunction(
                $a->toString(),
                $b->toString(),
            );
        };
    }

    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Sorts match arms by conditional when the conditionals are all integers or all strings.',
            [
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
match ($status) {
    'pending' => handlePending(),
    'active' => handleActive(),
    'closed' => handleClosed(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match ($status) {
    'active' => handleActive(),
    'closed' => handleClosed(),
    'pending' => handlePending(),
};
CODE_SAMPLE
                ),
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
match ($status) {
    'pending' => handlePending(),
    default => handleUnknown(),
    'active' => handleActive(),
    'closed' => handleClosed(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match ($status) {
    'active' => handleActive(),
    'closed' => handleClosed(),
    'pending' => handlePending(),
    default => handleUnknown(),
};
CODE_SAMPLE
                ),
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
match (true) {
    Zebra::class => handleZebra(),
    Apple::class => handleApple(),
    Mango::class => handleMango(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match (true) {
    Apple::class => handleApple(),
    Mango::class => handleMango(),
    Zebra::class => handleZebra(),
};
CODE_SAMPLE
                ),
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
match (true) {
    Zebra::class => handleZebra(),
    default => handleUnknown(),
    Apple::class => handleApple(),
    Mango::class => handleMango(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match (true) {
    Apple::class => handleApple(),
    Mango::class => handleMango(),
    Zebra::class => handleZebra(),
    default => handleUnknown(),
};
CODE_SAMPLE
                ),
                new RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
match ($code) {
    404 => 'Not Found',
    default => 'Unknown',
    200 => 'OK',
    500 => 'Server Error',
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match ($code) {
    200 => 'OK',
    404 => 'Not Found',
    500 => 'Server Error',
    default => 'Unknown',
};
CODE_SAMPLE
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
match ($status) {
    'active' => handleActive(),
    'pending' => handlePending(),
    'closed' => handleClosed(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match ($status) {
    'pending' => handlePending(),
    'closed' => handleClosed(),
    'active' => handleActive(),
};
CODE_SAMPLE,
                    [
                        self::CONFIGURATION_KEY_DIRECTION => 'desc',
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
match ($status) {
    'Pending' => handlePending(),
    'active' => handleActive(),
    'Closed' => handleClosed(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match ($status) {
    'active' => handleActive(),
    'Closed' => handleClosed(),
    'Pending' => handlePending(),
};
CODE_SAMPLE,
                    [
                        self::CONFIGURATION_KEY_COMPARISON_FUNCTION => 'strcasecmp',
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
match ($status) {
    'Status10' => handle10(),
    'Status2' => handle2(),
    'Status' => handleBase(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match ($status) {
    'Status' => handleBase(),
    'Status2' => handle2(),
    'Status10' => handle10(),
};
CODE_SAMPLE,
                    [
                        self::CONFIGURATION_KEY_COMPARISON_FUNCTION => 'strnatcmp',
                    ],
                ),
                new RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
match ($status) {
    'Status10' => handle10(),
    'status2' => handle2(),
    'Status' => handleBase(),
};
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
match ($status) {
    'Status' => handleBase(),
    'status2' => handle2(),
    'Status10' => handle10(),
};
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
            Node\Expr\Match_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof Node\Expr\Match_) {
            return null;
        }

        if (\count($node->arms) === 0) {
            return null;
        }

        $defaultArm = null;

        /** @var list<MatchArmWithConditional> $matchArmsWithConditionals */
        $matchArmsWithConditionals = [];

        foreach ($node->arms as $arm) {
            if (null === $arm->conds) {
                $defaultArm = $arm;

                continue;
            }

            if (\count($arm->conds) !== 1) {
                return null;
            }

            $conditional = self::conditionalFrom($arm->conds[0]);

            if (!$conditional instanceof Rules\Expressions\Matches\Conditional) {
                return null;
            }

            $matchArmsWithConditionals[] = Rules\Expressions\Matches\MatchArmWithConditional::create(
                $arm,
                $conditional,
            );
        }

        if ([] === $matchArmsWithConditionals) {
            return null;
        }

        $conditionalComparator = $this->conditionalComparatorFor(...$matchArmsWithConditionals);

        if (!$conditionalComparator instanceof \Closure) {
            return null;
        }

        $multiplier = $this->multiplier;

        \usort($matchArmsWithConditionals, static function (Rules\Expressions\Matches\MatchArmWithConditional $a, Rules\Expressions\Matches\MatchArmWithConditional $b) use ($conditionalComparator, $multiplier): int {
            /** @var int $result */
            $result = $conditionalComparator(
                $a->conditional(),
                $b->conditional(),
            );

            return $multiplier * $result;
        });

        $sortedArms = \array_map(static function (Rules\Expressions\Matches\MatchArmWithConditional $armWithConditional): Node\MatchArm {
            return $armWithConditional->arm();
        }, $matchArmsWithConditionals);

        if ($defaultArm instanceof Node\MatchArm) {
            $sortedArms[] = $defaultArm;
        }

        $node->arms = $sortedArms;

        return $node;
    }

    private static function conditionalFrom(Node\Expr $expr): ?Rules\Expressions\Matches\Conditional
    {
        if ($expr instanceof Node\Scalar\Int_) {
            return Rules\Expressions\Matches\IntConditional::fromInt($expr->value);
        }

        if ($expr instanceof Node\Scalar\String_) {
            return Rules\Expressions\Matches\StringConditional::fromString($expr->value);
        }

        if (
            $expr instanceof Node\Expr\ClassConstFetch
            && $expr->name instanceof Node\Identifier
            && $expr->name->toString() === 'class'
            && $expr->class instanceof Node\Name
        ) {
            $name = $expr->class;

            if ($name->hasAttribute('originalName')) {
                $originalName = $name->getAttribute('originalName');

                if ($originalName instanceof Node\Name) {
                    $name = $originalName;
                }
            }

            return Rules\Expressions\Matches\StringConditional::fromString($name->toString());
        }

        return null;
    }

    private function conditionalComparatorFor(Rules\Expressions\Matches\MatchArmWithConditional ...$matchArmsWithConditionals): ?\Closure
    {
        $matchArmsWithIntConditionals = \array_filter($matchArmsWithConditionals, static function (Rules\Expressions\Matches\MatchArmWithConditional $matchArmWithConditional): bool {
            return $matchArmWithConditional->conditional() instanceof Rules\Expressions\Matches\IntConditional;
        });

        if ($matchArmsWithIntConditionals === $matchArmsWithConditionals) {
            return $this->intConditionalComparator;
        }

        $matchArmsWithStringConditionals = \array_filter($matchArmsWithConditionals, static function (Rules\Expressions\Matches\MatchArmWithConditional $matchArmWithConditional): bool {
            return $matchArmWithConditional->conditional() instanceof Rules\Expressions\Matches\StringConditional;
        });

        if ($matchArmsWithStringConditionals === $matchArmsWithConditionals) {
            return $this->stringConditionalComparator;
        }

        return null;
    }
}
