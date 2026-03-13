<?php

namespace Example\App;

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

namespace Example\App;

use Example\Core;
final class ExampleController
{
    public function dashboard()
    {
        $controller = new Core\Controller\FrontController();
        $other = new \Other\Vendor\Service();
    }
}

?>
