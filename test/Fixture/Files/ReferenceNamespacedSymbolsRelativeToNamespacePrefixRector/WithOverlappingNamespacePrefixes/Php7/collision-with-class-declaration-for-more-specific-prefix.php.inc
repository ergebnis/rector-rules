<?php

namespace Example\App;

use Example\Core\Caching\Redis\Connection;
use Example\Core\Controller\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class Routing extends AbstractController
{
    public function dashboard(): ResponseInterface
    {
        Connection::create();
        Route::create('/example');
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
    public function dashboard(): ResponseInterface
    {
        Redis\Connection::create();
        Route::create('/example');
    }
}

?>
