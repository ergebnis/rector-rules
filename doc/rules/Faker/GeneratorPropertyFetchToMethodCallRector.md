# `Faker\GeneratorPropertyFetchToMethodCallRector`

Replaces references to deprecated properties of Faker\Generator with method calls.

## Examples

### Example

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Faker;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Faker\GeneratorPropertyFetchToMethodCallRector::class,
]);
```

#### Changes

```diff
-$faker->address;
+$faker->address();
```
