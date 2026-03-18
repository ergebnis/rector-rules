<?php

namespace Example\Core\Bar;

use Example\Core\Bar\Baz;
use Example\Core\Bar\Baz\Qux;
use Example\Core\Quz;

final class ExampleService
{
    public function __construct(
        private Baz $baz,
        private Qux $qux,
        private Quz $quz,
    ) {
    }
}

?>
-----
<?php

namespace Example\Core\Bar;

use Example\Core;

final class ExampleService
{
    public function __construct(
        private Core\Bar\Baz $baz,
        private Core\Bar\Baz\Qux $qux,
        private Core\Quz $quz,
    ) {
    }
}

?>
