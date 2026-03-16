<?php

namespace Example\App;

use function Example\Core\Helper\resolve_path;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        $path = resolve_path('/example');
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
    public function dashboard(): ResponseInterface
    {
        $path = Core\Helper\resolve_path('/example');
    }
}

?>
