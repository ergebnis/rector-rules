<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

final class Kernel
{
    public function handle(Request $request, LoggerInterface $logger): void
    {
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;
use Psr\Log;

final class Kernel
{
    public function handle(HttpFoundation\Request $request, Log\LoggerInterface $logger): void
    {
    }
}

?>
