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
final class Reference
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function is(NamespacePrefix $namespacePrefix): bool
    {
        return $namespacePrefix->toString() === $this->value;
    }

    public function isDeclaredIn(NamespacePrefix $namespacePrefix): bool
    {
        return \strpos($this->value, $namespacePrefix->toString() . '\\') === 0;
    }

    public function isOrIsDeclaredInOneOf(NamespacePrefix ...$namespacePrefixes): bool
    {
        foreach ($namespacePrefixes as $namespacePrefix) {
            if ($this->is($namespacePrefix)) {
                return true;
            }

            if ($this->isDeclaredIn($namespacePrefix)) {
                return true;
            }
        }

        return false;
    }

    public function append(string ...$segments): self
    {
        return new self(\sprintf(
            '%s\\%s',
            $this->value,
            \implode('\\', $segments),
        ));
    }

    public function relativeTo(NamespacePrefix $namespacePrefix): self
    {
        return new self(\substr(
            $this->value,
            \strlen($namespacePrefix->toString()) + 1,
        ));
    }
}
