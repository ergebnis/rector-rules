<?php

namespace App\Controller\Admin;

final class Controller
{
    public function __construct(\App\Controller\Admin\User\FindAction $findAction)
    {
    }
}

?>
-----
<?php

namespace App\Controller\Admin;

final class Controller
{
    public function __construct(User\FindAction $findAction)
    {
    }
}

?>
