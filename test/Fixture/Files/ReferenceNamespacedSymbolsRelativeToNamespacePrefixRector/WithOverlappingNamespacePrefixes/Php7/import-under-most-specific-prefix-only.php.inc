<?php

namespace Example\App;

use Example\Core\Caching\Redis\Connection;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        Connection::create();
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core\Caching\Redis;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        Redis\Connection::create();
    }
}

?>
