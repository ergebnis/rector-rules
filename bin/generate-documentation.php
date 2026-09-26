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
            return \sprintf(
                'doc/rules/%s/%s.md',
                \str_replace(
                    '\\',
                    '/',
                    $this->namespaceRelativeToNamespacePrefix(),
                ),
                $this->shortName(),
            );
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
            $sourceDirectory = \sprintf(
                '%s/../src',
                __DIR__,
            );

            $docDirectory = \sprintf(
                '%s/../doc',
                __DIR__,
            );

            $docsRulesDirectory = \sprintf(
                '%s/rules',
                $docDirectory,
            );

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
                $namespaceDirectory = \sprintf(
                    '%s/%s',
                    $docsRulesDirectory,
                    \str_replace(
                        '\\',
                        '/',
                        $namespace,
                    ),
                );

                $this->fileSystem->mkdir($namespaceDirectory);

                foreach ($rules as $rule) {
                    $filePath = \sprintf(
                        '%s/%s.md',
                        $namespaceDirectory,
                        $rule->shortName(),
                    );

                    $content = $this->documentationFor($rule);

                    $this->fileSystem->dumpFile(
                        $filePath,
                        $content,
                    );

                    echo \sprintf(
                        'Generated %s%s',
                        \str_replace(
                            \sprintf(
                                '%s/',
                                \dirname(
                                    $docsRulesDirectory,
                                    2,
                                ),
                            ),
                            '',
                            $filePath,
                        ),
                        \PHP_EOL,
                    );
                }
            }

            $this->updateReadme(
                \sprintf(
                    '%s/../README.md',
                    __DIR__,
                ),
                $rulesByNamespace,
            );

            echo \sprintf(
                'Done.%s',
                \PHP_EOL,
            );
        }

        private function documentationFor(Rule $rule): string
        {
            $lines = [];

            $lines[] = \sprintf(
                '# `%s\\%s`',
                $rule->namespaceRelativeToNamespacePrefix(),
                $rule->shortName(),
            );
            $lines[] = '';
            $lines[] = $rule->ruleDefinition()->getDescription();
            $lines[] = '';

            $codeSamples = $rule->ruleDefinition()->getCodeSamples();
            $sampleCount = \count($codeSamples);

            $configurationOptions = $rule->configurationOptions()->toArray();

            if (\count($configurationOptions) > 0) {
                $lines[] = '## Options';
                $lines[] = '';

                foreach ($configurationOptions as $option) {
                    $value = $option->value();

                    $lines[] = \sprintf(
                        '### `%s`',
                        $option->name()->toString(),
                    );
                    $lines[] = '';

                    $lines[] = $option->description()->toString();
                    $lines[] = '';
                    $lines[] = \sprintf(
                        '- type: `%s`',
                        $value->type(),
                    );

                    $allowedValues = $value->allowedValues();

                    if (\count($allowedValues) > 0) {
                        $formatted = \array_map(static function ($allowedValue): string {
                            return \sprintf(
                                '`%s`',
                                self::formatValue($allowedValue),
                            );
                        }, $allowedValues);

                        $lines[] = \sprintf(
                            '- allowed values: %s',
                            \implode(
                                ', ',
                                $formatted,
                            ),
                        );
                    }

                    $lines[] = \sprintf(
                        '- default value: `%s`',
                        self::formatValue($value->default()),
                    );
                    $lines[] = '';
                }
            }

            $lines[] = '## Examples';
            $lines[] = '';

            foreach ($codeSamples as $index => $codeSample) {
                $heading = '### Example';

                if (1 < $sampleCount) {
                    $heading = \sprintf(
                        '%s %d',
                        $heading,
                        $index + 1,
                    );
                }

                if (\count($configurationOptions) > 0) {
                    if ($codeSample instanceof RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample) {
                        $heading = \sprintf(
                            '%s (with %s)',
                            $heading,
                            self::listOptionNames(\array_keys($codeSample->getConfiguration())),
                        );
                    } else {
                        $heading = \sprintf(
                            '%s (with default configuration)',
                            $heading,
                        );
                    }
                }

                $lines[] = $heading;
                $lines[] = '';

                $namespaceSegments = \explode(
                    '\\',
                    $rule->namespaceRelativeToNamespacePrefix(),
                );

                $className = \sprintf(
                    '%s\\%s::class',
                    $rule->namespaceRelativeToNamespacePrefix(),
                    $rule->shortName(),
                );

                $lines[] = '#### Configuration';
                $lines[] = '';
                $lines[] = '```php';
                $lines[] = '<?php';
                $lines[] = '';
                $lines[] = 'declare(strict_types=1);';
                $lines[] = '';
                $lines[] = \sprintf(
                    'use Ergebnis\Rector\Rules\%s;',
                    $namespaceSegments[0],
                );
                $lines[] = 'use Rector\Config;';
                $lines[] = '';

                if ($codeSample instanceof RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample) {
                    $configuration = self::exportValue(
                        $codeSample->getConfiguration(),
                        '',
                    );

                    $lines[] = \sprintf(
                        'return Config\RectorConfig::configure()->withConfiguredRule(%s, %s);',
                        $className,
                        $configuration,
                    );
                } else {
                    $lines[] = 'return Config\RectorConfig::configure()->withRules([';
                    $lines[] = \sprintf(
                        '    %s,',
                        $className,
                    );
                    $lines[] = ']);';
                }

                $lines[] = '```';
                $lines[] = '';

                $diff = $this->diff(
                    $codeSample->getBadCode(),
                    $codeSample->getGoodCode(),
                );

                $lines[] = '#### Changes';
                $lines[] = '';
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

                return \sprintf(
                    '[%s]',
                    \implode(
                        ', ',
                        $items,
                    ),
                );
            }

            return \sprintf(
                "'%s'",
                (string) $value,
            );
        }

        /**
         * @param list<int|string> $optionNames
         */
        private static function listOptionNames(array $optionNames): string
        {
            \sort($optionNames);

            $formatted = \array_map(static function ($optionName): string {
                return \sprintf(
                    '`%s`',
                    $optionName,
                );
            }, $optionNames);

            $last = \array_pop($formatted);

            if ([] === $formatted) {
                return (string) $last;
            }

            if (1 === \count($formatted)) {
                return \sprintf(
                    '%s and %s',
                    $formatted[0],
                    $last,
                );
            }

            return \sprintf(
                '%s, and %s',
                \implode(
                    ', ',
                    $formatted,
                ),
                $last,
            );
        }

        /**
         * @param mixed $value
         */
        private static function exportValue(
            $value,
            string $indentation
        ): string {
            if (\is_bool($value)) {
                if ($value) {
                    return 'true';
                }

                return 'false';
            }

            if (\is_array($value)) {
                if ([] === $value) {
                    return '[]';
                }

                $isList = \array_keys($value) === \range(
                    0,
                    \count($value) - 1,
                );

                $elements = [];

                $elementIndentation = \sprintf(
                    '%s    ',
                    $indentation,
                );

                foreach ($value as $key => $element) {
                    $exported = self::exportValue(
                        $element,
                        $elementIndentation,
                    );

                    if (!$isList) {
                        $exported = \sprintf(
                            '%s => %s',
                            self::exportValue(
                                $key,
                                $elementIndentation,
                            ),
                            $exported,
                        );
                    }

                    $elements[] = \sprintf(
                        '%s%s,',
                        $elementIndentation,
                        $exported,
                    );
                }

                return \sprintf(
                    "[\n%s\n%s]",
                    \implode(
                        "\n",
                        $elements,
                    ),
                    $indentation,
                );
            }

            if (\is_string($value)) {
                /**
                 * In a single-quoted string, a backslash only needs escaping before another backslash, before a single quote, or at the end.
                 */
                $escaped = \preg_replace(
                    '/\\\\(?=\\\\|\'|$)/',
                    '\\\\\\\\',
                    $value,
                );

                return \sprintf(
                    "'%s'",
                    \str_replace(
                        "'",
                        "\\'",
                        (string) $escaped,
                    ),
                );
            }

            return \var_export(
                $value,
                true,
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
                        $output[] = \sprintf(
                            ' %s',
                            $line,
                        );

                        break;

                    case Diff\Differ::REMOVED:
                        $output[] = \sprintf(
                            '-%s',
                            $line,
                        );

                        break;

                    case Diff\Differ::ADDED:
                        $output[] = \sprintf(
                            '+%s',
                            $line,
                        );

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
                    'Could not read "%s".%s',
                    $readmePath,
                    \PHP_EOL,
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

            $newContent = \sprintf(
                "%s\n\n%s\n%s",
                \substr(
                    $content,
                    0,
                    $beginPosition + \strlen($beginMarker),
                ),
                $rulesSection,
                \substr(
                    $content,
                    $endPosition,
                ),
            );

            $this->fileSystem->dumpFile(
                $readmePath,
                $newContent,
            );

            echo \sprintf(
                'Updated README.md%s',
                \PHP_EOL,
            );
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
                    $heading = \sprintf(
                        '%s\\%s',
                        $rule->namespaceRelativeToNamespacePrefix(),
                        $rule->shortName(),
                    );

                    $anchor = self::anchorFor($heading);

                    $lines[] = \sprintf(
                        '- [`%s`](#%s)',
                        $rule->className(),
                        $anchor,
                    );
                }
            }

            $lines[] = '';

            foreach ($rulesByNamespace as $namespace => $rules) {
                $lines[] = \sprintf(
                    '### %s',
                    $namespace,
                );
                $lines[] = '';

                foreach ($rules as $rule) {
                    $lines[] = \sprintf(
                        '#### `%s\\%s`',
                        $rule->namespaceRelativeToNamespacePrefix(),
                        $rule->shortName(),
                    );
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

                    $lines[] = \sprintf(
                        '💡 Find out more in the rule documentation for [`%s\\%s`](%s).',
                        $rule->namespaceRelativeToNamespacePrefix(),
                        $rule->shortName(),
                        $rule->docPath(),
                    );
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
