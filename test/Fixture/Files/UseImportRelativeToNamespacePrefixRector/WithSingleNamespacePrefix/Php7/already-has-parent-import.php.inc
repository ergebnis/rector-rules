<?php

namespace Example\App;

use Example\Core;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\AbstractController
{
    public function dashboard(): ResponseInterface
    {
    }
}

?>
