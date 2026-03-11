<?php

namespace Example\App;

use Example\Core\EntityManager\EntityManagerInterface;
use Example\Core\Routing\Attribute\Route;

final class ExampleController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/example', name: 'example')]
    public function dashboard(): void
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;

final class ExampleController
{
    /**
     * @var Core\EntityManager\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Core\EntityManager\EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Core\Routing\Attribute\Route(path: '/example', name: 'example')]
    public function dashboard(): void
    {
    }
}

?>
