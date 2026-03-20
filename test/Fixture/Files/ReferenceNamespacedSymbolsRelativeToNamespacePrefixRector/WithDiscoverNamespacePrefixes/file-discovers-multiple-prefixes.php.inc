<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

final class Kernel
{
    public function handle(
        Request $request,
        LoggerInterface $logger
    ): void {
        Route::create('/');
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component;
use Psr\Log;

final class Kernel
{
    public function handle(
        Component\HttpFoundation\Request $request,
        Log\LoggerInterface $logger
    ): void {
        Component\Routing\Attribute\Route::create('/');
    }
}

?>
