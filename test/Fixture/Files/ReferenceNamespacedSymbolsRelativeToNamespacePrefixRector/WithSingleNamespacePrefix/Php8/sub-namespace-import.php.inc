<?php

namespace Example\App;

use Example\Core\Routing;
use Example\Core\Security;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
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
    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
        Core\Security\Attribute\IsGranted::check('example.permission');
    }
}

?>
