<?php

namespace Example\App;

use Example\Core\{Controller\AbstractController, Routing\Attribute\Route};
use Other\Foo\{Repository\FachbereichRepository, Service\FachbereichService};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    /**
     * @var FachbereichRepository
     */
    private $repository;

    /**
     * @var FachbereichService
     */
    private $service;

    public function __construct(
        FachbereichRepository $repository,
        FachbereichService $service
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
use Other\Foo\{Repository\FachbereichRepository, Service\FachbereichService};
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    /**
     * @var FachbereichRepository
     */
    private $repository;

    /**
     * @var FachbereichService
     */
    private $service;

    public function __construct(
        FachbereichRepository $repository,
        FachbereichService $service
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
