# `Expressions\Matches\SortMatchArmsByConditionalRector`

Sorts match arms by conditional when the conditionals are all integers or all strings.

## Options

### `comparison_function`

The comparison function to use for sorting conditionals when conditionals are strings.

- type: `string`
- allowed values: `'strcasecmp'`, `'strcmp'`, `'strnatcasecmp'`, `'strnatcmp'`
- default value: `'strcmp'`

### `direction`

The sorting direction.

- type: `string`
- allowed values: `'asc'`, `'desc'`
- default value: `'asc'`

## Examples

### Example 1

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Expressions\Matches\SortMatchArmsByConditionalRector::class,
]);
```

#### Changes

```diff
 match ($status) {
-    'pending' => handlePending(),
     'active' => handleActive(),
     'closed' => handleClosed(),
+    'pending' => handlePending(),
 };
```

### Example 2

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Expressions\Matches\SortMatchArmsByConditionalRector::class,
]);
```

#### Changes

```diff
 match ($status) {
+    'active' => handleActive(),
+    'closed' => handleClosed(),
     'pending' => handlePending(),
     default => handleUnknown(),
-    'active' => handleActive(),
-    'closed' => handleClosed(),
 };
```

### Example 3

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Expressions\Matches\SortMatchArmsByConditionalRector::class,
]);
```

#### Changes

```diff
 match (true) {
-    Zebra::class => handleZebra(),
     Apple::class => handleApple(),
     Mango::class => handleMango(),
+    Zebra::class => handleZebra(),
 };
```

### Example 4

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Expressions\Matches\SortMatchArmsByConditionalRector::class,
]);
```

#### Changes

```diff
 match (true) {
+    Apple::class => handleApple(),
+    Mango::class => handleMango(),
     Zebra::class => handleZebra(),
     default => handleUnknown(),
-    Apple::class => handleApple(),
-    Mango::class => handleMango(),
 };
```

### Example 5

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Expressions\Matches\SortMatchArmsByConditionalRector::class,
]);
```

#### Changes

```diff
 match ($code) {
+    200 => 'OK',
     404 => 'Not Found',
+    500 => 'Server Error',
     default => 'Unknown',
-    200 => 'OK',
-    500 => 'Server Error',
 };
```

### Example 6

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Matches\SortMatchArmsByConditionalRector::class, [
    'direction' => 'desc',
]);
```

#### Changes

```diff
 match ($status) {
-    'active' => handleActive(),
     'pending' => handlePending(),
     'closed' => handleClosed(),
+    'active' => handleActive(),
 };
```

### Example 7

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Matches\SortMatchArmsByConditionalRector::class, [
    'comparison_function' => 'strcasecmp',
]);
```

#### Changes

```diff
 match ($status) {
-    'Pending' => handlePending(),
     'active' => handleActive(),
     'Closed' => handleClosed(),
+    'Pending' => handlePending(),
 };
```

### Example 8

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Matches\SortMatchArmsByConditionalRector::class, [
    'comparison_function' => 'strnatcmp',
]);
```

#### Changes

```diff
 match ($status) {
+    'Status' => handleBase(),
+    'Status2' => handle2(),
     'Status10' => handle10(),
-    'Status2' => handle2(),
-    'Status' => handleBase(),
 };
```

### Example 9

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withConfiguredRule(Expressions\Matches\SortMatchArmsByConditionalRector::class, [
    'comparison_function' => 'strnatcasecmp',
]);
```

#### Changes

```diff
 match ($status) {
+    'Status' => handleBase(),
+    'status2' => handle2(),
     'Status10' => handle10(),
-    'status2' => handle2(),
-    'Status' => handleBase(),
 };
```
