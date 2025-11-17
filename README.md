# rector-rules

[![Integrate](https://github.com/ergebnis/rector-rules/workflows/Integrate/badge.svg)](https://github.com/ergebnis/rector-rules/actions)
[![Merge](https://github.com/ergebnis/rector-rules/workflows/Merge/badge.svg)](https://github.com/ergebnis/rector-rules/actions)
[![Release](https://github.com/ergebnis/rector-rules/workflows/Release/badge.svg)](https://github.com/ergebnis/rector-rules/actions)
[![Renew](https://github.com/ergebnis/rector-rules/workflows/Renew/badge.svg)](https://github.com/ergebnis/rector-rules/actions)

[![Code Coverage](https://codecov.io/gh/ergebnis/rector-rules/branch/main/graph/badge.svg)](https://codecov.io/gh/ergebnis/rector-rules)

[![Latest Stable Version](https://poser.pugx.org/ergebnis/rector-rules/v/stable)](https://packagist.org/packages/ergebnis/rector-rules)
[![Total Downloads](https://poser.pugx.org/ergebnis/rector-rules/downloads)](https://packagist.org/packages/ergebnis/rector-rules)
[![Monthly Downloads](http://poser.pugx.org/ergebnis/rector-rules/d/monthly)](https://packagist.org/packages/ergebnis/rector-rules)

This project provides a [`composer`](https://getcomposer.org) package with rules for [`rector/rector`](https://github.com/rectorphp/rector).

## Installation

Run

```sh
composer require --dev ergebnis/rector-rules
```

## Rules

This project provides the following rules for [`rector/rector`](https://github.com/rectorphp/rector):

- [`Ergebnis\Rector\Rules\Arrays\SortAssociativeArrayByKeyRector`](https://github.com/ergebnis/rector-rules#arrayssortassociativearraybykeyrector)
- [`Ergebnis\Rector\Rules\Faker\GeneratorPropertyFetchToMethodCallRector`](https://github.com/ergebnis/rector-rules#fakergeneratorpropertyfetchtomethodcallrector)

### Arrays

#### `Arrays\SortAssociativeArrayByKeyRector`

This rule sorts associative arrays by key.

```diff
 <?php

 $data = [
+    'bar' => [
+        'quux' => 'quuz',
+        'quz' => 'qux',
+    ],
     'foo' => [
         'foo',
         'bar',
         'baz',
     ],
-    'bar' => [
-        'quz' => 'qux',
-        'quux' => 'quuz',
-    ],
 ];
```
##### Configuration

- `comparison_function`: the comparison function, one of ([`'strcasecmp'`](https://www.php.net/manual/en/function.strcasecmp.php), [`'strcmp'`](https://www.php.net/manual/en/function.strcmp.php), [`'strnatcasecmp'`](https://www.php.net/manual/en/function.strnatcasecmp.php), or [`'strnatcmp'`](https://www.php.net/manual/en/function.strnatcmp.php)), defaults to `'strcmp'`
- `direction`: the sorting direction (one of `'asc'`, `'desc'`), defaults to `asc`

### Faker

#### `Faker\GeneratorPropertyFetchToMethodCallRector`

This rule replaces references to deprecated properties of `Faker\Generator` with method calls.

```diff
 <?php

 use Faker\Factory;

 $faker = Factory::create();

- $faker->boolean;
+ $faker->boolean();
```

## Changelog

The maintainers of this project record notable changes to this project in a [changelog](CHANGELOG.md).

## Contributing

The maintainers of this project suggest following the [contribution guide](.github/CONTRIBUTING.md).

## Code of Conduct

The maintainers of this project ask contributors to follow the [code of conduct](https://github.com/ergebnis/.github/blob/main/CODE_OF_CONDUCT.md).

## General Support Policy

The maintainers of this project provide limited support.

You can support the maintenance of this project by [sponsoring @ergebnis](https://github.com/sponsors/ergebnis).

## PHP Version Support Policy

This project supports PHP versions with [active and security support](https://www.php.net/supported-versions.php).

The maintainers of this project add support for a PHP version following its initial release and drop support for a PHP version when it has reached the end of security support.

## Security Policy

This project has a [security policy](.github/SECURITY.md).

## License

This project uses the [MIT license](LICENSE.md).

## Social

Follow [@localheinz](https://twitter.com/intent/follow?screen_name=localheinz) and [@ergebnis](https://twitter.com/intent/follow?screen_name=ergebnis) on Twitter.
