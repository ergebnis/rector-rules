<?php

namespace Example\App;

use Example\Core\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Example\Core\Security\Attribute\IsGranted;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    #[Route(path: '/example', name: 'example')]
    #[IsGranted('example.permission')]
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

final class ExampleController extends Core\AbstractController
{
    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    #[Core\Security\Attribute\IsGranted('example.permission')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
