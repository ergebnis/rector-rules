<?php

namespace Example\App;

use Example\Core\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    public function dashboard(): ResponseInterface
    {
        $controller = new \Example\Core\Controller\FrontController();
        $other = new \Other\Vendor\Service();
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
        $controller = new Core\Controller\FrontController();
        $other = new \Other\Vendor\Service();
    }
}

?>
