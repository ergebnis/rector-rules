<?php

namespace Example\App;

use Example\Core;
use Example\Core\Routing\Attribute\Route;
use Example\Core\Security\Attribute\IsGranted;

final class ExampleController extends Core\Controller\AbstractController
{
    #[Route(path: '/example', name: 'example')]
    #[IsGranted(attribute: 'example.permission')]
    public function dashboard(): void
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;

final class ExampleController extends Core\Controller\AbstractController
{
    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    #[Core\Security\Attribute\IsGranted(attribute: 'example.permission')]
    public function dashboard(): void
    {
    }
}

?>
