<?php

namespace Other;

use App;
use Psr\Http\Message\ResponseInterface;

final class ExternalService
{
    public function handle(): ResponseInterface
    {
        $action = new \App\Controller\DashboardAction();
    }
}

?>
-----
<?php

namespace Other;

use App;
use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class ExternalService
{
    public function handle(): ResponseInterface
    {
        $action = new Controller\DashboardAction();
    }
}

?>
