<?php

namespace Example\App;

use Example\Core\{Controller\AbstractController, Routing\Attribute\Route, Security\Attribute\IsGranted};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    #[Route(path: '/example', name: 'example')]
    #[IsGranted(attribute: 'example.permission')]
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
    #[Core\Security\Attribute\IsGranted(attribute: 'example.permission')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
