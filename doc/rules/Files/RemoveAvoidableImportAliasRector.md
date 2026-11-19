# `Files\RemoveAvoidableImportAliasRector`

Removes aliases from imports of classes and namespaces when the imported name is not otherwise in use.

## Examples

### Example

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\Files;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    Files\RemoveAvoidableImportAliasRector::class,
]);
```

#### Changes

```diff
 namespace App\Http;
 
-use App\Controller as Foo;
+use App\Controller;
 
 final class Router
 {
-    public function dashboard(): Foo\DashboardController
+    public function dashboard(): Controller\DashboardController
     {
     }
 }
```
