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
final class NamespacePrefixPlanner
{
    private const RESERVED_NAMES = [
        'array',
        'bool',
        'callable',
        'false',
        'float',
        'int',
        'iterable',
        'mixed',
        'never',
        'null',
        'object',
        'parent',
        'self',
        'static',
        'string',
        'true',
        'void',
    ];
    private NameResolver $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function plan(
        File $file,
        NamespacePrefix $namespacePrefix,
        NamespacePrefixes $moreSpecificNamespacePrefixes,
        FullyQualifiedNames $fullyQualifiedClassLikeNames,
        bool $forceRelativeReferences
    ): ?NamespacePrefixPlan {
        $lastNamespaceSegment = $namespacePrefix->lastNamespaceSegment()->toString();

        if (\in_array(\strtolower($lastNamespaceSegment), self::RESERVED_NAMES, true)) {
            return null;
        }

        $classImports = $file->imports()->ofSymbolKind(SymbolKind::classLike())->toArray();

        $hasNamespacePrefixImport = false;
        $hasNamespacePrefixImportWithAlias = false;

        foreach ($classImports as $import) {
            if (!$import->fullyQualifiedName()->is($namespacePrefix)) {
                continue;
            }

            if ($import->hasLocalName(LocalName::fromString($lastNamespaceSegment))) {
                $hasNamespacePrefixImport = true;
            } else {
                $hasNamespacePrefixImportWithAlias = true;
            }
        }

        if (
            $hasNamespacePrefixImportWithAlias
            && !$hasNamespacePrefixImport
        ) {
            return null;
        }

        $isMatchingImport = static function (Import $import) use ($namespacePrefix, $moreSpecificNamespacePrefixes): bool {
            return !$import->fullyQualifiedName()->is($namespacePrefix)
                && $import->fullyQualifiedName()->isDeclaredIn($namespacePrefix)
                && !$import->fullyQualifiedName()->isOrIsDeclaredInOneOf($moreSpecificNamespacePrefixes);
        };

        $matchingImports = \array_filter(
            $classImports,
            $isMatchingImport,
        );

        $hasMatchingImports = [] !== $matchingImports;

        if (!self::isRelevant(
            $file,
            $namespacePrefix,
            $moreSpecificNamespacePrefixes,
            $classImports,
            $hasMatchingImports,
            $hasNamespacePrefixImport,
            $forceRelativeReferences,
        )) {
            return null;
        }

        $namespace = $file->namespace();

        $namespaceIsOrContainsNamespacePrefix = false;
        $namespaceIsInsideNamespacePrefix = false;

        if (!$forceRelativeReferences) {
            $namespaceIsOrContainsNamespacePrefix = $namespace->isOrContains($namespacePrefix);
            $namespaceIsInsideNamespacePrefix = $namespace->isInside($namespacePrefix);
        }

        $unavailableFirstNamespaceSegments = [
            \strtolower($lastNamespaceSegment),
        ];

        foreach ($classImports as $import) {
            if (
                $import->fullyQualifiedName()->isDeclaredIn($namespacePrefix)
                && !$import->fullyQualifiedName()->isOrIsDeclaredInOneOf($moreSpecificNamespacePrefixes)
            ) {
                continue;
            }

            $unavailableFirstNamespaceSegments[] = \strtolower($import->localName()->toString());
        }

        $requiresNamespacePrefixImport = false;
        $respellingsSoFar = [];

        foreach ($file->symbolReferences()->toArray() as $symbolReference) {
            if ($symbolReference->location()->isDocBlockText()) {
                continue;
            }

            $spelling = self::newSpelling(
                $symbolReference,
                $file,
                $namespacePrefix,
                $moreSpecificNamespacePrefixes,
                $fullyQualifiedClassLikeNames,
                $namespaceIsOrContainsNamespacePrefix,
                $namespaceIsInsideNamespacePrefix,
                $unavailableFirstNamespaceSegments,
                $requiresNamespacePrefixImport,
            );

            if (null === $spelling) {
                continue;
            }

            $respellingsSoFar[] = Respelling::create(
                $symbolReference,
                $spelling,
            );
        }

        $respellings = Respellings::create(...$respellingsSoFar);

        if (!$namespaceIsInsideNamespacePrefix) {
            $requiresNamespacePrefixImport = !$namespaceIsOrContainsNamespacePrefix;
        }

        if (
            !$hasMatchingImports
            && $respellings->isEmpty()
        ) {
            return null;
        }

        $replacedImport = null;

        if (
            $requiresNamespacePrefixImport
            && !$hasNamespacePrefixImport
        ) {
            if ($file->declaredClassLikeNames()->contains(ClassLikeName::fromString($lastNamespaceSegment))) {
                return null;
            }

            foreach ($classImports as $import) {
                if (
                    $import->fullyQualifiedName()->is($namespacePrefix)
                    || $import->fullyQualifiedName()->isDeclaredIn($namespacePrefix)
                ) {
                    continue;
                }

                if (!$import->hasLocalName(LocalName::fromString($lastNamespaceSegment))) {
                    continue;
                }

                if (!NamespacePrefix::fromString($import->fullyQualifiedName()->toString())->isNamespacePrefixOf($namespacePrefix)) {
                    return null;
                }

                $replacedImport = $import;
            }
        }

        $importsAfterwards = [];

        foreach ($classImports as $import) {
            if ($import === $replacedImport) {
                continue;
            }

            if ($isMatchingImport($import)) {
                continue;
            }

            if (
                $import->fullyQualifiedName()->is($namespacePrefix)
                && !$requiresNamespacePrefixImport
            ) {
                continue;
            }

            $importsAfterwards[] = $import;
        }

        if (
            $requiresNamespacePrefixImport
            && !$hasNamespacePrefixImport
        ) {
            $importsAfterwards[] = Import::create(
                SymbolKind::classLike(),
                FullyQualifiedName::fromString($namespacePrefix->toString()),
                LocalName::fromString($lastNamespaceSegment),
            );
        }

        foreach ($file->imports()->toArray() as $import) {
            if (!\in_array($import, $classImports, true)) {
                $importsAfterwards[] = $import;
            }
        }

        if (!$this->resolvesToSameNames(
            $file,
            $respellings,
            Imports::create(...$importsAfterwards),
        )) {
            return null;
        }

        return NamespacePrefixPlan::create(
            $respellings,
            $hasNamespacePrefixImport,
            $requiresNamespacePrefixImport,
            $replacedImport,
        );
    }

