<?php

namespace Example\App;

use Example\Core\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Example\Core\Security\Attribute\IsGranted;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    public function dashboard(): ResponseInterface
    {
        Route::create('/example');
        IsGranted::check('example.permission');
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\AbstractController
{
    public function dashboard(): ResponseInterface
    {
        Core\Routing\Attribute\Route::create('/example');
        Core\Security\Attribute\IsGranted::check('example.permission');
    }
}

?>
