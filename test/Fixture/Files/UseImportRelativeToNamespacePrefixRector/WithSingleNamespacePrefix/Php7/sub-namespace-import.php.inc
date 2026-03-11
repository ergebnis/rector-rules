<?php

namespace Example\App;

use Example\Core\Routing;
use Example\Core\Security;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        Routing\Attribute\Route::create('/example');
        Security\Attribute\IsGranted::check('example.permission');
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
        Core\Routing\Attribute\Route::create('/example');
        Core\Security\Attribute\IsGranted::check('example.permission');
    }
}

?>
