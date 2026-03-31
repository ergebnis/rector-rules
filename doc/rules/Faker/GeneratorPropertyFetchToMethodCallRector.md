# `Faker\GeneratorPropertyFetchToMethodCallRector`

Replaces references to deprecated properties of Faker\Generator with method calls.

## Examples

### Example

```diff
-$faker->address;
+$faker->address();
```
