<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;

final class Kernel
{
    public function handle(Request $request, LoggerInterface $logger): Response
    {
        return new Response();
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;
use Psr\Log;
use Psr\Http;

final class Kernel
{
    public function handle(HttpFoundation\Request $request, Log\LoggerInterface $logger): HttpFoundation\Response
    {
        return new HttpFoundation\Response();
    }
}

?>
