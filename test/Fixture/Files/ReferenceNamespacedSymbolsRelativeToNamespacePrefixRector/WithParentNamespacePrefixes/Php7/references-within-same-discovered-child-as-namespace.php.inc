<?php

namespace Example\Foo;

use Example\Foo\Bar;
use Example\Foo\Bar\Baz;
use Example\Foo\Quz;

final class ExampleService
{
    /**
     * @var Bar
     */
    private $bar;

    /**
     * @var Baz
     */
    private $baz;

    /**
     * @var Quz
     */
    private $quz;

    public function __construct(
        Bar $bar,
        Baz $baz,
        Quz $quz
    ) {
        $this->bar = $bar;
        $this->baz = $baz;
        $this->quz = $quz;
    }
}

?>
-----
<?php

namespace Example\Foo;

final class ExampleService
{
    /**
     * @var Bar
     */
    private $bar;

    /**
     * @var Bar\Baz
     */
    private $baz;

    /**
     * @var Quz
     */
    private $quz;

    public function __construct(
        Bar $bar,
        Bar\Baz $baz,
        Quz $quz
    ) {
        $this->bar = $bar;
        $this->baz = $baz;
        $this->quz = $quz;
    }
}

?>
