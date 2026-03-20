<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

final class Kernel
{
    public function handle(Request $request, LoggerInterface $logger): Response
    {
        return new JsonResponse([]);
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;
use Psr\Log\LoggerInterface;

final class Kernel
{
    public function handle(HttpFoundation\Request $request, LoggerInterface $logger): HttpFoundation\Response
    {
        return new HttpFoundation\JsonResponse([]);
    }
}

?>
