<?php

namespace Example\App;

use Other\Foo\{Repository\DepartmentRepository, Service\DepartmentService};
use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    public function __construct(
        private DepartmentRepository $repository,
        private DepartmentService $service,
    ) {
    }
}

?>
