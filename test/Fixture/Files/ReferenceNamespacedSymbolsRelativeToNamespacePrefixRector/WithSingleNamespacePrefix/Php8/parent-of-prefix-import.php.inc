<?php

namespace Example\App;

use Example;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function dashboard(): ResponseInterface
    {
        Example\Core\Routing\Attribute\Route::create('/example');
        Example\Core\Security\Attribute\IsGranted::check('example.permission');
    }
}

?>
-----
<?php

namespace Example\App;

use Example;
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
