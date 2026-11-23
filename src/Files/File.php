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
final class File
{
    private FileNamespace $namespace;
    private Imports $imports;
    private ClassLikeNames $declaredClassLikeNames;
    private SymbolReferences $symbolReferences;

    private function __construct(
        FileNamespace $namespace,
        Imports $imports,
        ClassLikeNames $declaredClassLikeNames,
        SymbolReferences $symbolReferences
    ) {
        $this->namespace = $namespace;
        $this->imports = $imports;
        $this->declaredClassLikeNames = $declaredClassLikeNames;
        $this->symbolReferences = $symbolReferences;
    }

    public static function create(
        FileNamespace $namespace,
        Imports $imports,
        ClassLikeNames $declaredClassLikeNames,
        SymbolReferences $symbolReferences
    ): self {
        return new self(
            $namespace,
            $imports,
            $declaredClassLikeNames,
            $symbolReferences,
        );
    }

    public function namespace(): FileNamespace
    {
        return $this->namespace;
    }

    public function imports(): Imports
    {
        return $this->imports;
    }

    public function declaredClassLikeNames(): ClassLikeNames
    {
        return $this->declaredClassLikeNames;
    }

    public function symbolReferences(): SymbolReferences
    {
        return $this->symbolReferences;
    }
}
