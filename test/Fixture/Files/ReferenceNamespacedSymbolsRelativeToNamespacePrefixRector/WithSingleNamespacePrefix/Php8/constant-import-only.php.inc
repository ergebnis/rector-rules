<?php

namespace Example\App;

use const Example\Core\Config\DEFAULT_TIMEOUT;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        $timeout = DEFAULT_TIMEOUT;
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        $timeout = Core\Config\DEFAULT_TIMEOUT;
    }
}

?>
