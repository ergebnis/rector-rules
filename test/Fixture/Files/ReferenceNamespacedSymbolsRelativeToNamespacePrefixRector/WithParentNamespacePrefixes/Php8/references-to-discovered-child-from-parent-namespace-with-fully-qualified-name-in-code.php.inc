<?php

namespace Example;

final class ExampleController
{
    public function dashboard()
    {
        $controller = new \Example\Core\Controller\FrontController();
        $other = new \Other\Vendor\Service();
    }
}

?>
-----
<?php

namespace Example;

final class ExampleController
{
    public function dashboard()
    {
        $controller = new Core\Controller\FrontController();
        $other = new \Other\Vendor\Service();
    }
}

?>
