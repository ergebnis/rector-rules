<?php

namespace Example\App;

use Example\Core\Controller\AbstractController;
use Example\Database\Connection;
use Example\Http\Request;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends AbstractController
{
    public function dashboard(
        Connection $connection,
        Request $request
    ): ResponseInterface {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;
use Example\Database;
use Example\Http;
use Psr\Http\Message\ResponseInterface;

final class ExampleController extends Core\Controller\AbstractController
{
    public function dashboard(
        Database\Connection $connection,
        Http\Request $request
    ): ResponseInterface {
    }
}

?>
