# `Arrays\SortAssociativeArrayByKeyRector`

Sorts associative arrays by key.

## Configuration

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

### Example 1

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

### Example 2

Configuration:

- `direction`: `'desc'`

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

### Example 3

Configuration:

- `comparison_function`: `'strcasecmp'`

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

### Example 4

Configuration:

- `comparison_function`: `'strnatcmp'`

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

### Example 5

Configuration:

- `comparison_function`: `'strnatcasecmp'`

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
