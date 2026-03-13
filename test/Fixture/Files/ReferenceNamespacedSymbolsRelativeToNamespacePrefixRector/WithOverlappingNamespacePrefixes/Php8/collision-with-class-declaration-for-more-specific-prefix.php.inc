<?php

namespace Example\App;

use Example\Core\Caching\Redis\Connection;
use Example\Core\Controller\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class Routing extends AbstractController
{
    #[Route(path: '/example', name: 'example')]
    #[Connection(host: 'localhost')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core\Caching\Redis;
use Example\Core;
use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class Routing extends Core\Controller\AbstractController
{
    #[Route(path: '/example', name: 'example')]
    #[Redis\Connection(host: 'localhost')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
