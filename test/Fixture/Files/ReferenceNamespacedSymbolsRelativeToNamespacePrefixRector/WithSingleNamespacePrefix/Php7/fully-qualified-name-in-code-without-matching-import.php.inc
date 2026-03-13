<?php

namespace Example\App;

use Psr\Http\Message\ResponseInterface;

final class ExampleController
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

use Psr\Http\Message\ResponseInterface;
use Example\Core;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        $controller = new Core\Controller\FrontController();
        $other = new \Other\Vendor\Service();
    }
}

?>
