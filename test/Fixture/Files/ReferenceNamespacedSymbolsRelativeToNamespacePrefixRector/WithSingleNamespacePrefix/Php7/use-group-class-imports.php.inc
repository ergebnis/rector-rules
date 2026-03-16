<?php

namespace Example\App;

use Example\Core\{Controller\AbstractController, Routing\Attribute\Route, Security\Attribute\IsGranted};
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

final class ExampleController extends Core\Controller\AbstractController
{
    public function dashboard(): ResponseInterface
    {
        Core\Routing\Attribute\Route::create('/example');
        Core\Security\Attribute\IsGranted::check('example.permission');
    }
}

?>
