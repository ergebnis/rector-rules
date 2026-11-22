<?php

namespace App;

use Symfony\Component as Foo;

final class RequestHolder
{
    /**
     * @var Foo\HttpFoundation\Request
     */
    private $request;
}

?>
-----
<?php

namespace App;

use Symfony\Component as Foo;
use Symfony\Component\HttpFoundation;

final class RequestHolder
{
    /**
     * @var HttpFoundation\Request
     */
    private $request;
}

?>
