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
final class NamespacePrefixDiscoverer
{
    public function discoverParentNamespacePrefixes(
        File $file,
        NamespacePrefixes $parentNamespacePrefixes,
        NamespacePrefixes $namespacePrefixes
    ): NamespacePrefixes {
        /** @var array<string, NamespacePrefix> $discovered */
        $discovered = [];

        $namespace = $file->namespace();

        $names = [];

        if (!$namespace->isGlobal()) {
            $names[] = $namespace->toString();
        }

        foreach ($file->imports()->ofSymbolKind(SymbolKind::classLike())->toArray() as $import) {
            $names[] = $import->fullyQualifiedName()->toString();
        }

        $namesOfNamespaces = self::namesOfNamespaces(
            $file,
            false,
        );

        foreach ($namesOfNamespaces as $name) {
            $names[] = $name;
        }

        foreach ($names as $name) {
            $segments = \explode(
                '\\',
                $name,
            );

            if (\count($segments) < 2) {
                continue;
            }

            if (\array_key_exists($segments[0], $discovered)) {
                continue;
            }

            try {
                $discovered[$segments[0]] = NamespacePrefix::fromString($segments[0]);
            } catch (\InvalidArgumentException $exception) {
                continue;
            }
        }

        $existingNamespacePrefixes = $parentNamespacePrefixes->merge($namespacePrefixes);

        return NamespacePrefixes::create(...\array_values(\array_filter($discovered, static function (NamespacePrefix $discoveredNamespacePrefix) use ($existingNamespacePrefixes): bool {
            foreach ($existingNamespacePrefixes->toArray() as $existingNamespacePrefix) {
                if (
                    $discoveredNamespacePrefix->toString() === $existingNamespacePrefix->toString()
                    || $discoveredNamespacePrefix->isNamespacePrefixOf($existingNamespacePrefix)
                    || $existingNamespacePrefix->isNamespacePrefixOf($discoveredNamespacePrefix)
                ) {
                    return false;
                }
            }

            return true;
        })));
    }

    public function discoverNamespacePrefixes(
        File $file,
        NamespacePrefixes $parentNamespacePrefixes,
        NamespacePrefixes $namespacePrefixes
    ): NamespacePrefixes {
        if ($parentNamespacePrefixes->isEmpty()) {
            return NamespacePrefixes::create();
        }

        $names = [];

        foreach ($file->imports()->ofSymbolKind(SymbolKind::classLike())->toArray() as $import) {
            $names[] = $import->fullyQualifiedName()->toString();
        }

        $namesOfNamespaces = self::namesOfNamespaces(
            $file,
            true,
        );

        foreach ($namesOfNamespaces as $name) {
            $names[] = $name;
        }

        $discovered = [];

        foreach ($names as $name) {
            foreach ($parentNamespacePrefixes->toArray() as $parentNamespacePrefix) {
                $parent = $parentNamespacePrefix->toString();

                if (\strpos($name, $parent . '\\') !== 0) {
                    continue;
                }

                $segments = \explode(
                    '\\',
                    \substr(
                        $name,
                        \strlen($parent) + 1,
                    ),
                );

                $namespacePrefix = $parentNamespacePrefix->append(NamespaceSegment::fromString($segments[0]));

                if ($namespacePrefixes->contains($namespacePrefix)) {
                    continue;
                }

                $discovered[] = $namespacePrefix;
            }
        }

        return NamespacePrefixes::create(...$discovered);
    }

    /**
     * @return list<string>
     */
    private static function namesOfNamespaces(
        File $file,
        bool $withNamesRelativeToImportsInDocBlocks
    ): array {
        $names = [];

        foreach ($file->symbolReferences()->toArray() as $symbolReference) {
            $fullyQualifiedName = $symbolReference->fullyQualifiedName();

            if (!$fullyQualifiedName instanceof FullyQualifiedName) {
                continue;
            }

            $spelling = $symbolReference->spelling();

            if (!$symbolReference->location()->isCode()) {
                if (!$spelling->isFullyQualified()) {
                    if (!$withNamesRelativeToImportsInDocBlocks) {
                        continue;
                    }

                    $import = $file->imports()
                        ->ofSymbolKind(SymbolKind::classLike())
                        ->forLocalName(LocalName::fromString($spelling->leadingSegment()));

                    if (
                        !$import instanceof Import
                        || $spelling->isUnqualified()
                    ) {
                        continue;
                    }
                }

                $names[] = $fullyQualifiedName->toString();

                continue;
            }

            if ($symbolReference->symbolKind()->isClassLike()) {
                $names[] = $fullyQualifiedName->toString();

                continue;
            }

            $segments = \explode(
                '\\',
                $fullyQualifiedName->toString(),
            );

            \array_pop($segments);

            $names[] = \implode(
                '\\',
                $segments,
            );
        }

        return $names;
    }
}
