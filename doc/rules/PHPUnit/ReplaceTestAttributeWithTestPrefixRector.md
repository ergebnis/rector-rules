# `PHPUnit\ReplaceTestAttributeWithTestPrefixRector`

Replaces #[Test] attributes with test method prefixes.

## Examples

### Example

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
