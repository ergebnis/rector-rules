# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

For a full diff see [`1.4.0...main`][1.4.0...main].

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

[fd198f0...0.1.0]: https://github.com/ergebnis/rector-rules/compare/fd198f0...0.1.0
[0.1.0...0.2.0]: https://github.com/ergebnis/rector-rules/compare/0.1.0...0.2.0
[0.2.0...0.3.0]: https://github.com/ergebnis/rector-rules/compare/0.2.0...0.3.0
[0.3.0...0.4.0]: https://github.com/ergebnis/rector-rules/compare/0.3.0...0.4.0
[0.4.0...1.0.0]: https://github.com/ergebnis/rector-rules/compare/0.4.0...1.0.0
[1.0.0...1.0.1]: https://github.com/ergebnis/rector-rules/compare/1.0.0...1.0.1
[1.0.1...1.1.0]: https://github.com/ergebnis/rector-rules/compare/1.0.1...1.1.0
[1.1.0...1.2.0]: https://github.com/ergebnis/rector-rules/compare/1.1.0...1.2.0
[1.2.0...1.3.0]: https://github.com/ergebnis/rector-rules/compare/1.2.0...1.3.0
[1.3.0...main]: https://github.com/ergebnis/rector-rules/compare/1.3.0...main

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

[@localheinz]: https://github.com/localheinz
