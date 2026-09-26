# `Expressions\Arrays\SortAssociativeArrayByKeyRector`

Sorts associative arrays by key.

## Options

### `comparison_function`

The comparison function to use for sorting keys.

- type: `string`
- allowed values: `'strcasecmp'`, `'strcmp'`, `'strnatcasecmp'`, `'strnatcmp'`
- default value: `'strcmp'`

### `direction`

The sorting direction.

- type: `string`
- allowed values: `'asc'`, `'desc'`
- default value: `'asc'`

## Examples

### Example 1 (with default configuration)

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Expressions\Arrays\SortAssociativeArrayByKeyRector::class,
]);
```

#### Changes

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

### Example 2 (with `direction`)

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Arrays\SortAssociativeArrayByKeyRector::class, [
    'direction' => 'desc',
]);
```

#### Changes

```diff
 $data = [
-    'bar' => [
-        'quux' => 'quuz',
-        'quz' => 'qux',
-    ],
     'foo' => [
         'foo',
         'bar',
         'baz',
+    ],
+    'bar' => [
+        'quz' => 'qux',
+        'quux' => 'quuz',
     ],
 ];
```

### Example 3 (with `comparison_function`)

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Arrays\SortAssociativeArrayByKeyRector::class, [
    'comparison_function' => 'strcasecmp',
]);
```

#### Changes

```diff
 $data = [
+    'Quux' => 'quuz',
+    'quux' => 'quuz',
     'Quz' => 'qux',
     'QuZ' => 'qux',
     'quz' => 'qux',
-    'Quux' => 'quuz',
-    'quux' => 'quuz',
 ];
```

### Example 4 (with `comparison_function`)

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Arrays\SortAssociativeArrayByKeyRector::class, [
    'comparison_function' => 'strnatcmp',
]);
```

#### Changes

```diff
 $data = [
+    'Quux' => 'quuz',
+    'Quz' => 'qux',
+    'Quz2' => 'qux',
     'Quz10' => 'qux',
-    'Quz2' => 'qux',
-    'Quz' => 'qux',
-    'Quux' => 'quuz',
 ];
```

### Example 5 (with `comparison_function`)

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Arrays\SortAssociativeArrayByKeyRector::class, [
    'comparison_function' => 'strnatcasecmp',
]);
```

#### Changes

```diff
 $data = [
-    'Quz10' => 'qux',
-    'Quz2' => 'qux',
+    'Quux' => 'quuz',
+    'quux' => 'quuz',
     'Quz' => 'qux',
     'QuZ' => 'qux',
     'quz' => 'qux',
-    'Quux' => 'quuz',
-    'quux' => 'quuz',
+    'Quz2' => 'qux',
+    'Quz10' => 'qux',
 ];
```
