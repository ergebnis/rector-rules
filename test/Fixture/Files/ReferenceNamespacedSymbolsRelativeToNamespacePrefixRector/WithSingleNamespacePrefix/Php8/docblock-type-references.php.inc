<?php

namespace Example\App;

use Example\Core\Persistence\EntityManagerInterface;
use Example\Core\Http\Response;

final class ExampleService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Response               $response
     */
    public function __construct($entityManager, $response)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @throws Response
     */
    public function doSomething()
    {
    }

    /**
     * @param EntityManagerInterface|null $entityManager
     */
    public function withNullable($entityManager)
    {
    }

    /**
     * @param EntityManagerInterface[] $managers
     */
    public function withArray($managers)
    {
    }

    /**
     * @param \Example\Core\Persistence\EntityManagerInterface $entityManager
     */
    public function withFqn($entityManager)
    {
    }

    /**
     * @return array<string, EntityManagerInterface>
     */
    public function withGeneric()
    {
    }

    /**
     * @return array{entityManager: EntityManagerInterface, response: Response}
     */
    public function withArrayShape()
    {
    }

    /**
     * @return object{entityManager: EntityManagerInterface, response: Response}
     */
    public function withObjectShape()
    {
    }

    /**
     * @param string $name
     */
    public function nonMatching($name)
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;

final class ExampleService
{
    /**
     * @var Core\Persistence\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param Core\Persistence\EntityManagerInterface $entityManager
     * @param Core\Http\Response $response
     */
    public function __construct($entityManager, $response)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Core\Persistence\EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @throws Core\Http\Response
     */
    public function doSomething()
    {
    }

    /**
     * @param Core\Persistence\EntityManagerInterface|null $entityManager
     */
    public function withNullable($entityManager)
    {
    }

    /**
     * @param Core\Persistence\EntityManagerInterface[] $managers
     */
    public function withArray($managers)
    {
    }

    /**
     * @param Core\Persistence\EntityManagerInterface $entityManager
     */
    public function withFqn($entityManager)
    {
    }

    /**
     * @return array<string, Core\Persistence\EntityManagerInterface>
     */
    public function withGeneric()
    {
    }

    /**
     * @return array{entityManager: Core\Persistence\EntityManagerInterface, response: Core\Http\Response}
     */
    public function withArrayShape()
    {
    }

    /**
     * @return object{entityManager: Core\Persistence\EntityManagerInterface, response: Core\Http\Response}
     */
    public function withObjectShape()
    {
    }

    /**
     * @param string $name
     */
    public function nonMatching($name)
    {
    }
}

?>
