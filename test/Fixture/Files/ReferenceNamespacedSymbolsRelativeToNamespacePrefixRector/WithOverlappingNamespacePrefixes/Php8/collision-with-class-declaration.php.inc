<?php

namespace Example\App;

use Example\Core\Caching\Redis\Connection;
use Example\Core\Controller\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class Core extends AbstractController
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
use Example\Core\Controller\AbstractController;
use Example\Core\Routing;
use Psr\Http\Message\ResponseInterface;

final class Core extends AbstractController
{
    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    #[Redis\Connection(host: 'localhost')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
