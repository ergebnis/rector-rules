<?php

namespace Example\App;

use Example\Core;
use Example\Core\Routing\Attribute\Route;
use Example\Core\Security\Attribute\IsGranted;

final class ExampleController extends Core\AbstractController
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

namespace Example\App;

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
