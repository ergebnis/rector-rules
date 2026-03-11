<?php

namespace Example\App;

use Example\Core\Routing;
use Example\Core\Security;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    #[Security\Attribute\IsGranted('example.permission')]
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

final class ExampleController
{
    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    #[Core\Security\Attribute\IsGranted('example.permission')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
