# `Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector`

Removes named arguments for single-parameter function and method calls.

## Examples

### Example

```diff
-strlen(string: 'hello');
+strlen('hello');
```
