<?php

namespace Example\App;

use Example\Core\Controller\AbstractController as BaseController;
use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends BaseController
{
    #[Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
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
    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
