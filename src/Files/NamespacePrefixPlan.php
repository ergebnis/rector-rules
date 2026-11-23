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
final class NamespacePrefixPlan
{
    private Respellings $respellings;
    private bool $hasNamespacePrefixImport;
    private bool $requiresNamespacePrefixImport;
    private ?Import $replacedImport;

    private function __construct(
        Respellings $respellings,
        bool $hasNamespacePrefixImport,
        bool $requiresNamespacePrefixImport,
        ?Import $replacedImport
    ) {
        $this->respellings = $respellings;
        $this->hasNamespacePrefixImport = $hasNamespacePrefixImport;
        $this->requiresNamespacePrefixImport = $requiresNamespacePrefixImport;
        $this->replacedImport = $replacedImport;
    }

    public static function create(
        Respellings $respellings,
        bool $hasNamespacePrefixImport,
        bool $requiresNamespacePrefixImport,
        ?Import $replacedImport
    ): self {
        return new self(
            $respellings,
            $hasNamespacePrefixImport,
            $requiresNamespacePrefixImport,
            $replacedImport,
        );
    }

    public function respellings(): Respellings
    {
        return $this->respellings;
    }

    public function hasNamespacePrefixImport(): bool
    {
        return $this->hasNamespacePrefixImport;
    }

    public function requiresNamespacePrefixImport(): bool
    {
        return $this->requiresNamespacePrefixImport;
    }

    public function replacedImport(): ?Import
    {
        return $this->replacedImport;
    }
}
