<?php

namespace Example\App;

use Example\Core\Persistence\EntityManagerInterface;
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

    public function dashboard(): void
    {
        Route::create('/example');
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
     * @var Core\Persistence\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Core\Persistence\EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function dashboard(): void
    {
        Core\Routing\Attribute\Route::create('/example');
    }
}

?>
