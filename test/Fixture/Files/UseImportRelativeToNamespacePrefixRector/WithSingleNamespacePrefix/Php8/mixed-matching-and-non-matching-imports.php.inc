<?php

namespace Example\App;

use Example\Core\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Other\DepartmentRepository;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    /**
     * @var DepartmentRepository
     */
    private $repository;

    public function __construct(DepartmentRepository $repository)
    {
        $this->repository = $repository;
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
use Other\DepartmentRepository;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\AbstractController
{
    /**
     * @var DepartmentRepository
     */
    private $repository;

    public function __construct(DepartmentRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
