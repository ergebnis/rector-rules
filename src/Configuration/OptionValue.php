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

use Ergebnis\Rector\Rules;

/**
 * @internal
 */
final class OptionValue
{
    private string $type;
    private $default;

    /**
     * @var array<int, mixed>
     */
    private array $allowedValues;

    /**
     * @var \Closure(mixed): mixed
     */
    private \Closure $resolver;

    /**
     * @param mixed                  $default
     * @param array<int, mixed>      $allowedValues
     * @param \Closure(mixed): mixed $resolver
     */
    private function __construct(
        string $type,
        $default,
        array $allowedValues,
        \Closure $resolver
    ) {
        $this->type = $type;
        $this->default = $default;
        $this->allowedValues = $allowedValues;
        $this->resolver = $resolver;
    }

    public static function booleanDefaultingTo(bool $default): self
    {
        return new self(
            'bool',
            $default,
            [],
            static function ($value): bool {
                if (!\is_bool($value)) {
                    throw Rules\Configuration\InvalidOptionValue::typeMismatch('a boolean');
                }

                return $value;
            },
        );
    }

    public static function string(string $default): self
    {
        return new self(
            'string',
            $default,
            [],
            static function ($value): string {
                if (!\is_string($value)) {
                    throw Rules\Configuration\InvalidOptionValue::typeMismatch('a string');
                }

                return $value;
            },
        );
    }

    /**
     * @param array<int, string> $allowedValues
     */
    public static function oneOf(
        array $allowedValues,
        string $default
    ): self {
        return new self(
            'string',
            $default,
            $allowedValues,
            static function ($value) use ($allowedValues): string {
                if (!\is_string($value)) {
                    throw Rules\Configuration\InvalidOptionValue::typeMismatchWithAllowedValues($allowedValues);
                }

                if (!\in_array($value, $allowedValues, true)) {
                    throw Rules\Configuration\InvalidOptionValue::notAllowed(
                        $value,
                        $allowedValues,
                    );
                }

                return $value;
            },
        );
    }

    /**
     * @param array<int, string> $default
     */
    public static function listOfStringsDefaultingTo(array $default = []): self
    {
        return new self(
            'list<string>',
            $default,
            [],
            static function ($value): array {
                if (!\is_array($value)) {
                    throw Rules\Configuration\InvalidOptionValue::typeMismatch('a list of strings');
                }

                if (\array_values($value) !== $value) {
                    throw Rules\Configuration\InvalidOptionValue::typeMismatch('a list of strings');
                }

                foreach ($value as $item) {
                    if (!\is_string($item)) {
                        throw Rules\Configuration\InvalidOptionValue::typeMismatch('a list of strings');
                    }
                }

                return $value;
            },
        );
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function resolve($value)
    {
        return ($this->resolver)($value);
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function default()
    {
        return $this->default;
    }

    /**
     * @return array<int, mixed>
     */
    public function allowedValues(): array
    {
        return $this->allowedValues;
    }
}
