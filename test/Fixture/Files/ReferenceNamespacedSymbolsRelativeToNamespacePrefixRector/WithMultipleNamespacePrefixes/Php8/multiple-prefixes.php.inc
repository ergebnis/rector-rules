<?php

namespace Example\App;

use Example\Core\Controller\AbstractController;
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

    #[Route(path: '/example', name: 'example')]
    public function dashboard(): ResponseInterface
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core\Controller\AbstractController;
use Example\Core\Routing;
use Example\Domain;
use Psr\Http;

final class ExampleController extends AbstractController
{
    /**
     * @var Domain\UserRepository
     */
    private $userRepository;

    public function __construct(Domain\UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): Http\Message\ResponseInterface
    {
    }
}

?>
