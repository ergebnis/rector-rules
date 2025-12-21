# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

For a full diff see [`1.8.0...main`][1.8.0...main].

### Changed

- Adjusted `Rules\Arrays\SortAssociativeArrayByKeyRector` to resolve values for keys referring to `::class` pseudo constants from the original name instead of the fully-qualified class name ([#265]), by [@localheinz]

## [`1.8.0`][1.8.0]

For a full diff see [`1.7.0...1.8.0`][1.7.0...1.8.0].

### Changed

- Adjusted `Rules\Arrays\SortAssociativeArrayByKeyRector` to resolve values for keys referring to `::class` pseudo constants ([#258]), by [@localheinz]

## [`1.7.0`][1.7.0]

For a full diff see [`1.6.0...1.7.0`][1.6.0...1.7.0].

### Added

- Added `Rules\Faker\GeneratorPropertyFetchToMethodCallRector` to allow replacing references to deprecated properties of `Faker\Generator` with method calls ([#249]), by [@localheinz]

### Changed

- Required `rector/rector:^2.1.3` ([#248]), by [@localheinz]

## [`1.6.0`][1.6.0]

For a full diff see [`1.5.1...1.6.0`][1.5.1...1.6.0].

### Changed

- Allowed installation on PHP 8.5 ([#225]), by [@localheinz]

## [`1.5.1`][1.5.1]

For a full diff see [`1.5.0...1.5.1`][1.5.0...1.5.1].

### Fixed

- Removed an unnecessary condition in `Rules\Arrays\SortAssociativeArrayByKeyRector` ([#210]), by [@localheinz]
- Reverted back to using `strcmp` as default comparison function in `Rules\Arrays\SortAssociativeArrayByKeyRector` ([#216]), by [@localheinz]

## [`1.5.0`][1.5.0]

For a full diff see [`1.4.0...1.5.0`][1.4.0...1.5.0].

### Changed

- Allowed configuring `Rules\Arrays\SortAssociativeArrayByKeyRector` ([#210]), by [@localheinz]

## [`1.4.0`][1.4.0]

For a full diff see [`1.3.0...1.4.0`][1.3.0...1.4.0].

### Changed

- Required `rector/rector:^2.0.11` ([#199]), by [@localheinz]
- Adjusted `Rules\Arrays\SortAssociativeArrayByKeyRector` to stop ignoring associative arrays used in sub-classes of `PHPUnit\Framework\TestCase` ([#200]), by [@localheinz]

## [`1.3.0`][1.3.0]

For a full diff see [`1.2.0...1.3.0`][1.2.0...1.3.0].

### Added

- Added support for `rector/rector:^2.0.0` ([#171]), by [@localheinz]

## [`1.2.0`][1.2.0]

For a full diff see [`1.1.0...1.2.0`][1.1.0...1.2.0].

### Added

- Added support for PHP 8.4 ([#162]), by [@localheinz]

### Changed

- Required `rector/rector:^1.0.0` ([#161]), by [@localheinz]

## [`1.1.0`][1.1.0]

For a full diff see [`1.0.1...1.1.0`][1.0.1...1.1.0].

### Changed

- Allowed installation on PHP 8.4 ([#139]), by [@localheinz]

## [`1.0.1`][1.0.1]

For a full diff see [`1.0.0...1.0.1`][1.0.0...1.0.1].

### Fixed

- Allowed installation of `rector/rector:~0.19.2` ([#77]), by [@localheinz]

## [`1.0.0`][1.0.0]

For a full diff see [`0.4.0...1.0.0`][0.4.0...1.0.0].

### Changed

- Required `rector/rector:^1.0.0` ([#76]), by [@localheinz]

## [`0.4.0`][0.4.0]

For a full diff see [`0.3.0...0.4.0`][0.3.0...0.4.0].

### Changed

- Required `rector/rector:~0.19.2` ([#64]), by [@localheinz]
- Allowed installation of `nikic/php-parser:^5.0.0` ([#65]), by [@localheinz]

## [`0.3.0`][0.3.0]

For a full diff see [`0.2.0...0.3.0`][0.2.0...0.3.0].

### Added

- Added support for PHP 8.0 ([#36]), by [@localheinz]
- Added support for PHP 7.4 ([#37]), by [@localheinz]

## [`0.2.0`][0.2.0]

For a full diff see [`0.1.0...0.2.0`][0.1.0...0.2.0].

### Added

- Added support for PHP 8.3 ([#34]), by [@localheinz]

### Changed

- Updated `rector/rector ([#3]), by [@localheinz]

## [`0.1.0`][0.1.0]

For a full diff see [`fd198f0...0.1.0`][fd198f0...0.1.0].

### Added

- Added `Rules\Arrays\SortAssociativeArrayByKeyRector` ([#1]), by [@localheinz]

[0.1.0]: https://github.com/ergebnis/rector-rules/releases/tag/0.1.0
[0.2.0]: https://github.com/ergebnis/rector-rules/releases/tag/0.2.0
[0.3.0]: https://github.com/ergebnis/rector-rules/releases/tag/0.3.0
[0.4.0]: https://github.com/ergebnis/rector-rules/releases/tag/0.4.0
[1.0.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.0.0
[1.0.1]: https://github.com/ergebnis/rector-rules/releases/tag/1.0.1
[1.1.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.1.0
[1.2.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.2.0
[1.3.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.3.0
[1.4.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.4.0
[1.5.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.5.0
[1.5.1]: https://github.com/ergebnis/rector-rules/releases/tag/1.5.1
[1.6.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.6.0
[1.7.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.7.0
[1.8.0]: https://github.com/ergebnis/rector-rules/releases/tag/1.8.0

[fd198f0...0.1.0]: https://github.com/ergebnis/rector-rules/compare/fd198f0...0.1.0
[0.1.0...0.2.0]: https://github.com/ergebnis/rector-rules/compare/0.1.0...0.2.0
[0.2.0...0.3.0]: https://github.com/ergebnis/rector-rules/compare/0.2.0...0.3.0
[0.3.0...0.4.0]: https://github.com/ergebnis/rector-rules/compare/0.3.0...0.4.0
[0.4.0...1.0.0]: https://github.com/ergebnis/rector-rules/compare/0.4.0...1.0.0
[1.0.0...1.0.1]: https://github.com/ergebnis/rector-rules/compare/1.0.0...1.0.1
[1.0.1...1.1.0]: https://github.com/ergebnis/rector-rules/compare/1.0.1...1.1.0
[1.1.0...1.2.0]: https://github.com/ergebnis/rector-rules/compare/1.1.0...1.2.0
[1.2.0...1.3.0]: https://github.com/ergebnis/rector-rules/compare/1.2.0...1.3.0
[1.3.0...1.4.0]: https://github.com/ergebnis/rector-rules/compare/1.3.0...1.4.0
[1.4.0...1.5.0]: https://github.com/ergebnis/rector-rules/compare/1.4.0...1.5.0
[1.5.0...1.5.1]: https://github.com/ergebnis/rector-rules/compare/1.5.0...1.5.1
[1.5.1...1.6.0]: https://github.com/ergebnis/rector-rules/compare/1.5.1...1.6.0
[1.6.0...1.7.0]: https://github.com/ergebnis/rector-rules/compare/1.6.0...1.7.0
[1.7.0...1.8.0]: https://github.com/ergebnis/rector-rules/compare/1.7.0...1.8.0
[1.8.0...main]: https://github.com/ergebnis/rector-rules/compare/1.8.0...main

[#1]: https://github.com/ergebnis/rector-rules/pull/1
[#3]: https://github.com/ergebnis/rector-rules/pull/3
[#34]: https://github.com/ergebnis/rector-rules/pull/34
[#36]: https://github.com/ergebnis/rector-rules/pull/36
[#37]: https://github.com/ergebnis/rector-rules/pull/37
[#64]: https://github.com/ergebnis/rector-rules/pull/64
[#65]: https://github.com/ergebnis/rector-rules/pull/65
[#76]: https://github.com/ergebnis/rector-rules/pull/76
[#77]: https://github.com/ergebnis/rector-rules/pull/77
[#139]: https://github.com/ergebnis/rector-rules/pull/139
[#161]: https://github.com/ergebnis/rector-rules/pull/161
[#162]: https://github.com/ergebnis/rector-rules/pull/162
[#171]: https://github.com/ergebnis/rector-rules/pull/171
[#199]: https://github.com/ergebnis/rector-rules/pull/199
[#200]: https://github.com/ergebnis/rector-rules/pull/200
[#210]: https://github.com/ergebnis/rector-rules/pull/210
[#216]: https://github.com/ergebnis/rector-rules/pull/216
[#225]: https://github.com/ergebnis/rector-rules/pull/225
[#248]: https://github.com/ergebnis/rector-rules/pull/248
[#249]: https://github.com/ergebnis/rector-rules/pull/249
[#258]: https://github.com/ergebnis/rector-rules/pull/258
[#265]: https://github.com/ergebnis/rector-rules/pull/265

[@localheinz]: https://github.com/localheinz
