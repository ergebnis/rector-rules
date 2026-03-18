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

use Example\Core;

final class ExampleService
{
    /**
     * @var Core\Bar
     */
    private $bar;

    /**
     * @var Core\Bar\Baz
     */
    private $baz;

    /**
     * @var Core\Quz
     */
    private $quz;

    public function __construct(
        Core\Bar $bar,
        Core\Bar\Baz $baz,
        Core\Quz $quz
    ) {
        $this->bar = $bar;
        $this->baz = $baz;
        $this->quz = $quz;
    }
}

?>
