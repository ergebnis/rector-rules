# `Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector`

Removes named arguments for single-parameter function and method calls.

## Examples

### Example

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Expressions;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector::class,
]);
```

#### Changes

```diff
-strlen(string: 'hello');
+strlen('hello');
```
