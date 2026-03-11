<?php

use Example\Core\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Example\Core\Security\Attribute\IsGranted;

final class ExampleController extends AbstractController
{
    #[Route(path: '/example', name: 'example')]
    #[IsGranted('example.permission')]
    public function dashboard(): void
    {
    }
}

?>
-----
<?php

use Example\Core;

final class ExampleController extends Core\AbstractController
{
    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    #[Core\Security\Attribute\IsGranted('example.permission')]
    public function dashboard(): void
    {
    }
}

?>
