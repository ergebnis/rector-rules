<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Finder\Finder;
use Psr\Log\LoggerInterface;

final class Kernel
{
    public function handle(
        Request $request,
        LoggerInterface $logger
    ): void {
        Route::create('/');
        $finder = new Finder();
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Symfony\Component\Finder;
use Psr\Log\LoggerInterface;

final class Kernel
{
    public function handle(
        HttpFoundation\Request $request,
        LoggerInterface $logger
    ): void {
        Routing\Attribute\Route::create('/');
        $finder = new Finder\Finder();
    }
}

?>
