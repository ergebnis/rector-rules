<?php

namespace Example\App;

use Example\Core;
use Example\Core\Routing\Attribute\Route;
use Example\Core\Security\Attribute\IsGranted;

final class ExampleController extends Core\AbstractController
{
    public function dashboard(): void
    {
        Route::create('/example');
        IsGranted::check('example.permission');
    }
}

?>
-----
<?php

namespace Example\App;

use Example\Core;

final class ExampleController extends Core\AbstractController
{
    public function dashboard(): void
    {
        Core\Routing\Attribute\Route::create('/example');
        Core\Security\Attribute\IsGranted::check('example.permission');
    }
}

?>
