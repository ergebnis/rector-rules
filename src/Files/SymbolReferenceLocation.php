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
final class SymbolReferenceLocation
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function code(): self
    {
        return new self('code');
    }

    public static function docBlockText(): self
    {
        return new self('doc-block-text');
    }

    public static function docBlockType(): self
    {
        return new self('doc-block-type');
    }

    public function isCode(): bool
    {
        return 'code' === $this->value;
    }

    public function isDocBlockText(): bool
    {
        return 'doc-block-text' === $this->value;
    }

    public function isDocBlockType(): bool
    {
        return 'doc-block-type' === $this->value;
    }
}
