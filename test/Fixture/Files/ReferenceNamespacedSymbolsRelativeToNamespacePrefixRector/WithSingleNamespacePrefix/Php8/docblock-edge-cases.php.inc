<?php

namespace Example\App;

use Example\Core;
use Example\Core\Controller\AbstractController;

final class ExampleService
{
    /**
     * @param \Other\Vendor\Service $service
     */
    public function withNonMatchingFqn($service)
    {
    }

    /**
     * @param Core\Controller\AbstractController $controller
     */
    public function withAlreadyRewritten($controller)
    {
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;

final class ExampleService
{
    /**
     * @param \Other\Vendor\Service $service
     */
    public function withNonMatchingFqn($service)
    {
    }

    /**
     * @param Core\Controller\AbstractController $controller
     */
    public function withAlreadyRewritten($controller)
    {
    }
}

?>
