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

use Ergebnis\Classy;
use Ergebnis\Rector\Rules;
use Rector\Rector;
use SebastianBergmann\Diff;
use Symfony\Component\Filesystem;
use Symfony\Component\Finder;
use Symplify\RuleDocGenerator;

require_once __DIR__ . '/../vendor/autoload.php';

(static function (): void {
    final class Rule
    {
        private const NAMESPACE_PREFIX = 'Ergebnis\\Rector\\Rules\\';
        private string $className;
        private RuleDocGenerator\ValueObject\RuleDefinition $ruleDefinition;
        private Rules\Configuration\Options $configurationOptions;

        private function __construct(
            string $className,
            RuleDocGenerator\ValueObject\RuleDefinition $ruleDefinition,
            Rules\Configuration\Options $configurationOptions
        ) {
            $this->className = $className;
            $this->ruleDefinition = $ruleDefinition;
            $this->configurationOptions = $configurationOptions;
        }

        public static function create(
            string $className,
            RuleDocGenerator\ValueObject\RuleDefinition $ruleDefinition,
            Rules\Configuration\Options $configurationOptions
        ): self {
            return new self(
                $className,
                $ruleDefinition,
                $configurationOptions,
            );
        }

        public function className(): string
        {
            return $this->className;
        }

        public function namespaceRelativeToNamespacePrefix(): string
        {
            $withoutPrefix = \str_replace(
                self::NAMESPACE_PREFIX,
                '',
                $this->className,
            );

            $lastSeparator = \strrpos($withoutPrefix, '\\');

            if (false === $lastSeparator) {
                return '';
            }

            return \substr($withoutPrefix, 0, $lastSeparator);
        }

        public function shortName(): string
        {
            $lastSeparator = \strrpos($this->className, '\\');

            if (false === $lastSeparator) {
                return $this->className;
            }

            return \substr($this->className, $lastSeparator + 1);
        }

        public function ruleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
        {
            return $this->ruleDefinition;
        }

        public function configurationOptions(): Rules\Configuration\Options
        {
            return $this->configurationOptions;
        }

        public function docPath(): string
        {
            return 'doc/rules/' . \str_replace('\\', '/', $this->namespaceRelativeToNamespacePrefix()) . '/' . $this->shortName() . '.md';
        }
    }

    $documentationGenerator = new class() {
        private Filesystem\Filesystem $fileSystem;
        private Diff\Differ $differ;

        public function __construct()
        {
            $this->fileSystem = new Filesystem\Filesystem();
            $this->differ = new Diff\Differ(new Diff\Output\DiffOnlyOutputBuilder(''));
        }

        public function run(): void
        {
            $sourceDirectory = __DIR__ . '/../src';

            $docDirectory = __DIR__ . '/../doc';

            $docsRulesDirectory = $docDirectory . '/rules';

            if ($this->fileSystem->exists($docDirectory)) {
                $this->fileSystem->remove($docDirectory);
            }

            $collector = new Classy\Collector\DefaultConstructFromFinderCollector(new Classy\Collector\TokenGetAllConstructFromSourceCollector());

            $finder = Finder\Finder::create()
                ->files()
                ->in($sourceDirectory)
                ->name('*Rector.php');

            $constructs = \array_filter($collector->collectFromFinder($finder), static function (Classy\ConstructFromSplFileInfo $construct): bool {
                if (!$construct->type()->equals(Classy\Type::class())) {
                    return false;
                }

                $contents = \file_get_contents($construct->splFileInfo()->getPathname());

                if (!\is_string($contents)) {
                    return false;
                }

                /**
                 * @see Rules\Files\UseImportRelativeToNamespacePrefixRector
                 */
                if (\strpos($contents, 'E_USER_DEPRECATED') !== false) {
                    return false;
                }

                return true;
            });

            \usort($constructs, static function (Classy\ConstructFromSplFileInfo $a, Classy\ConstructFromSplFileInfo $b): int {
                return $a->name()->toString() <=> $b->name()->toString();
            });

            $rulesByNamespace = [];

            foreach ($constructs as $construct) {
                $class = $construct->name()->toString();

                $reflectionClass = new \ReflectionClass($class);

                if (!$reflectionClass->isSubclassOf(Rector\AbstractRector::class)) {
                    continue;
                }

                $rector = $reflectionClass->newInstanceWithoutConstructor();

                if (!$rector instanceof Rector\AbstractRector) {
                    continue;
                }

                if (!$reflectionClass->hasMethod('getRuleDefinition')) {
                    continue;
                }

                $reflectionMethod = $reflectionClass->getMethod('getRuleDefinition');

                if (!$reflectionMethod->isPublic()) {
                    continue;
                }

                $ruleDefinition = $rector->getRuleDefinition();

                $configurationOptions = Rules\Configuration\Options::create();

                if ($rector instanceof Rules\Configuration\HasConfigurationOptions) {
                    $configurationOptions = $rector->configurationOptions();
                }

                $rule = Rule::create(
                    $class,
                    $ruleDefinition,
                    $configurationOptions,
                );

                $rulesByNamespace[$rule->namespaceRelativeToNamespacePrefix()][] = $rule;
            }

            \ksort($rulesByNamespace);

            foreach ($rulesByNamespace as $namespace => $rules) {
                $namespaceDirectory = $docsRulesDirectory . '/' . \str_replace('\\', '/', $namespace);

                $this->fileSystem->mkdir($namespaceDirectory);

                foreach ($rules as $rule) {
                    $filePath = $namespaceDirectory . '/' . $rule->shortName() . '.md';

                    $content = $this->documentationFor($rule);

                    $this->fileSystem->dumpFile(
                        $filePath,
                        $content,
                    );

                    echo \sprintf(
                        'Generated %s' . \PHP_EOL,
                        \str_replace(
                            \dirname($docsRulesDirectory, 2) . '/',
                            '',
                            $filePath,
                        ),
                    );
                }
            }

            $this->updateReadme(
                __DIR__ . '/../README.md',
                $rulesByNamespace,
            );

            echo 'Done.' . \PHP_EOL;
        }

        private function documentationFor(Rule $rule): string
        {
            $lines = [];

            $lines[] = '# `' . $rule->namespaceRelativeToNamespacePrefix() . '\\' . $rule->shortName() . '`';
            $lines[] = '';
            $lines[] = $rule->ruleDefinition()->getDescription();
            $lines[] = '';

            $codeSamples = $rule->ruleDefinition()->getCodeSamples();
            $sampleCount = \count($codeSamples);

            $configurationOptions = $rule->configurationOptions()->toArray();

            if (\count($configurationOptions) > 0) {
                $lines[] = '## Configuration';
                $lines[] = '';

                foreach ($configurationOptions as $option) {
                    $value = $option->value();

                    $lines[] = '### `' . $option->name()->toString() . '`';
                    $lines[] = '';

                    $lines[] = $option->description()->toString();
                    $lines[] = '';
                    $lines[] = '- type: `' . $value->type() . '`';

                    $allowedValues = $value->allowedValues();

                    if (\count($allowedValues) > 0) {
                        $formatted = \array_map(
                            static function ($allowedValue): string {
                                return '`' . self::formatValue($allowedValue) . '`';
                            },
                            $allowedValues,
                        );

                        $lines[] = '- allowed values: ' . \implode(', ', $formatted);
                    }

                    $lines[] = '- default value: `' . self::formatValue($value->default()) . '`';
                    $lines[] = '';
                }
            }

            $lines[] = '## Examples';
            $lines[] = '';

            foreach ($codeSamples as $index => $codeSample) {
                if (1 < $sampleCount) {
                    $lines[] = '### Example ' . ($index + 1);
                } else {
                    $lines[] = '### Example';
                }

                $lines[] = '';

                if ($codeSample instanceof RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample) {
                    $configuration = $codeSample->getConfiguration();

                    $lines[] = 'Configuration:';
                    $lines[] = '';

                    foreach ($configuration as $key => $value) {
                        $lines[] = '- `' . $key . '`: `' . self::formatValue($value) . '`';
                    }

                    $lines[] = '';
                }

                $diff = $this->diff(
                    $codeSample->getBadCode(),
                    $codeSample->getGoodCode(),
                );

                $lines[] = '```diff';
                $lines[] = $diff;
                $lines[] = '```';
                $lines[] = '';
            }

            return \implode("\n", $lines);
        }

        /**
         * @param mixed $value
         */
        private static function formatValue($value): string
        {
            if (\is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            if (\is_array($value)) {
                $items = \array_map(static function ($item): string {
                    return \sprintf(
                        "'%s'",
                        $item,
                    );
                }, $value);

                return '[' . \implode(', ', $items) . ']';
            }

            return \sprintf(
                "'%s'",
                (string) $value,
            );
        }

        private function diff(
            string $before,
            string $after
        ): string {
            $entries = $this->differ->diffToArray(
                $before,
                $after,
            );

            $output = [];

            foreach ($entries as $entry) {
                $line = \rtrim(
                    $entry[0],
                    "\n",
                );

                switch ($entry[1]) {
                    case Diff\Differ::OLD:
                        $output[] = ' ' . $line;

                        break;

                    case Diff\Differ::REMOVED:
                        $output[] = '-' . $line;

                        break;

                    case Diff\Differ::ADDED:
                        $output[] = '+' . $line;

                        break;
                }
            }

            return \implode("\n", $output);
        }

        private static function anchorFor(string $heading): string
        {
            $anchor = \strtolower($heading);
            $anchor = \preg_replace(
                '/[^a-z0-9\s-]/',
                '',
                $anchor,
            );

            return \preg_replace(
                '/\s+/',
                '-',
                \trim($anchor),
            );
        }

        /**
         * @param array<string, list<Rule>> $rulesByNamespace
         */
        private function updateReadme(
            string $readmePath,
            array $rulesByNamespace
        ): void {
            $content = \file_get_contents($readmePath);

            if (false === $content) {
                echo \sprintf(
                    'Could not read "%s".' . \PHP_EOL,
                    $readmePath,
                );

                exit(1);
            }

            $beginMarker = '<!-- BEGIN RULES -->';
            $endMarker = '<!-- END RULES -->';

            $beginPosition = \strpos(
                $content,
                $beginMarker,
            );

            $endPosition = \strpos(
                $content,
                $endMarker,
            );

            if (
                false === $beginPosition
                || false === $endPosition
            ) {
                throw new \RuntimeException(\sprintf(
                    'Could not find markers "%s" and "%s" in "%s".',
                    $beginMarker,
                    $endMarker,
                    $readmePath,
                ));
            }

            $rulesSection = $this->rulesSection($rulesByNamespace);

            $newContent = \substr($content, 0, $beginPosition + \strlen($beginMarker))
                . "\n\n"
                . $rulesSection
                . "\n"
                . \substr($content, $endPosition);

            $this->fileSystem->dumpFile(
                $readmePath,
                $newContent,
            );

            echo 'Updated README.md' . \PHP_EOL;
        }

        /**
         * @param array<string, list<Rule>> $rulesByNamespace
         */
        private function rulesSection(array $rulesByNamespace): string
        {
            $lines = [];

            $lines[] = 'This project provides the following rules for [`rector/rector`](https://github.com/rectorphp/rector):';
            $lines[] = '';

            foreach ($rulesByNamespace as $rules) {
                foreach ($rules as $rule) {
                    $heading = $rule->namespaceRelativeToNamespacePrefix() . '\\' . $rule->shortName();

                    $anchor = self::anchorFor($heading);

                    $lines[] = '- [`' . $rule->className() . '`](#' . $anchor . ')';
                }
            }

            $lines[] = '';

            foreach ($rulesByNamespace as $namespace => $rules) {
                $lines[] = '### ' . $namespace;
                $lines[] = '';

                foreach ($rules as $rule) {
                    $lines[] = '#### `' . $rule->namespaceRelativeToNamespacePrefix() . '\\' . $rule->shortName() . '`';
                    $lines[] = '';
                    $lines[] = $rule->ruleDefinition()->getDescription();
                    $lines[] = '';

                    $codeSamples = $rule->ruleDefinition()->getCodeSamples();

                    if (\count($codeSamples) > 0) {
                        $firstSample = $codeSamples[0];

                        $diff = $this->diff(
                            $firstSample->getBadCode(),
                            $firstSample->getGoodCode(),
                        );

                        $lines[] = '```diff';
                        $lines[] = $diff;
                        $lines[] = '```';
                        $lines[] = '';
                    }

                    $lines[] = '💡 Find out more in the rule documentation for [`' . $rule->namespaceRelativeToNamespacePrefix() . '\\' . $rule->shortName() . '`](' . $rule->docPath() . ').';
                    $lines[] = '';
                }
            }

            return \implode(
                "\n",
                $lines,
            );
        }
    };

    $documentationGenerator->run();
})();
