<?php

use Example\Core\AbstractController;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    public function dashboard(): ResponseInterface
    {
    }
}

?>
-----
<?php

use Example\Core;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\AbstractController
{
    public function dashboard(): ResponseInterface
    {
    }
}

?>
