<?php

namespace Example\App;

use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
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

use Example\Core\Routing;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
