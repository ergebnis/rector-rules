<?php

namespace App\Controller\Admin;

use App\Controller\UserController;

final class Dashboard
{
    public function render(UserController $userController): string
    {
        \App\Controller\Admin\log('render');

        return \App\Controller\Admin\Helper\resolve_path('/admin');
    }
}

?>
-----
<?php

namespace App\Controller\Admin;

use App\Controller;

final class Dashboard
{
    public function render(Controller\UserController $userController): string
    {
        Controller\Admin\log('render');

        return Helper\resolve_path('/admin');
    }
}

?>
