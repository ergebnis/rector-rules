<?php

namespace Example\App;

use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        Route::create('/example');
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
    public function dashboard(): ResponseInterface
    {
        Routing\Attribute\Route::create('/example');
    }
}

?>
