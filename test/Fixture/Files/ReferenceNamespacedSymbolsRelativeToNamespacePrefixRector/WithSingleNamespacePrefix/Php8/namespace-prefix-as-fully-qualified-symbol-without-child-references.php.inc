<?php

namespace Example\App;

use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        $core = new \Example\Core();
    }
}

?>
-----
<?php

namespace Example\App;

use Psr\Http\Message\ResponseInterface;
use Example\Core;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        $core = new Core();
    }
}

?>
