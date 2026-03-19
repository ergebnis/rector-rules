<?php

namespace App\Service;

use App\Controller\DashboardAction;
use App\Controller\Admin\UserListAction;
use App\Controller\Admin\Role\AssignAction;
use Psr\Http\Message\ResponseInterface;

final class AdminService
{
    public function __construct(
        DashboardAction $dashboard,
        UserListAction $userList,
        AssignAction $assign
    ) {
    }

    public function run(): ResponseInterface
    {
    }
}

?>
-----
<?php

namespace App\Service;

use App\Controller;
use App\Controller\Admin;
use App\Controller\Admin\Role;
use Psr\Http\Message\ResponseInterface;

final class AdminService
{
    public function __construct(
        Controller\DashboardAction $dashboard,
        Admin\UserListAction $userList,
        Role\AssignAction $assign
    ) {
    }

    public function run(): ResponseInterface
    {
    }
}

?>
