<?php

namespace App\Http;

use App\Controller as Foo;

/**
 * @see Foo\UserController
 * @see Foo\UserController::show()
 * @see https://example.com/routing
 * @uses Foo\Helper\render()
 * @used-by Foo\Kernel::handle()
 * @covers Foo\UserController
 */
final class Router
{
    public function dashboard(): Foo\DashboardController
    {
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller;

/**
 * @see Controller\UserController
 * @see Controller\UserController::show()
 * @see https://example.com/routing
 * @uses Controller\Helper\render()
 * @used-by Controller\Kernel::handle()
 * @covers Foo\UserController
 */
final class Router
{
    public function dashboard(): Controller\DashboardController
    {
    }
}

?>
