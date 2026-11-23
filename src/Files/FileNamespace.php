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
 *
 * @see https://www.php.net/manual/en/language.namespaces.global.php
 */
final class FileNamespace
{
    private ?NamespacePrefix $value;

    private function __construct(?NamespacePrefix $value)
    {
        $this->value = $value;
    }

    public static function global(): self
    {
        return new self(null);
    }

    public static function named(NamespacePrefix $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        if (!$this->value instanceof NamespacePrefix) {
            return '';
        }

        return $this->value->toString();
    }

    public function isGlobal(): bool
    {
        return !$this->value instanceof NamespacePrefix;
    }

    public function is(FullyQualifiedName $fullyQualifiedName): bool
    {
        if (!$this->value instanceof NamespacePrefix) {
            return false;
        }

        return $fullyQualifiedName->is($this->value);
    }

    public function contains(FullyQualifiedName $fullyQualifiedName): bool
    {
        if (!$this->value instanceof NamespacePrefix) {
            return false;
        }

        return $fullyQualifiedName->isDeclaredIn($this->value);
    }

    public function isOrContains(NamespacePrefix $namespacePrefix): bool
    {
        if (!$this->value instanceof NamespacePrefix) {
            return false;
        }

        if ($this->value->toString() === $namespacePrefix->toString()) {
            return true;
        }

        return $this->value->isNamespacePrefixOf($namespacePrefix);
    }

    public function isInside(NamespacePrefix $namespacePrefix): bool
    {
        if (!$this->value instanceof NamespacePrefix) {
            return false;
        }

        return $namespacePrefix->isNamespacePrefixOf($this->value);
    }

    public function qualify(Spelling $spelling): FullyQualifiedName
    {
        if (
            $spelling->isFullyQualified()
            || !$this->value instanceof NamespacePrefix
        ) {
            return FullyQualifiedName::fromString($spelling->withoutLeadingBackslash());
        }

        return FullyQualifiedName::fromString(\sprintf(
            '%s\\%s',
            $this->value->toString(),
            $spelling->toString(),
        ));
    }

    public function relativeName(FullyQualifiedName $fullyQualifiedName): Spelling
    {
        if (!$this->value instanceof NamespacePrefix) {
            return Spelling::fromString($fullyQualifiedName->toString());
        }

        return Spelling::fromString($fullyQualifiedName->relativeTo($this->value)->toString());
    }
}
