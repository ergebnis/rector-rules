<?php

namespace Example;

use Example\Core\Controller\AbstractController;
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

namespace Example;

use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    public function dashboard(): ResponseInterface
    {
    }
}

?>
