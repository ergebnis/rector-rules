<?php

use Other\Foo\SomeClass;
use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    public function __construct(SomeClass $service, ResponseInterface $response)
    {
    }
}

?>
