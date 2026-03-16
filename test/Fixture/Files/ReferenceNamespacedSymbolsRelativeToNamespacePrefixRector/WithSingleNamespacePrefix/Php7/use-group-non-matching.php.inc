<?php

namespace Example\App;

use Other\Foo\{Repository\FachbereichRepository, Service\FachbereichService};
use Psr\Http\Message\ResponseInterface;

final class ExampleService
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
}

?>
