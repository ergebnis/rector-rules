<?php

namespace Example\App;

use Example\Core\AbstractController;
use Example\Core\Routing\Attribute\Route;
use Example\Domain\UserRepository;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
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
use Example\Domain;
use Psr\Http;

final class ExampleController extends Core\AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(Domain\UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function dashboard(): Http\Message\ResponseInterface
    {
        Core\Routing\Attribute\Route::create('/example');
    }
}

?>
