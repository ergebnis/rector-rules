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
 * @see https://www.php.net/manual/en/language.namespaces.rules.php
 */
final class NameResolver
{
    private const SPECIAL_CLASS_NAMES = [
        'parent',
        'self',
        'static',
    ];

    public function resolve(
        Spelling $spelling,
        SymbolKind $symbolKind,
        FileNamespace $namespace,
        Imports $imports
    ): ?FullyQualifiedName {
        $name = $spelling->toString();

        if ('' === $name) {
            return null;
        }

        if (\strpos($name, '\\') === 0) {
            return FullyQualifiedName::fromString(\substr(
                $name,
                1,
            ));
        }

        $segments = \explode(
            '\\',
            $name,
        );

        if (\count($segments) > 1) {
            $leadingSegment = \array_shift($segments);

            if ('namespace' === \strtolower($leadingSegment)) {
                $relativeName = \implode(
                    '\\',
                    $segments,
                );

                return $namespace->qualify(Spelling::fromString($relativeName));
            }

            $import = $imports
                ->ofSymbolKind(SymbolKind::classLike())
                ->forLocalName(LocalName::fromString($leadingSegment));

            if ($import instanceof Import) {
                return $import->fullyQualifiedName()->append(...$segments);
            }

            return $namespace->qualify($spelling);
        }

        if ($symbolKind->isClassLike()) {
            if (\in_array(\strtolower($name), self::SPECIAL_CLASS_NAMES, true)) {
                return null;
            }

            $import = $imports
                ->ofSymbolKind($symbolKind)
                ->forLocalName(LocalName::fromString($name));

            if ($import instanceof Import) {
                return $import->fullyQualifiedName();
            }

            return $namespace->qualify($spelling);
        }

        $import = $imports
            ->ofSymbolKind($symbolKind)
            ->forLocalName(LocalName::fromString($name));

        if ($import instanceof Import) {
            return $import->fullyQualifiedName();
        }

        if ($namespace->isGlobal()) {
            return FullyQualifiedName::fromString($name);
        }

        return null;
    }
}
