<?php

namespace Example\App;

use Example\Core;
use Example\Core\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    public function dashboard(Core $core): ResponseInterface
    {
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
    public function dashboard(Core $core): ResponseInterface
    {
    }
}

?>
