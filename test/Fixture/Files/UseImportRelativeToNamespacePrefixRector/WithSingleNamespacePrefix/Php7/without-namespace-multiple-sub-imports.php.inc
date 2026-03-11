<?php

use Example\Core\AbstractController;
use Example\Core\Routing\Attribute\Route;

final class ExampleController extends AbstractController
{
    public function dashboard(): void
    {
        Route::create('/example');
    }
}

?>
-----
<?php

use Example\Core;

final class ExampleController extends Core\AbstractController
{
    public function dashboard(): void
    {
        Core\Routing\Attribute\Route::create('/example');
    }
}

?>
