<?php

namespace Example\App;

use Example\Core;
use Example\Core\Controller\AbstractController;

final class ExampleService
{
    /**
     * @param Core $core
     * @param AbstractController $controller
     *
     * @return Core
     */
    public function doSomething($core, $controller)
    {
    }

    /**
     * @param \Example\Core $core
     */
    public function withFqn($core)
    {
    }

    /**
     * @return array<string, Core>
     */
    public function withGeneric()
    {
    }

    /**
     * @return Core|null
     */
    public function withNullable()
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
     * @param Core $core
     * @param Core\Controller\AbstractController $controller
     *
     * @return Core
     */
    public function doSomething($core, $controller)
    {
    }

    /**
     * @param Core $core
     */
    public function withFqn($core)
    {
    }

    /**
     * @return array<string, Core>
     */
    public function withGeneric()
    {
    }

    /**
     * @return Core|null
     */
    public function withNullable()
    {
    }
}

?>
