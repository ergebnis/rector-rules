<?php

namespace App\Service;

use App\{Controller\DashboardAction, Controller\User\FindAction};
use Psr\Http\Message\ResponseInterface;

final class PartialParentService extends DashboardAction
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

final class PartialParentService extends Controller\DashboardAction
{
    public function find(): Controller\User\FindAction
    {
    }
}

?>
