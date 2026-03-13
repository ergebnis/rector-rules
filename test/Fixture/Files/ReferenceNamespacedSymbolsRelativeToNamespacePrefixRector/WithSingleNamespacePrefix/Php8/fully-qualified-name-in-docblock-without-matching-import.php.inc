<?php

namespace Example\App;

use Psr\Http\Message\ResponseInterface;

final class ExampleService
{
    /**
     * @param \Example\Core\Controller\AbstractController $controller
     *
     * @return \Example\Core\Entity\User
     */
    public function process($controller)
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Psr\Http\Message\ResponseInterface;
use Example\Core;

final class ExampleService
{
    /**
     * @param Core\Controller\AbstractController $controller
     *
     * @return Core\Entity\User
     */
    public function process($controller)
    {
    }
}

?>
