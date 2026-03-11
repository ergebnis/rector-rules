<?php

namespace Example\App;

use Example\Core\EntityManager\EntityManagerInterface;
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
     * @param \Example\Core\EntityManager\EntityManagerInterface $entityManager
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
     * @var Core\EntityManager\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param Core\EntityManager\EntityManagerInterface $entityManager
     * @param Core\Http\Response $response
     */
    public function __construct($entityManager, $response)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Core\EntityManager\EntityManagerInterface
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
     * @param Core\EntityManager\EntityManagerInterface|null $entityManager
     */
    public function withNullable($entityManager)
    {
    }

    /**
     * @param Core\EntityManager\EntityManagerInterface[] $managers
     */
    public function withArray($managers)
    {
    }

    /**
     * @param Core\EntityManager\EntityManagerInterface $entityManager
     */
    public function withFqn($entityManager)
    {
    }

    /**
     * @return array<string, Core\EntityManager\EntityManagerInterface>
     */
    public function withGeneric()
    {
    }

    /**
     * @return array{entityManager: Core\EntityManager\EntityManagerInterface, response: Core\Http\Response}
     */
    public function withArrayShape()
    {
    }

    /**
     * @return object{entityManager: Core\EntityManager\EntityManagerInterface, response: Core\Http\Response}
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
