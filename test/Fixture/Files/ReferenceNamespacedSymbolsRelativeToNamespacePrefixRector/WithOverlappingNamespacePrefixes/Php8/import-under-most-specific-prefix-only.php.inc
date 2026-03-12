<?php

namespace Example\App;

use Example\Core\Caching\Redis\Connection;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
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
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    #[Redis\Connection(host: 'localhost')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
