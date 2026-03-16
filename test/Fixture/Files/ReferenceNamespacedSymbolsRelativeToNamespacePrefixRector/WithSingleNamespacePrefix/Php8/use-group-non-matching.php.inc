<?php

namespace Example\App;

use Other\Foo\{Repository\FachbereichRepository, Service\FachbereichService};
use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    public function __construct(
        private FachbereichRepository $repository,
        private FachbereichService $service,
    ) {
    }
}

?>
