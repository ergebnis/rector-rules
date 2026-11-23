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

namespace Ergebnis\Rector\Rules\Files;

/**
 * @internal
 */
final class ClassLikeNames
{
    /**
     * @var list<ClassLikeName>
     */
    private array $values;

    private function __construct(ClassLikeName ...$values)
    {
        $this->values = \array_values($values);
    }

    public static function create(ClassLikeName ...$values): self
    {
        return new self(...$values);
    }

    public function contains(ClassLikeName $other): bool
    {
        foreach ($this->values as $value) {
            if ($value->equals($other)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<ClassLikeName>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
