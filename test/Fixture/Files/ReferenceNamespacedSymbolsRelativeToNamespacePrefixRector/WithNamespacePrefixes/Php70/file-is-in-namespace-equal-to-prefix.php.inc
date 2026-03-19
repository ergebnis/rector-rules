<?php

namespace App\Controller;

use App\Controller\User\FindAction;
use App\Controller\User\CreateAction;
use App\Controller\DashboardAction;

final class FrontController
{
    public function delegate(): DashboardAction
    {
        return new DashboardAction();
    }

    public function find(): User\FindAction
    {
        return new FindAction();
    }

    /**
     * @return CreateAction
     */
    public function create()
    {
        return new CreateAction();
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

    /**
     * @return User\CreateAction
     */
    public function create()
    {
        return new User\CreateAction();
    }
}

?>
