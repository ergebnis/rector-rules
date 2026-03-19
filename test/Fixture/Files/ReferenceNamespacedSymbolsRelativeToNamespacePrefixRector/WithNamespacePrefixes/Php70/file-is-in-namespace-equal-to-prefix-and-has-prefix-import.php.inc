<?php

namespace App\Controller;

use App\Controller;
use App\Controller\User\FindAction;

final class FrontController
{
    public function delegate(): Controller\DashboardAction
    {
        return new Controller\DashboardAction();
    }

    public function find(): Controller\User\FindAction
    {
        return new FindAction();
    }
}

?>
-----
<?php

namespace App\Controller;

final class FrontController
{
    public function delegate(): DashboardAction
    {
        return new DashboardAction();
    }

    public function find(): User\FindAction
    {
        return new User\FindAction();
    }
}

?>
