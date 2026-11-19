<?php

namespace App\Http;

use App\{Controller as Foo, Model};

final class Router
{
    public function dashboard(Model\User $user): Foo\DashboardController
    {
    }
}

?>
-----
<?php

namespace App\Http;

use App\{Controller, Model};

final class Router
{
    public function dashboard(Model\User $user): Controller\DashboardController
    {
    }
}

?>
