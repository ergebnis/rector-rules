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
final class Configuration
{
    /**
     * @var array<string, mixed>
     */
    private array $values;

    /**
     * @param array<string, mixed> $values
     */
    private function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @throws InvalidConfigurationKey
     */
    public static function fromArray(array $values): self
    {
        $invalidKeys = \array_filter(\array_keys($values), static function ($key): bool {
            return !\is_string($key);
        });

        if ([] !== $invalidKeys) {
            throw InvalidConfigurationKey::with(...\array_map(static function ($invalidKey): string {
                return \gettype($invalidKey);
            }, $invalidKeys));
        }

        return new self($values);
    }

    /**
     * @throws UnknownOptionName
     *
     * @return mixed
     */
    public function get(OptionName $name)
    {
        if (!\array_key_exists($name->toString(), $this->values)) {
            throw UnknownOptionName::create($name->toString());
        }

        return $this->values[$name->toString()];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
