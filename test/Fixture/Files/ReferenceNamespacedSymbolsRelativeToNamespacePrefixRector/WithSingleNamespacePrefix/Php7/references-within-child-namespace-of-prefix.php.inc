<?php

namespace Example\Core\Bar;

use Example\Core\Bar\Baz;
use Example\Core\Bar\Baz\Qux;
use Example\Core\Quz;

final class ExampleService
{
    /**
     * @var Baz
     */
    private $baz;

    /**
     * @var Qux
     */
    private $qux;

    /**
     * @var Quz
     */
    private $quz;

    public function __construct(
        Baz $baz,
        Qux $qux,
        Quz $quz
    ) {
        $this->baz = $baz;
        $this->qux = $qux;
        $this->quz = $quz;
    }
}

?>
-----
<?php

namespace Example\Core\Bar;

use Example\Core;

final class ExampleService
{
    /**
     * @var Core\Bar\Baz
     */
    private $baz;

    /**
     * @var Core\Bar\Baz\Qux
     */
    private $qux;

    /**
     * @var Core\Quz
     */
    private $quz;

    public function __construct(
        Core\Bar\Baz $baz,
        Core\Bar\Baz\Qux $qux,
        Core\Quz $quz
    ) {
        $this->baz = $baz;
        $this->qux = $qux;
        $this->quz = $quz;
    }
}

?>
