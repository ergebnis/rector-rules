<?php

namespace Example\App;

final class ExampleService
{
    /**
     * @param \Example\Core\Controller\AbstractController $controller
     */
    public function doSomething($controller)
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
     * @param Core\Controller\AbstractController $controller
     */
    public function doSomething($controller)
    {
    }
}

?>
