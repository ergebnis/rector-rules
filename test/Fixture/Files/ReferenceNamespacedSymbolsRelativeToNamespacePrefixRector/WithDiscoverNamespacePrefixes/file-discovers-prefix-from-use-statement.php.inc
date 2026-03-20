<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Kernel
{
    public function handle(Request $request): Response
    {
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component;

final class Kernel
{
    public function handle(Component\HttpFoundation\Request $request): Component\HttpFoundation\Response
    {
    }
}

?>
