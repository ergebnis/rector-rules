<?php

namespace Example\App;

use Other\Foo\{Repository\DepartmentRepository, Service\DepartmentService};
use Psr\Http\Message\ResponseInterface;

final class ExampleService
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
}

?>
