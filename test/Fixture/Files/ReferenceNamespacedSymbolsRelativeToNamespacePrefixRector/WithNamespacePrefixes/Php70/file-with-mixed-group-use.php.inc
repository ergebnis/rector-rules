<?php

namespace App\Service;

use App\{Controller\DashboardAction, Entity\User};
use Psr\Http\Message\ResponseInterface;

final class MixedGroupService extends DashboardAction
{
    public function find(): User
    {
    }
}

?>
-----
<?php

namespace App\Service;

use App\Controller;
use App\{Entity\User};
use Psr\Http\Message\ResponseInterface;

final class MixedGroupService extends Controller\DashboardAction
{
    public function find(): User
    {
    }
}

?>
