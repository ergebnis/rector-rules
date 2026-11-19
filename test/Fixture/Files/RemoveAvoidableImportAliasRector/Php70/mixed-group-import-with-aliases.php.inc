<?php

namespace App\Http;

use App\{Controller as Foo, Model, function Helper\render as view};

final class Router
{
    public function dashboard(Model\User $user): Foo\DashboardController
    {
        return view();
    }
}

?>
-----
<?php

namespace App\Http;

use App\{Controller, Model, function Helper\render};

final class Router
{
    public function dashboard(Model\User $user): Controller\DashboardController
    {
        return render();
    }
}

?>
