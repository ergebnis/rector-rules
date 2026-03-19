<?php

namespace App\Service;

use App\Controller\{DashboardAction, User\FindAction};
use Psr\Http\Message\ResponseInterface;

final class GroupService extends DashboardAction
{
    public function find(): FindAction
    {
    }
}

?>
-----
<?php

namespace App\Service;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class GroupService extends Controller\DashboardAction
{
    public function find(): Controller\User\FindAction
    {
    }
}

?>
