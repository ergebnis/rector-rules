<?php

namespace Example\App;

use Example\Core\{Controller\AbstractController, Routing\Attribute\Route};
use Other\Foo\{Repository\DepartmentRepository, Service\DepartmentService};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    /**
     * @var DepartmentRepository
     */
    private $repository;

    /**
     * @var DepartmentService
     */
    private $service;

    public function __construct(
        DepartmentRepository $repository,
        DepartmentService $service
    ) {
        $this->repository = $repository;
        $this->service = $service;
    }

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
use Other\Foo\{Repository\DepartmentRepository, Service\DepartmentService};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    /**
     * @var DepartmentRepository
     */
    private $repository;

    /**
     * @var DepartmentService
     */
    private $service;

    public function __construct(
        DepartmentRepository $repository,
        DepartmentService $service
    ) {
        $this->repository = $repository;
        $this->service = $service;
    }

    public function dashboard(): ResponseInterface
    {
        Core\Routing\Attribute\Route::create('/example');
    }
}

?>
