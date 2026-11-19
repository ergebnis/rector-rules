<?php

namespace App\Http;

use App\Controller as Foo;

final class Router
{
    public function dashboard(): Foo\DashboardController
    {
        Controller();
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller;

final class Router
{
    public function dashboard(): Controller\DashboardController
    {
        Controller();
    }
}

?>
