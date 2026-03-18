<?php

namespace Example\Foo;

use Example\Foo\Bar;
use Example\Foo\Bar\Baz;
use Example\Foo\Quz;

final class ExampleService
{
    public function __construct(
        private Bar $bar,
        private Baz $baz,
        private Quz $quz,
    ) {
    }
}

?>
-----
<?php

namespace Example\Foo;

final class ExampleService
{
    public function __construct(
        private Bar $bar,
        private Bar\Baz $baz,
        private Quz $quz,
    ) {
    }
}

?>
