<?php

namespace App\Controller;

use App\Controller\User\FindAction;
use Other\Controller;

final class FrontController extends FindAction
{
    public function __construct(Controller $controller)
    {
    }
}

?>
