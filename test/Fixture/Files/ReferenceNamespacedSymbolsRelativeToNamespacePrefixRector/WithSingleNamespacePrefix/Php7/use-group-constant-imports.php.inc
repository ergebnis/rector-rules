<?php

namespace Example\App;

use Example\Core\Controller\AbstractController;
use const Example\Core\Config\{DEFAULT_TIMEOUT, MAX_RETRIES};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    public function dashboard(): ResponseInterface
    {
        $timeout = DEFAULT_TIMEOUT;
        $retries = MAX_RETRIES;
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    public function dashboard(): ResponseInterface
    {
        $timeout = Core\Config\DEFAULT_TIMEOUT;
        $retries = Core\Config\MAX_RETRIES;
    }
}

?>
