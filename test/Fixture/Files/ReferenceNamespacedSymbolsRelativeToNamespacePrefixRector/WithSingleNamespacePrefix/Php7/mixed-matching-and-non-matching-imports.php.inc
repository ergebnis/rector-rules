<?php

namespace Example\App;

use Example\Core\Controller\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Other\Foo\Repository\FachbereichRepository;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    /**
     * @var FachbereichRepository
     */
    private $repository;

    public function __construct(FachbereichRepository $repository)
    {
        $this->repository = $repository;
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
use Other\Foo\Repository\FachbereichRepository;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    /**
     * @var FachbereichRepository
     */
    private $repository;

    public function __construct(FachbereichRepository $repository)
    {
        $this->repository = $repository;
    }

    public function dashboard(): ResponseInterface
    {
        Core\Routing\Attribute\Route::create('/example');
    }
}

?>
