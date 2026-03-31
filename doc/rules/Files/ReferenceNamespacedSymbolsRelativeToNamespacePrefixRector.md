# `Files\ReferenceNamespacedSymbolsRelativeToNamespacePrefixRector`

Replaces references to namespaced symbols (classes, functions, constants) whose fully-qualified name starts with a namespace prefix so they are relative to that prefix.

## Configuration

### `discoverNamespacePrefixes`

Automatically discover namespace prefixes by scanning the file's references and extracting their first segment.

- type: `bool`
- default value: `false`

### `forceRelativeReferences`

Force references to be expressed relative to the namespace prefix even when the file namespace matches the prefix.

- type: `bool`
- default value: `false`

### `namespacePrefixes`

A list of namespace prefixes to consolidate.

- type: `list<string>`
- default value: `[]`

### `parentNamespacePrefixes`

A list of parent namespace prefixes for automatic discovery of namespace prefixes per file.

- type: `list<string>`
- default value: `[]`

## Examples

### Example 1

Configuration:

- `namespacePrefixes`: `['Foo\Bar\Baz']`

```diff
-use Foo\Bar;
-use Foo\Bar\Baz\Qux;
+use Foo\Bar\Baz;
 
-new Bar\Baz\Qux\Quuz();
-new Qux\Quuz\Grauply();
+new Baz\Qux\Quuz();
+new Baz\Qux\Quuz\Grauply();
```

### Example 2

Configuration:

- `namespacePrefixes`: `['Example\Core\Routing', 'Example\Domain', 'Psr\Http']`

```diff
 namespace Example\App;
 
-use Example\Core\Routing\Attribute\Route;
-use Example\Domain\UserRepository;
-use Psr\Http\Message\ResponseInterface;
+use Example\Core\Routing;
+use Example\Domain;
+use Psr\Http;
 
 final class ExampleController
 {
-    private UserRepository $userRepository;
+    private Domain\UserRepository $userRepository;
 
-    #[Route(path: '/example', name: 'example')]
-    public function dashboard(): ResponseInterface
+    #[Routing\Attribute\Route(path: '/example', name: 'example')]
+    public function dashboard(): Http\Message\ResponseInterface
     {
     }
 }
```

### Example 3

Configuration:

- `parentNamespacePrefixes`: `['Example']`

```diff
 namespace Example\App;
 
-use Example\Core\Controller\AbstractController;
+use Example\Core;
 
-final class ExampleController extends AbstractController
+final class ExampleController extends Core\Controller\AbstractController
 {
 }
```

### Example 4

Configuration:

- `namespacePrefixes`: `['Example\Core\Routing']`
- `parentNamespacePrefixes`: `['Example']`

```diff
 namespace Example\App;
 
-use Example\Core\Controller\AbstractController;
-use Example\Core\Routing\Attribute\Route;
+use Example\Core;
+use Example\Core\Routing;
 
-final class ExampleController extends AbstractController
+final class ExampleController extends Core\Controller\AbstractController
 {
-    #[Route(path: '/example', name: 'example')]
+    #[Routing\Attribute\Route(path: '/example', name: 'example')]
     public function dashboard()
     {
     }
 }
```

### Example 5

Configuration:

- `namespacePrefixes`: `['Example\Core\Routing', 'Example\Core', 'Example\Core\Caching\Redis']`

```diff
 namespace Example\App;
 
-use Example\Core\Caching\Redis\Connection;
-use Example\Core\Controller\AbstractController;
-use Example\Core\Routing\Attribute\Route;
+use Example\Core\Caching\Redis;
+use Example\Core;
+use Example\Core\Routing;
 
-final class ExampleController extends AbstractController
+final class ExampleController extends Core\Controller\AbstractController
 {
-    #[Route(path: '/example', name: 'example')]
-    #[Connection(host: 'localhost')]
+    #[Routing\Attribute\Route(path: '/example', name: 'example')]
+    #[Redis\Connection(host: 'localhost')]
     public function dashboard()
     {
     }
 }
```

### Example 6

Configuration:

- `forceRelativeReferences`: `true`
- `namespacePrefixes`: `['Example\Core']`

```diff
 namespace Example\Core\Bar;
 
-use Example\Core\Bar\Baz;
-use Example\Core\Bar\Baz\Qux;
-use Example\Core\Quz;
+use Example\Core;
 
 final class ExampleService
 {
     public function __construct(
-        private Baz $baz,
-        private Qux $qux,
-        private Quz $quz,
+        private Core\Bar\Baz $baz,
+        private Core\Bar\Baz\Qux $qux,
+        private Core\Quz $quz,
     ) {
     }
 }
```

### Example 7

Configuration:

- `discoverNamespacePrefixes`: `true`

```diff
 namespace App;
 
-use Ramsey\Uuid\Uuid;
-use Symfony\Component\HttpFoundation\Request;
-use Symfony\Component\HttpFoundation\Response;
+use Ramsey\Uuid;
+use Symfony\Component;
 
 final class Kernel
 {
-    public function handle(Request $request): Response
+    public function handle(Component\HttpFoundation\Request $request): Component\HttpFoundation\Response
     {
-        $id = Uuid::uuid4();
+        $id = Uuid\Uuid::uuid4();
     }
 }
```
