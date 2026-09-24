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
final class Option
{
    private OptionName $name;
    private OptionDescription $description;
    private OptionValue $value;

    private function __construct(
        OptionName $name,
        OptionDescription $description,
        OptionValue $value
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->value = $value;
    }

    public static function create(
        OptionName $name,
        OptionDescription $description,
        OptionValue $value
    ): self {
        return new self(
            $name,
            $description,
            $value,
        );
    }

    public function name(): OptionName
    {
        return $this->name;
    }

    public function description(): OptionDescription
    {
        return $this->description;
    }

    public function value(): OptionValue
    {
        return $this->value;
    }
}
