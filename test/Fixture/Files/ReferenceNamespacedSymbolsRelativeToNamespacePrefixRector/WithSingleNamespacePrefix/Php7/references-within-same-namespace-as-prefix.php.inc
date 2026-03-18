<?php

namespace Example\Core;

use Example\Core\Bar;
use Example\Core\Bar\Baz;
use Example\Core\Quz;

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

namespace Example\Core;

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
