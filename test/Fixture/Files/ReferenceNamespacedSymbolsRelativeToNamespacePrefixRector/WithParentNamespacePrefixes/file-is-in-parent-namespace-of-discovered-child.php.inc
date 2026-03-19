<?php

namespace Symfony;

use Symfony\Component\HttpFoundation\Request;

final class Kernel
{
    public function handle(Request $request): void
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
    }
}

?>
-----
<?php

namespace Symfony;

final class Kernel
{
    public function handle(Component\HttpFoundation\Request $request): void
    {
        $response = new Component\HttpFoundation\Response();
    }
}

?>
