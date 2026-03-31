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
final class Option
{
    private Rules\Configuration\OptionName $name;
    private Rules\Configuration\OptionDescription $description;
    private Rules\Configuration\OptionValue $value;

    private function __construct(
        Rules\Configuration\OptionName $name,
        Rules\Configuration\OptionDescription $description,
        Rules\Configuration\OptionValue $value
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->value = $value;
    }

    public static function create(
        Rules\Configuration\OptionName $name,
        Rules\Configuration\OptionDescription $description,
        Rules\Configuration\OptionValue $value
    ): self {
        return new self(
            $name,
            $description,
            $value,
        );
    }

    public function name(): Rules\Configuration\OptionName
    {
        return $this->name;
    }

    public function description(): Rules\Configuration\OptionDescription
    {
        return $this->description;
    }

    public function value(): Rules\Configuration\OptionValue
    {
        return $this->value;
    }
}