    /**
     * @param list<Import> $classImports
     */
    private static function isRelevant(
        File $file,
        NamespacePrefix $namespacePrefix,
        NamespacePrefixes $moreSpecificNamespacePrefixes,
        array $classImports,
        bool $hasMatchingImports,
        bool $hasNamespacePrefixImport,
        bool $forceRelativeReferences
    ): bool {
        if (
            $hasMatchingImports
            || $hasNamespacePrefixImport
        ) {
            return true;
        }

        foreach ($classImports as $import) {
            if (NamespacePrefix::fromString($import->fullyQualifiedName()->toString())->isNamespacePrefixOf($namespacePrefix)) {
                return true;
            }
        }

        $namespace = $file->namespace();

        foreach ($file->symbolReferences()->toArray() as $symbolReference) {
            $spelling = $symbolReference->spelling();

            if ($symbolReference->location()->isCode()) {
                $fullyQualifiedName = $symbolReference->fullyQualifiedName();

                if (!$fullyQualifiedName instanceof FullyQualifiedName) {
                    continue;
                }

                if (
                    !$fullyQualifiedName->isOrIsDeclaredIn($namespacePrefix)
                    || $fullyQualifiedName->isOrIsDeclaredInOneOf($moreSpecificNamespacePrefixes)
                ) {
                    continue;
                }

                if ($spelling->isFullyQualified()) {
                    return true;
                }

                if (
                    $forceRelativeReferences
                    && !$spelling->isUnqualified()
                ) {
                    return true;
                }

                continue;
            }

            if (\strpos($spelling->toString(), '\\' . $namespacePrefix->toString() . '\\') === 0) {
                return true;
            }

            if (
                !$forceRelativeReferences
                || $namespace->isGlobal()
                || $spelling->isFullyQualified()
                || $spelling->isUnqualified()
                || 1 !== \preg_match('/^[A-Z]/', $spelling->toString())
            ) {
                continue;
            }

            $fullyQualifiedName = $namespace->qualify($spelling);

            if (
                $fullyQualifiedName->isOrIsDeclaredIn($namespacePrefix)
                && !$fullyQualifiedName->isOrIsDeclaredInOneOf($moreSpecificNamespacePrefixes)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $unavailableFirstNamespaceSegments
     */
    private static function newSpelling(
        SymbolReference $symbolReference,
        File $file,
        NamespacePrefix $namespacePrefix,
        NamespacePrefixes $moreSpecificNamespacePrefixes,
        FullyQualifiedNames $fullyQualifiedClassLikeNames,
        bool $namespaceIsOrContainsNamespacePrefix,
        bool $namespaceIsInsideNamespacePrefix,
        array $unavailableFirstNamespaceSegments,
        bool &$requiresNamespacePrefixImport
    ): ?Spelling {
        $fullyQualifiedName = $symbolReference->fullyQualifiedName();

        if (!$fullyQualifiedName instanceof FullyQualifiedName) {
            return null;
        }

        if (
            !$fullyQualifiedName->is($namespacePrefix)
            && !$fullyQualifiedName->isDeclaredIn($namespacePrefix)
        ) {
            return null;
        }

        $spelling = $symbolReference->spelling();
        $isFullyQualified = $spelling->isFullyQualified();
        $isFunctionOrConstant = !$symbolReference->symbolKind()->isClassLike();

        if ($symbolReference->location()->isCode()) {
            if (
                $isFunctionOrConstant
                && $spelling->isUnqualified()
            ) {
                return null;
            }

            if (
                $isFunctionOrConstant
                && $fullyQualifiedName->is($namespacePrefix)
            ) {
                return null;
            }
        } elseif (!$isFullyQualified) {
            $import = $file->imports()
                ->ofSymbolKind(SymbolKind::classLike())
                ->forLocalName(LocalName::fromString($spelling->leadingSegment()));

            if (!$import instanceof Import) {
                if ($file->namespace()->isGlobal()) {
                    return null;
                }

                if (
                    $spelling->isUnqualified()
                    && !$fullyQualifiedClassLikeNames->contains($fullyQualifiedName)
                ) {
                    return null;
                }
            }
        }

        if ($fullyQualifiedName->isOrIsDeclaredInOneOf($moreSpecificNamespacePrefixes)) {
            return null;
        }

        $namespace = $file->namespace();

        if ($namespaceIsOrContainsNamespacePrefix) {
            if ($namespace->is($fullyQualifiedName)) {
                return null;
            }

            $newSpelling = $namespace->relativeName($fullyQualifiedName);

            if (
                $symbolReference->location()->isCode()
                && $isFunctionOrConstant
                && $newSpelling->isUnqualified()
            ) {
                return null;
            }
        } else {
            $newSpelling = self::nameRelativeToNamespacePrefixOrNamespaceOfContainingFile(
                $fullyQualifiedName,
                $namespacePrefix,
                $namespace,
                $namespaceIsInsideNamespacePrefix,
                $unavailableFirstNamespaceSegments,
                $requiresNamespacePrefixImport,
                $symbolReference->location()->isCode() && $isFunctionOrConstant,
            );
        }

        if ($symbolReference->location()->isCode()) {
            if ($spelling->withoutLeadingBackslash() === $newSpelling->toString()) {
                return null;
            }
        } elseif ($spelling->equals($newSpelling)) {
            return null;
        }

        return $newSpelling;
    }

    /**
     * @param list<string> $unavailableFirstNamespaceSegments
     */
    private static function nameRelativeToNamespacePrefixOrNamespaceOfContainingFile(
        FullyQualifiedName $fullyQualifiedName,
        NamespacePrefix $namespacePrefix,
        FileNamespace $namespace,
        bool $namespaceIsInsideNamespacePrefix,
        array $unavailableFirstNamespaceSegments,
        bool &$requiresNamespacePrefixImport,
        bool $requiresQualifiedName
    ): Spelling {
        if (
            $namespaceIsInsideNamespacePrefix
            && $namespace->contains($fullyQualifiedName)
        ) {
            $name = $namespace->relativeName($fullyQualifiedName);

            $namespaceSegments = \explode(
                '\\',
                $name->toString(),
            );

            if (
                !\in_array(\strtolower($namespaceSegments[0]), $unavailableFirstNamespaceSegments, true)
                && (
                    !$requiresQualifiedName
                    || \count($namespaceSegments) > 1
                )
            ) {
                return $name;
            }
        }

        $requiresNamespacePrefixImport = true;

        if ($fullyQualifiedName->is($namespacePrefix)) {
            return Spelling::fromString($namespacePrefix->lastNamespaceSegment()->toString());
        }

        return Spelling::fromString(\sprintf(
            '%s\\%s',
            $namespacePrefix->lastNamespaceSegment()->toString(),
            $fullyQualifiedName->relativeTo($namespacePrefix)->toString(),
        ));
    }

    private function resolvesToSameNames(
        File $file,
        Respellings $respellings,
        Imports $importsAfterwards
    ): bool {
        foreach ($file->symbolReferences()->toArray() as $symbolReference) {
            $fullyQualifiedName = $symbolReference->fullyQualifiedName();

            if (!$fullyQualifiedName instanceof FullyQualifiedName) {
                continue;
            }

            $spelling = $respellings->spellingOf($symbolReference);

            if (!$spelling instanceof Spelling) {
                $spelling = $symbolReference->spelling();
            }

            $fullyQualifiedNameAfterwards = $this->nameResolver->resolve(
                $spelling,
                $symbolReference->symbolKind(),
                $file->namespace(),
                $importsAfterwards,
            );

            if (!$fullyQualifiedNameAfterwards instanceof FullyQualifiedName) {
                return false;
            }

            if ($fullyQualifiedNameAfterwards->toString() !== $fullyQualifiedName->toString()) {
                return false;
            }
        }

        return true;
    }
}
