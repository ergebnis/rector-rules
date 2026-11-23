<?php

namespace App\Controller\Admin;

use Framework\Controller;

final class UserController extends Controller
{
    public function __construct(\App\Controller\Admin\User\FindAction $findAction)
    {
    }
}

?>
-----
<?php

namespace App\Controller\Admin;

use Framework\Controller;

final class UserController extends Controller
{
    public function __construct(User\FindAction $findAction)
    {
    }
}

?>
