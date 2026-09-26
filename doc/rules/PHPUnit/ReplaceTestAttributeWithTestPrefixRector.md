# `PHPUnit\ReplaceTestAttributeWithTestPrefixRector`

Replaces #[Test] attributes with test method prefixes.

## Examples

### Example

#### Configuration

```php
<?php

declare(strict_types=1);

use Ergebnis\Rector\Rules\PHPUnit;
use Rector\Config;

return Config\RectorConfig::configure()->withRules([
    PHPUnit\ReplaceTestAttributeWithTestPrefixRector::class,
]);
```

#### Changes

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
