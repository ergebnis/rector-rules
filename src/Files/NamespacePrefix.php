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
 * @see https://www.php.net/manual/en/language.namespaces.definition.php
 */
final class NamespacePrefix
{
    private NamespaceSegments $namespaceSegments;

    private function __construct(NamespaceSegments $namespaceSegments)
    {
        $this->namespaceSegments = $namespaceSegments;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        try {
            $namespaceSegments = NamespaceSegments::fromString($value);
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException(\sprintf(
                'Value needs to be a valid namespace, got "%s".',
                $value,
            ));
        }

        return new self($namespaceSegments);
    }

    public function toString(): string
    {
        return $this->namespaceSegments->toString();
    }

    public function namespaceSegmentCount(): int
    {
        return $this->namespaceSegments->count();
    }

    public function lastNamespaceSegment(): NamespaceSegment
    {
        return $this->namespaceSegments->last();
    }

    public function isNamespacePrefixOf(self $other): bool
    {
        return \strpos($other->namespaceSegments->toString(), $this->namespaceSegments->toString() . '\\') === 0;
    }
}
