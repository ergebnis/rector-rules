<?php

use Other\SomeClass;
use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    /**
     * @var SomeClass
     */
    private $service;

    public function __construct(SomeClass $service)
    {
        $this->service = $service;
    }
}

?>
