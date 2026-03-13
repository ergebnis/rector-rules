<?php

namespace Example\App;

use Example\Core;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    public function dashboard(): ResponseInterface
    {
        Core\Routing\Attribute\Route::create('/example');
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Example\Core\Routing;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    public function dashboard(): ResponseInterface
    {
        Routing\Attribute\Route::create('/example');
    }
}

?>
