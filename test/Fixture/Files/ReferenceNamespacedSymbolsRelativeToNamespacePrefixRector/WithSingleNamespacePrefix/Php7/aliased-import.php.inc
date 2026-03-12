<?php

namespace Example\App;

use Example\Core\Controller\AbstractController as BaseController;
use Example\Core\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends BaseController
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
