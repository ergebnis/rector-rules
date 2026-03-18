<?php

namespace Example\Core;

use Example\Core\Bar;
use Example\Core\Bar\Baz;
use Example\Core\Quz;

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

namespace Example\Core;

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
