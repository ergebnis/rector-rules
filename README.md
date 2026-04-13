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

<!-- BEGIN RULES -->

This project provides the following rules for [`rector/rector`](https://github.com/rectorphp/rector):

- [`Ergebnis\Rector\Rules\Expressions\Arrays\SortAssociativeArrayByKeyRector`](#expressionsarrayssortassociativearraybykeyrector)
- [`Ergebnis\Rector\Rules\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector`](#expressionscalllikesremovenamedargumentforsingleparameterrector)
- [`Ergebnis\Rector\Rules\Expressions\Matches\SortMatchArmsByConditionalRector`](#expressionsmatchessortmatcharmsbyconditionalrector)
- [`Ergebnis\Rector\Rules\Faker\GeneratorPropertyFetchToMethodCallRector`](#fakergeneratorpropertyfetchtomethodcallrector)
- [`Ergebnis\Rector\Rules\Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector`](#filesreferencenamespacedsymbolsrelativetonamespaceprefixrector)
- [`Ergebnis\Rector\Rules\PHPUnit\ReplaceTestAttributeWithTestPrefixRector`](#phpunitreplacetestattributewithtestprefixrector)

### Expressions\Arrays

#### `Expressions\Arrays\SortAssociativeArrayByKeyRector`

Sorts associative arrays by key.

```diff
 $data = [
+    'bar' => [
+        'quux' => 'quuz',
+        'quz' => 'qux',
+    ],
     'foo' => [
         'foo',
         'bar',
         'baz',
-    ],
-    'bar' => [
-        'quz' => 'qux',
-        'quux' => 'quuz',
     ],
 ];
```

💡 Find out more in the rule documentation for [`Expressions\Arrays\SortAssociativeArrayByKeyRector`](doc/rules/Expressions/Arrays/SortAssociativeArrayByKeyRector.md).

### Expressions\CallLikes

#### `Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector`

Removes named arguments for single-parameter function and method calls.

```diff
-strlen(string: 'hello');
+strlen('hello');
```

💡 Find out more in the rule documentation for [`Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector`](doc/rules/Expressions/CallLikes/RemoveNamedArgumentForSingleParameterRector.md).

### Expressions\Matches

#### `Expressions\Matches\SortMatchArmsByConditionalRector`

Sorts match arms by conditional when the conditionals are all integers or all strings.

```diff
 match ($status) {
-    'pending' => handlePending(),
     'active' => handleActive(),
     'closed' => handleClosed(),
+    'pending' => handlePending(),
 };
```

💡 Find out more in the rule documentation for [`Expressions\Matches\SortMatchArmsByConditionalRector`](doc/rules/Expressions/Matches/SortMatchArmsByConditionalRector.md).

### Faker

#### `Faker\GeneratorPropertyFetchToMethodCallRector`

Replaces references to deprecated properties of Faker\Generator with method calls.

```diff
-$faker->address;
+$faker->address();
```

💡 Find out more in the rule documentation for [`Faker\GeneratorPropertyFetchToMethodCallRector`](doc/rules/Faker/GeneratorPropertyFetchToMethodCallRector.md).

### Files

#### `Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector`

Replaces references to namespaced symbols (classes, functions, constants) whose fully-qualified name starts with a namespace prefix so they are relative to that prefix.

```diff
-use Foo\Bar;
-use Foo\Bar\Baz\Qux;
+use Foo\Bar\Baz;
 
-new Bar\Baz\Qux\Quuz();
-new Qux\Quuz\Grauply();
+new Baz\Qux\Quuz();
+new Baz\Qux\Quuz\Grauply();
```

💡 Find out more in the rule documentation for [`Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector`](doc/rules/Files/ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector.md).

### PHPUnit

#### `PHPUnit\ReplaceTestAttributeWithTestPrefixRector`

Replaces #[Test] attributes with test method prefixes.

```diff
 use PHPUnit\Framework;
 
 final class SomeTest extends Framework\TestCase
 {
-    #[Framework\Attributes\Test]
-    public function onePlusOneShouldBeTwo(): void
+    public function testOnePlusOneShouldBeTwo(): void
     {
         self::assertSame(2, 1 + 1);
     }
 }
```

💡 Find out more in the rule documentation for [`PHPUnit\ReplaceTestAttributeWithTestPrefixRector`](doc/rules/PHPUnit/ReplaceTestAttributeWithTestPrefixRector.md).

<!-- END RULES -->

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
