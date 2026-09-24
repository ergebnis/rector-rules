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

namespace Ergebnis\Rector\Rules\Configuration;

/**
 * @internal
 */
final class Options
{
    /**
     * @var array<string, Option>
     */
    private array $values;

    /**
     * @param array<string, Option> $values
     */
    private function __construct(array $values)
    {
        $this->values = $values;
    }

    public static function create(Option ...$options): self
    {
        $optionNames = \array_map(static function (Option $option): string {
            return $option->name()->toString();
        }, $options);

        $duplicateOptionNames = \array_values(\array_unique(\array_diff_assoc(
            $optionNames,
            \array_unique($optionNames),
        )));

        if ([] !== $duplicateOptionNames) {
            throw DuplicateOptionName::create(...$duplicateOptionNames);
        }

        return new self(\array_combine(
            $optionNames,
            $options,
        ));
    }

    /**
     * @return list<Option>
     */
    public function toArray(): array
    {
        return \array_values($this->values);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function resolveConfigurationFrom(array $configuration): Configuration
    {
        $unknownOptionNames = \array_diff(
            \array_keys($configuration),
            \array_keys($this->values),
        );

        if (\count($unknownOptionNames) > 0) {
            throw UnknownOptionName::create(...$unknownOptionNames);
        }

        $resolved = [];

        foreach ($this->values as $optionName => $option) {
            if (!\array_key_exists($optionName, $configuration)) {
                $resolved[$optionName] = $option->value()->default();

                continue;
            }

            $value = $configuration[$optionName];

            try {
                $resolved[$optionName] = $option->value()->resolve($value);
            } catch (InvalidOptionValue $exception) {
                throw InvalidOptionValue::forOption(
                    $optionName,
                    $exception,
                );
            }
        }

        return Configuration::fromArray($resolved);
    }
}
