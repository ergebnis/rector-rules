<?php

namespace App\Service;

use App\Controller\User\FindAction;
use Other\Controller;

final class UserService extends FindAction
{
    public function __construct(Controller $controller)
    {
    }
}

?>
