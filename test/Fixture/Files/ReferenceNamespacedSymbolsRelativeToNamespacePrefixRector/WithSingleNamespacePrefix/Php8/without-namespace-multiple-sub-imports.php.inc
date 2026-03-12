<?php

use Example\Core\Controller\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Example\Core\Security\Attribute\IsGranted;
use Psr\Http\Message\ResponseInterface;

#[Route(path: '/example', name: 'example')]
#[IsGranted(attribute: 'example.permission')]
final class ExampleController extends AbstractController
{
    public function dashboard(): ResponseInterface
    {
    }
}

?>
-----
<?php

use Example\Core;
use Psr\Http\Message\ResponseInterface;

#[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
#[Core\Security\Attribute\IsGranted(attribute: 'example.permission')]
final class ExampleController extends Core\Controller\AbstractController
{
    public function dashboard(): ResponseInterface
    {
    }
}

?>
