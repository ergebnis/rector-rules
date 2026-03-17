<?php

namespace Example\App;

use Example\Core\{Controller\AbstractController, Routing\Attribute\Route};
use Other\Foo\{Repository\DepartmentRepository, Service\DepartmentService};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    public function __construct(
        private DepartmentRepository $repository,
        private DepartmentService $service,
    ) {
    }

    #[Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Other\Foo\{Repository\DepartmentRepository, Service\DepartmentService};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    public function __construct(
        private DepartmentRepository $repository,
        private DepartmentService $service,
    ) {
    }

    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
