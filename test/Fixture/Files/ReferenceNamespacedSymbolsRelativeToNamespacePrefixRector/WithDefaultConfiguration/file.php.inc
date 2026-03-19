<?php

namespace App\Controller;

use App\Controller\User\FindAction;
use Symfony\Component\HttpFoundation\Response;

final class DashboardAction
{
    public function __invoke(): Response
    {
        $action = new FindAction();
    }
}

?>
